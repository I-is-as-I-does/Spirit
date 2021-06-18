<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

class Reader
{

    private $Spirit;
    private $Plate;
    private $Sod;

    public function __construct($Spirit)
    {
        $this->Spirit = $Spirit;
        $this->Plate = $Spirit->Plate();
        $this->Sod = $this->Spirit->getSod();

    }

    private function checkMeasures($rscImg)
    {

        if (imagesx($rscImg) != $this->Plate->width) {
            return 'img-has-wrong-width';
        }
        if (imagesy($rscImg) != $this->Plate->height) {
            return 'img-has-wrong-height';
        }
        return true;
    }

    public function readImg($rscImg)
    {
        $check = $this->checkMeasures($rscImg);
        if ($check !== true) {
            $this->Spirit->record($check);
            return false;
        }

        $infos = ["startpos" => $this->Plate->lure1_len,
            "lengthpos" => $this->Plate->info_len - $this->Plate->pos_len,
        ];

        foreach ($infos as $pos => $begin) {
            $raw_pos = $this->extract($img, $this->Plate->pos_len, $begin);
            $infos[$pos] = preg_replace('/[^\d]/', '', $raw_pos);
        }

        //extract data
        $outdata = $this->extract($img, $infos["lengthpos"], $infos["startpos"]);
        $valid_outdata = Utils::b64pad($outdata);

        imagedestroy($img);

        $decrypt = $this->Sod->decrypt($valid_outdata);
        if ($decrypt === false) {
            $this->Spirit->record($this->Sod->getLogs()[0]);
            return false;
        }

        if ($this->Spirit->inTestMode()) {
            $this->testOutput($decrypt, $infos["startpos"], $infos["lengthpos"]);
        }
        return $decrypt;
    }

    private function extract($img, $pile, $strt)
    {

        $pixelX = $strt * 8;
        $pixelY = $this->Plate->encod_y;

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
            $rgb = imagecolorat($img, $pixelX, $pixelY); // @doc: color of the pixel at the x and y positions
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

    private function testOutput($decrypt, $startpos, $lenghtpos)
    {
//@doc: for testing purposes:
        echo implode('<br>', ['start position: ' . $startpos, 'data length: ' . $lenghtpos, 'decrypted data: ' . $decrypt]);
    }

}
