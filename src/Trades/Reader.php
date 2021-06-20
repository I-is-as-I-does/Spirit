<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

use \SSITU\Jack\Jack;

class Reader
{

    private $Spirit;
    private $Plate;

    public function __construct($Spirit)
    {
        $this->Spirit = $Spirit;

    }

    private function getRscImg($img)
    {
        if (is_file($img)) {
            return Jack::Images()->fileToRsc($img);
        }
        return Jack::Images()->b64ToRsrc($img);
    }


    private function readSignature($signature)
    {
       $signature = base64_decode($signature);
       $split = explode('$',$signature);
       $luresBytes = [];
       foreach(['lure2Bytes', 'lure1Bytes', 'flavourCode'] as $k){
        $luresBytes[$k] = array_pop($split);
       }
       if(array_pop($split) === 1){
           $flavour = 'Sodium';
       } else {
        $flavour = 'Sugar';
       }
       $cryptKey = implode('$',$split);
       if(!$this->Spirit->initSod($cryptKey, $flavour)){
           return false;
       }
       return $luresBytes;
    }



    public function readImg($img, $signature)
    {

        $rscImg = $this->getRscImg($img);
        if ($rscImg === false) {
            $this->Spirit->record('invalid-image');
            return false;
        }

        $luresBytes = readSignature($signature);
        if($luresBytes === false){
            $this->Spirit->record('invalid-signature');
            return false;
        }
      
        $height = imagesy($rscImg);
        $width = imagesx($rscImg);

        $this->Plate = $Spirit->Plate();
        if(!$this->Plate->setConfig($width, $height,$luresBytes['lure1Bytes'],$luresBytes['lure2Bytes'])){
            return false;
        }

        $infos = ["startpos" => $this->Plate->lure1_len,
            "lengthpos" => $this->Plate->extradata_len - $this->Plate->pos_len,
        ];

        foreach ($infos as $pos => $begin) {
            $raw_pos = $this->extract($rscImg, $this->Plate->pos_len, $begin);
            $infos[$pos] = preg_replace('/[^\d]/', '', $raw_pos);
        }

        //extract data
        $outdata = $this->extract($rscImg, $infos["lengthpos"], $infos["startpos"]);
        $valid_outdata = Utils::b64pad($outdata);

        imagedestroy($rscImg);

        $Sod = $this->Spirit->getSod();
        $decrypt = $Sod->decrypt($valid_outdata);
        if ($decrypt === false) {
            $this->Spirit->record($Sod->getLogs()[0]);
            return false;
        }

        return $decrypt;
    }

  

    private function extract($rscImg, $pile, $strt)
    {

        $pixelX = $strt * 8;
        $pixelY = 0;

        $stop = $pile * 8;

        while ($pixelX > $this->Plate->width) {
            $pixelY++;
            $pixelX -= $this->Plate->width;
        }

        $extract = '';

        for ($x = 0; $x < $this->Plate->encod_limits; $x++) { // @doc: loop through pixel by pixel
            if ($pixelX === $this->Plate->width) { // @doc: if this is true, we've reached the end of the row of pixels, start on next row
                $pixelY++;
                $pixelX = 0;
            }
            $rgb = imagecolorat($rscImg, $pixelX, $pixelY); // @doc: color of the pixel at the x and y positions
            $b = $rgb & 0xFF;

            $binBlue = Utils::toBin($b);
            $leastBit = strlen($binBlue) - 1;

            $extract .= $binBlue[$leastBit]; // @doc: add the lsb to binary result

            if ($x == $stop) {

                return Utils::toHex($extract);
            }

            $pixelX++; // @doc: change x coordinates to next
        }
    }

}
