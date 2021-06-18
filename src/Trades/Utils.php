<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

class Utils
{

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

    public static function b64length($bytes)
    { //padded, aka with eventual ='s at the end (used to round up to a multiple of 4)
        return ((4 * $bytes / 3) + 3) & ~3;
    }

    public static function randFill($gap)
    {
        $b64 = base64_encode(random_bytes($gap));
        $out = str_replace('=', '+', $b64);
        return $out;
    }

    public static function b64bytes($char)
    {
        return ceil($char * 3 / 4);
    }

    public static function b64pad($value, $pad = '=') {
        while (strlen($value) % 4 > 0) {
          $value .= $pad;
        }
        return $value;
      }

}
