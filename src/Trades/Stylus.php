<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

class Stylus
{

    private $Spirit;
    private $Plate;
    private $Sheet;
    private $Sod;

    private $textcolor;

    public function __construct($Spirit)
    {
        $this->Spirit = $Spirit;
        $this->Plate = $Spirit->Plate();
        $this->Sheet = $Spirit->Sheet();
    }

   private function textColor($rscImg){

        if(empty($this->textcolor) || empty(imagecolorsforindex($rscImg,$this->Sheet->textColorCodes)){
            $this->textcolor = imagecolorallocate($rscImg, ...$this->Sheet->textColorCodes);
        }
       return $this->textcolor;
        
    }

    public function writeBottom($rscImg, $endx, $text)
    {
        imagettftext($rscImg, $this->Sheet->fontSize, $this->Sheet->angle, $endx["xmid"], $this->Sheet->endy, $this->textColor($rscImg), $this->Sheet->fontFile, $text);
        return $rscImg;
    }

    public function writeTop($rscImg, $headx, $text)
    {
        imagettftext($rscImg, $this->Sheet->fontSize, $this->Sheet->angle, $headx["xmid"], $this->Sheet->heady, $this->textColor($rscImg), $this->Sheet->fontFile, $text);
        return $rscImg;
    }

    public function writeCenter($rscImg, $mainText, $addtTexts = [])
    {
        $lines = $this->getLines($mainText, $addtTexts);
        $rscImg = $this->imprint($lines, $rscImg);
        return $rscImg;
    }

    private function getLines($mainText, $addtTexts)
    {
        $lines = [$mainText];
        if (!empty($addtTexts)) {

            $chardim = $this->boxDim('M'); // @doc maj M is considered a good "template"
            $maxchars = floor($this->Sheet->maxWidth / $chardim["w"]);
            foreach ($addtTexts as $addt) {
                $wrapncks = wordwrap($addt, $maxchars);
                $nlines = explode("\n", $wrapncks);
                $lines = array_merge($lines, $nlines);
            }
            $maxdiff = $this->Sheet->baseDiff - count($lines);
            if ($maxdiff < 0) {
                $lines = array_slice($lines, 0, $maxdiff);
                $lastline = trim(array_pop($lines)) . ' [...]';
                $lines[] = $lastline;
            }
        }
        return $lines;
    }

    private function imprint($lines, $rscImg)
    {
        $textcolor = $this->textColor($rscImg);
        $linescount = count($lines);

        /* nicknames inheritance lines */
        $y = ($this->Plate->height - $linescount * $this->Sheet->lineHeight) / 2; //@doc: y pos. of the middle of 1st line
        $y += $this->Sheet->y_adjust; // @doc: adjustments to get the line bottom y pos.

        foreach ($lines as $text) {
            $solox = $this->xPoz($text);
            imagettftext($rscImg, $this->Sheet->fontSize, $this->Sheet->angle, $solox["xmid"], $y, $textcolor, $this->Sheet->fontFile, $text);

            $y += $this->Sheet->lineHeight;
        }

        return $rscImg;
    }

    private function boxDim($input)
    {
        //doc: beware, imagettfbbox is often buggy, depending on server running GD
        $box = imagettfbbox($this->Sheet->fontSize, $this->Sheet->angle, $this->Sheet->fontFile, $input);
        $inputwidth = abs($box[2] - $box[0]);
        $inputheight = abs($box[7] - $box[1]);
        return ["w" => $inputwidth, "h" => $inputheight];
    }

    private function xPoz($input)
    {
        $indim = $this->boxDim($input);
        $xmid = ($this->Plate->width - $indim["w"]) / 2;
        $out = ["xmid" => $xmid];
        $out["maskx1"] = $xmid - $this->Sheet->margin;
        $out["maskx2"] = $xmid + $indim["w"] + $this->Sheet->margin;
        return $out;
    }

}
