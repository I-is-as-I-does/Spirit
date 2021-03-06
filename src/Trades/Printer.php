<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

class Printer
{
    use Plate, Sheet;

    private $Spirit;
    private $Paper;
    private $Stylus;

    public function __construct($Spirit)
    {
        $this->Spirit = $Spirit;
        $this->Paper = new Paper($this);
        $this->Stylus = new Stylus($this);
    }

    public function __get($prop)
    {
        if ($prop[0] === strtolower($prop[0]) && property_exists($this, $prop)) {
            return $this->$prop;
        }
    }

    function print(string $dataToInject, array $config, ?string $spiritKey = null) {

       
        $this->config = $config;
        if (!$this->setPlateParam() || !$this->setSheetParam()) {
            return false;
        }

        if(!empty($spiritKey) && !$this->isValidKey($spiritKey)){
            return false;
        }
        
        $cryptdata = $this->Sod->encrypt($dataToInject);
        if ($cryptdata === false) {
            $this->Spirit->record($this->Sod->getLogs()[0]);
            return false;
        }
 
        $rscImg = $this->Paper->getRscImg();
        if (empty($rscImg)) {
            $this->Spirit->record('invalid-base-image');
            return false;
        }

        if (array_filter($this->texts, function ($itm) {return empty($itm);}) !== $this->texts) {
            $rscImg = $this->Stylus->writeTexts($rscImg);
        }

        return $this->StegaData($rscImg, $cryptdata, $spiritKey);
    }

 
    private function StegaData($rscImg, $cryptdata, $spiritKey = null)
    {
        $cryptdata = rtrim($cryptdata, '=');
        $datalen = strlen($cryptdata);
        $info = Utils::adjustLen($this->poslen, $datalen);
        $randpoz = random_int($this->minfillerLen, $this->fillerLimit - $datalen);
        $filler = Utils::randFill($randpoz);
         if(empty($spiritKey)){   
         $spiritKey = substr($filler, -$this->keyLength);             
        } else {
          $filler = substr_replace($filler, $spiritKey, -$this->keyLength);
        }
        $toinject = $filler . $info . $cryptdata;
        $toinject .= Utils::randFill($this->pxLimit - strlen($toinject));
   
        $binData = Utils::toBin($toinject);
        if (strlen($toinject) > $this->pxLimit) {
            $this->Spirit->record('too much data to inject in a too small img');
            return false;
        }
        $rscImg = $this->rewritePx($rscImg, $binData);
        $out_b64img = Utils::rscTob64($rscImg, $this->imgExtension);
        imagedestroy($rscImg);
        return ['image' => $out_b64img, 'key' => $spiritKey];
    }

    private function rewritePx($rscImg, $binData)
    {
        $pixelX = 0;
        $pixelY = 0;

        for ($x = 0; $x < $this->encod_limits; $x++) {
            if ($pixelX === $this->width) {
                $pixelY++;
                $pixelX = 0;
            }
            $rgb = imagecolorat($rscImg, $pixelX, $pixelY);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;

            $binBlue = Utils::toBin($b);
            $leastBit = strlen($binBlue) - 1;

            $binBlue[$leastBit] = $binData[$x];
            $newBlue = Utils::toHex($binBlue);

            $new_color = imagecolorallocate($rscImg, $r, $g, $newBlue);
            imagesetpixel($rscImg, $pixelX, $pixelY, $new_color);

            $pixelX++;
        }
        return $rscImg;
    }

}
