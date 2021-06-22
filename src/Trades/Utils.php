<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

class Utils
{

    public static function isPostvInt($value)
    { //  @doc works even if $value is a string-integer
        return ((is_int($value) || ctype_digit($value)) && (int) $value > 0);
    }

    public static function b64ToRsrc($dataimg)
    {
        if (stripos($dataimg, 'data:image/') === false) {
            return false;
        }
        $dataimg = preg_replace('/^data:image\/[a-z]+;base64/', '', $dataimg);
        $dataimg = str_replace(' ', '+', $dataimg);
        $decdimg = base64_decode($dataimg);
        if ($decdimg !== false) {
            return imagecreatefromstring($decdimg);
        }
        return false;
    }

    public static function rscTob64($rscImg, $ext)
    {
        $ext = strtolower($ext);
        if ($ext === 'jpg') {
            $ext = 'jpeg';
        }
        // @doc: buffering is required; can't directly base64 encode the img resource
        ob_start();
        switch ($ext) {
            case 'png':
                imagepng($rscImg);
                break;
            case 'gif':
                imagegif($rscImg);
                break;
            case 'webp':
                imagewebp($rscImg);
                break;
            case 'jpeg':
                imagejpeg($rscImg);
                break;
            default:
                '';
        }
        $contents = ob_get_contents();
        ob_end_clean();

        if (empty($contents)) {
            return false;
        }
        return 'data:image/' . $ext . ';base64,' . base64_encode($contents);
    }

    public static function pathToRsc($path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === 'jpg') {
            $ext = 'jpeg';
        }
        switch ($ext) {
            case 'png':
                $rscImg = imagecreatefrompng($path);
                break;
            case 'gif':
                $rscImg = imagecreatefromgif($path);
                break;
            case 'webp':
                $rscImg = imagecreatefromwebp($path);
                break;
            case 'jpeg':
                $rscImg = imagecreatefromjpeg($path);
                break;
            default:
                $rscImg = false;
        }
        return $rscImg;

    }

    public static function toBin($str)
    {
        $str = (string) $str;
        $l = strlen($str);
        $result = '';
        while ($l--) {
            $result = str_pad(decbin(ord($str[$l])), 8, "0", STR_PAD_LEFT) . $result;
        }
        return $result;
    }

    public static function toHex($str)
    {
        $text_array = explode("\r\n", chunk_split($str, 8));
        $newstring = '';
        for ($n = 0; $n < count($text_array) - 1; $n++) {
            $newstring .= chr(base_convert($text_array[$n], 2, 10));
        }
        return $newstring;
    }

    public static function addrandPad($targLen, $str)
    {
        while (strlen($str) < $targLen) {
            $a_z = "abcdefghijklmnopqrstuvwxyz";
            $str .= $a_z[random_int(0, 25)];
        }
        return $str;
    }

    public static function adjustLen($targLen, $str)
    {
        while (strlen($str) > $targLen) {
            $str = substr($str, 0, -1);
        }
        return self::addrandPad($targLen, $str);
    }

    public static function randFill($targLen)
    {
       $b64 = base64_encode(random_bytes(self::b64Len($targLen)));
       return self::adjustLen($targLen, rtrim($b64,'='));
       }

    public static function b64Len($char)
    {
        return floor($char * 3 / 4);
    }

    public static function b64pad($value, $pad = '=')
    {
        while (strlen($value) % 4 > 0) {
            $value .= $pad;
        }
        return $value;
    }

}
