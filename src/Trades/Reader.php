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
        $this->Plate = $Spirit->Plate();

    }

    public function readPassport($img)
    {

        $infos = ["startpos" => $this->Plate->lure1_len,
            "lengthpos" => $this->Plate->info_len - $this->Plate->pos_len,
        ];
        foreach ($infos as $pos => $begin) {
            $raw_pos = $this->extract($img, $this->Plate->pos_len, $begin);
            $infos[$pos] = preg_replace('/[^\d]/', '', $raw_pos);
        }

        /* extract data */
        $outdata = $this->extract($img, $infos["lengthpos"], $infos["startpos"]);
        $valid_outdata = Jack::Help()->b64pad($outdata);

        imagedestroy($img);

        $decrypt = $this->Spirit->getSod()->decrypt($valid_outdata);
        if($decrypt === false){
            $this->Spirit->addToLog('extract error');
            return false;
        }
        if ($this->Spirit->inTestMode()) {
            return $this->testReadStegaData($decrypt, $infos["startpos"], $infos["lenghtpos"]);
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

    private function testReadStegaData($decrypt, $startpos, $lenghtpos)
    {
//@doc: for testing purposes:
        echo implode('<br>', ['start position: ' . $startpos, 'length position: ' . $lenghtpos, 'result: ' . $decrypt]);
    }

}
