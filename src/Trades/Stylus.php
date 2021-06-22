<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

class Stylus
{
    private $Printer;

    public function __construct($Printer)
    {
        $this->Printer = $Printer;
    }

    public function writeTexts($rscImg)
    {
        $textColor = imagecolorallocate($rscImg, ...$this->Printer->textColorCodes);

        if (!empty($this->Printer->texts['headerText'])) {
            imagettftext($rscImg, $this->Printer->fontSize, $this->Printer->angle, $this->Printer->headx["xmid"], $this->Printer->heady, $textColor, $this->Printer->fontFilePath, $this->Printer->texts['headerText']);
        }
        if (!empty($this->Printer->texts['footerText'])) {
            imagettftext($rscImg, $this->Printer->fontSize, $this->Printer->angle, $this->Printer->endx["xmid"], $this->Printer->endy, $textColor, $this->Printer->fontFilePath, $this->Printer->texts['footerText']);
        }
        if (!empty($this->Printer->texts['mainText']) || !empty($this->Printer->texts['addtTexts'])) {
            $rscImg = $this->imprint($rscImg, $textColor);
        }
        return $rscImg;
    }

    private function getLines()
    {
        $lines = [$this->Printer->texts['mainText']];
        if (!empty($this->Printer->texts['addtTexts'])) {

            $chardim = $this->Printer->boxDim('M'); // @doc maj M is considered a good "template"
            $maxchars = floor($this->Printer->maxWidth / $chardim["w"]);
            foreach ($this->Printer->texts['addtTexts'] as $addt) {
                $wrapncks = wordwrap($addt, $maxchars);
                $nlines = explode("\n", $wrapncks);
                $lines = array_merge($lines, $nlines);
            }
            $maxdiff = $this->Printer->baseDiff - count($lines);
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

        $y = ($this->Printer->height - $linescount * $this->Printer->lineHeight) / 2; //@doc: y pos. of the middle of 1st line
        $y += $this->Printer->y_adjust; // @doc: adjustments to get the line bottom y pos.

        foreach ($lines as $text) {
            $solox = $this->Printer->xPoz($text);
            imagettftext($rscImg, $this->Printer->fontSize, $this->Printer->angle, $solox["xmid"], $y, $textColor, $this->Printer->fontFilePath, $text);
            $y += $this->Printer->lineHeight;
        }

        return $rscImg;
    }

}
