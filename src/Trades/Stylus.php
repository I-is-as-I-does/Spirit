<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

use \SSITU\Spirit\Spirit;

class Stylus extends Spirit
{

    private $Spirit;
    private $Plate;
    private $Sheet;

    protected function __construct($Spirit)
    {
        $this->Spirit = $Spirit;
    }

    public function writeTexts($rscImg)
    {
        $this->Plate = $this->Spirit->Plate();
        $this->Sheet = $this->Spirit->Sheet();

        $textColor = imagecolorallocate($rscImg, ...$this->Sheet->textColorCodes);

        if (!empty($this->Sheet->texts['headerText'])) {
            imagettftext($rscImg, $this->Sheet->fontSize, $this->Sheet->angle, $this->Sheet->headx["xmid"], $this->Sheet->heady, $textColor, $this->Sheet->fontFilePath, $this->Sheet->texts['headerText']);
        }
        if (!empty($this->Sheet->texts['footerText'])) {
            imagettftext($rscImg, $this->Sheet->fontSize, $this->Sheet->angle, $this->Sheet->endx["xmid"], $this->Sheet->endy, $textColor, $this->Sheet->fontFilePath, $this->Sheet->texts['footerText']);
        }
        if (!empty($this->Sheet->texts['mainText']) || !empty($this->Sheet->texts['addtTexts'])) {
            $rscImg = $this->imprint($rscImg, $textColor);
        }
        return $rscImg;
    }

    private function getLines()
    {
        $lines = [$this->Sheet->texts['mainText']];
        if (!empty($this->Sheet->texts['addtTexts'])) {

            $chardim = $this->Sheet->boxDim('M'); // @doc maj M is considered a good "template"
            $maxchars = floor($this->Sheet->maxWidth / $chardim["w"]);
            foreach ($this->Sheet->texts['addtTexts'] as $addt) {
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

    private function imprint($rscImg, $textColor)
    {
        $lines = $this->getLines();
        $linescount = count($lines);

        $y = ($this->Plate->height - $linescount * $this->Sheet->lineHeight) / 2; //@doc: y pos. of the middle of 1st line
        $y += $this->Sheet->y_adjust; // @doc: adjustments to get the line bottom y pos.

        foreach ($lines as $text) {
            $solox = $this->Sheet->xPoz($text);
            imagettftext($rscImg, $this->Sheet->fontSize, $this->Sheet->angle, $solox["xmid"], $y, $textColor, $this->Sheet->fontFilePath, $text);
            $y += $this->Sheet->lineHeight;
        }

        return $rscImg;
    }

}
