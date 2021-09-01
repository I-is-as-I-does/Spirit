<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

class Reader
{
    use Plate;

    private $Spirit;

    public function __construct($Spirit)
    {
        $this->Spirit = $Spirit;
    }

    public function read(string $img, string $spiritKey)
    {
    
        $rscImg = $this->getRscImg($img);
        if ($rscImg === false) {
            $this->Spirit->record('invalid-image');
            return false;
        }

        $this->setConfig($rscImg, $spiritKey);
        if(!$this->setPlateParam() || !$this->isValidKey($spiritKey)){
            return false;
        }

        $extrdata = $this->extract($rscImg); 
        imagedestroy($rscImg);

        $keypos =strpos($extrdata, $spiritKey);

        if($keypos === false){
            $this->Spirit->record('invalid-key-image-pair');
            return false;
        }

        $targdata = $this->getData($extrdata, $keypos);
        if($targdata === false){
            return false;
        }

        $decrypt = $this->Sod->decrypt($targdata);
        if ($decrypt === false) {
            $this->Spirit->record($this->Sod->getLogs()[0]);
            return false;
        }

        return $decrypt;
    }

    private function setConfig($rscImg, $spiritKey)
    {      
        $this->config['height'] = imagesy($rscImg);
        $this->config['width'] = imagesx($rscImg);
        $this->config['keyLength'] = strlen($spiritKey);
    }

    private function getData($extrdata, $keypos)
    {      
        $datalenpos = $keypos + $this->keyLength;
        $datalen = substr($extrdata,$datalenpos,$this->poslen);
        $datalen = preg_replace('/[^\d]/', '', $datalen);
        if(empty($datalen)){
            $this->Spirit->record('invalid-encryption');
            return false;
           }
        $outdata = substr($extrdata,$datalenpos + $this->poslen, $datalen); 
        return Utils::b64pad($outdata);
    }

    
    private function getRscImg($img)
    {
        if (is_file($img)) {
            return Utils::pathToRsc($img);
        }
        return Utils::b64ToRsrc($img);
    }


    private function extract($rscImg)
    {
        $pixelX =0;
        $pixelY = 0;
    
        $extract = '';

        for ($x = 0; $x < $this->encod_limits; $x++) { 
            if ($pixelX === $this->width) {
                $pixelY++;
                $pixelX = 0;
            }
            $rgb = imagecolorat($rscImg, $pixelX, $pixelY); 
            $b = $rgb & 0xFF;

            $binBlue = Utils::toBin($b);
            $leastBit = strlen($binBlue) - 1;

            $extract .= $binBlue[$leastBit]; 
            $pixelX++;
        }
        return Utils::toHex($extract);
    }

}
