<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

use \SSITU\Jack\Jack;

class Printer
{

    private $Spirit;
    private $Plate;
    private $Sheet;


    public function __construct($Spirit)
    {
        $this->Spirit = $Spirit;
        $this->Plate = $Spirit->Plate();
        $this->Sheet = $Spirit->Sheet();

    }

    public function printPassport($accessUrl, $parentNickname, $nextNicknames = [])
    {

        $lines = $this->getLines($parentNickname, $nextNicknames);
        $cryptdata = $this->cryptData($accessUrl, $lines);

        if ($cryptdata === false) {
          
            $this->Spirit->addToLog('Sod encrypt error');
            return false;
        }

        $image = $this->passDraw($lines, $accessUrl);
        $stegaImage = $this->StegaData($image, $cryptdata);

        if ($stegaImage === false) {
            $this->Spirit->addToLog('Too much data to inject in too small image');
            return false;
        }

        return $stegaImage;
    }

    private function cryptData($accessUrl, $lines)
    {
        $indata = implode('|', [date('Ymd'), $accessUrl, $this->Sheet->passTitle, implode(' ', $lines)]);
        return $this->Spirit->getSod()->encrypt($indata);
    }

    private function getLines($parentNickname, $nextNicknames)
    {
        if (empty($nextNicknames)) {
            return [$parentNickname];
        }

        array_unshift($nextNicknames, $parentNickname);

        $chardim = $this->boxDim('M'); // @doc maj M is considered a good "template"
        $maxchars = floor($this->Sheet->maxWidth / $chardim["w"]);

        $wrapncks = wordwrap(implode(' ', $nextNicknames), $maxchars);
        $lines = explode("\n", $wrapncks);
        $maxdiff = $this->Sheet->baseDiff - count($lines);
        if ($maxdiff < 0) {
            $lines = array_slice($lines, 0, $maxdiff);
            $lastline = trim(array_pop($lines)) . ' [...]';
            $lines[] = $lastline;
        }
        return $lines;
    }

    private function drawBaseImage($accessUrl)
    {
        $image = imagecreatetruecolor($this->Plate->width, $this->Plate->height);
        $textcolor = imagecolorallocate($image, ...$this->Sheet->textColorCodes);
        $bgcolor = imagecolorallocate($image, ...$this->Sheet->bgColorCodes);
        $linecolor = imagecolorallocate($image, ...$this->Sheet->lineColorCodes);

        $endx = $this->xPoz($accessUrl);
        $headx = $this->xPoz($this->Sheet->passTitle);
        /* bg */
        imagefilledrectangle($image, 0, 0, $this->Plate->width, $this->Plate->height, $bgcolor);
        /* frame */
        imagerectangle($image, $this->Sheet->margin, $this->Sheet->margin, $this->Plate->width - $this->Sheet->margin, $this->Plate->height - $this->Sheet->margin, $linecolor);
        /* frame horizontal "eraser" (to keep only angles) */
        imagefilledrectangle($image, 0, $this->Sheet->margin * 3, $this->Plate->width, $this->Plate->height - $this->Sheet->margin * 3, $bgcolor);

        /* top frame eraser, width based on header width  */
        imagefilledrectangle($image, $headx["maskx1"], 0, $headx["maskx2"], $this->Sheet->heady, $bgcolor);
        /* header */
        imagettftext($image, $this->Sheet->fontSize, $this->Sheet->angle, $headx["xmid"], $this->Sheet->heady, $textcolor, $this->Sheet->fontFile, $this->Sheet->passTitle);

        /* bottom frame eraser, width based on footer width  */
        imagefilledrectangle($image, $endx["maskx1"], $this->Sheet->maskendy, $endx["maskx2"], $this->Plate->height, $bgcolor);
        /* footer  */
        imagettftext($image, $this->Sheet->fontSize, $this->Sheet->angle, $endx["xmid"], $this->Sheet->endy, $textcolor, $this->Sheet->fontFile, $accessUrl);

        return ['image' => $image, 'textcolor' => $textcolor];
    }

    private function passDraw($lines, $accessUrl)
    {
        $bases = $this->drawBaseImage($accessUrl);
        $image = $bases['image'];
        $textcolor = $bases['textcolor'];

        $linescount = count($lines);

        /* nicknames inheritance lines */
        $y = ($this->Plate->height - $linescount * $this->Sheet->lineHeight) / 2; //@doc: y pos. of the middle of 1st line
        $y += $this->Sheet->y_adjust; // @doc: adjustments to get the line bottom y pos.
        if ($linescount === 1) {
            $solox = $this->xPoz($lines[0]);
            imagettftext($image, $this->Sheet->fontSize, $this->Sheet->angle, $solox["xmid"], $y, $textcolor, $this->Sheet->fontFile, $lines[0]);
        } else {
            foreach ($lines as $text) {
                $text = str_replace(' ', '  ', $text); //@doc: a bit more spacing between nicknames //@todo: check if it does not wreck previous availbl space logic
                imagettftext($image, $this->Sheet->fontSize, $this->Sheet->angle, $this->Sheet->margin, $y, $textcolor, $this->Sheet->fontFile, $text);
                $y += $this->Sheet->lineHeight;
            }
        }
        return $image;
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

    private function rewritePx($img, $cryptdataBin)
    {

        $pixelX = 0;
        $pixelY = $this->Plate->encod_y;

        for ($x = 0; $x < $this->Plate->encod_limits; $x++) { // @doc: loop through pixel by pixel
            if ($pixelX === $this->Plate->width) { // @doc: if this is true, we've reached the end of the row of pixels, start on next row
                $pixelY++;
                $pixelX = 0;
            }
            $rgb = imagecolorat($img, $pixelX, $pixelY); // @doc: color of the pixel at the x and y positions
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;

            $binBlue = Utils::toBin($b);
            $leastBit = strlen($binBlue) - 1;

            $binBlue[$leastBit] = $cryptdataBin[$x]; // @doc: change least significant bit with the bit from data
            $newBlue = Utils::toHex($binBlue);

            $new_color = imagecolorallocate($img, $r, $g, $newBlue); // @doc: swap pixel with new pixel that has its blue lsb changed (looks the same)
            imagesetpixel($img, $pixelX, $pixelY, $new_color); // @doc: set the color at the x and y positions

            $pixelX++; // @doc: change x coordinates to next
        }
        return $img;
    }

    private function StegaData($img, $cryptdata)
    {
        /* @doc: to maintain a small filesize, just a part of the pass is encoded;
        if more data needs to be injected, full potential is:
        - $this->Plate->encod_limits = ($this->Plate->width-1) * ($this->Plate->height-1);
        - $this->Plate->encod_y = 0; */

        $adtchar = '';
        $padlen = 0;
        $pads = strstr($cryptdata, '=');
        if ($pads !== false) {
            $cryptdata = rtrim($cryptdata, '=');
            $padlen = strlen($pads);
            $adtchar .= Jack::Help()->multRandLetters($padlen);
        }
        $datalen = strlen($cryptdata);
        $realdata_len = $this->Plate->info_len + $datalen + $padlen;

        // @doc: * 8 bits
        if (($realdata_len + $this->Sheet->minfiller_len) * 8 > $this->Plate->encod_limits) {
            $this->Spirit->addToLog('too much data to inject in a too small img');
            return false;
        }

        $file_bytes = Utils::b64bytes(($this->Plate->encod_limits / 8) - $realdata_len);
        $file_filler = Utils::randFill($file_bytes);

        $maxpoz = strlen($file_filler) - $datalen - $padlen - $this->Sheet->minfiller_len;
        $randpoz = rand($this->Sheet->minfiller_len, $maxpoz);
        $startpoz = $randpoz + $this->Plate->info_len;

        $hiddendata = substr_replace($file_filler, $cryptdata . $adtchar, $randpoz, 0);

        $frmt_startpoz = Jack::Help()->b64pad($startpoz, Jack::Help()->randLetter());
        $frmt_lengtpoz = Jack::Help()->b64pad($datalen, Jack::Help()->randLetter());

        $toinject = $this->Sheet->lure1 . $frmt_startpoz . $this->Sheet->lure2 . $frmt_lengtpoz . $hiddendata;
        $cryptdataBin = Utils::toBin($toinject);
        $img = $this->rewritePx($img, $cryptdataBin);

        $out_b64img = Utils::b64Img($img);
        imagedestroy($img);

        if ($this->Spirit->inTestMode()) {
            return $this->testStegaData($startpoz, $datalen, $toinject, $out_b64img);
        }
        return $out_b64img;
    }

    private function testStegaData($startpoz, $datalen, $toinject, $out_b64img)
    {
//@doc: for testing purposes:
        echo implode('<br>', ['start position: ' . $startpoz, 'data length: ' . $datalen, 'file limits: ' . $this->Plate->encod_limits, 'injected data length in bytes: ' . strlen($toinject) * 8, 'result: ', '<img src="' . $out_b64img . '">']);
    }

}
