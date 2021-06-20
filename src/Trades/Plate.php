<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

use \SSITU\Jack\Jack;

class Plate
{

    private $Spirit;

    private $width;
    private $height;

    private $lure1Bytes;
    private $lure2Bytes;
    private $encod_limits;
    private $pos_len;
    private $lure1_len;
    private $lure2_len;
    private $extradata_len;

    public function __construct($Spirit)
    {
        $this->Spirit = $Spirit;

    }

    private function prcIntParam($paramMap)
    {
        foreach ($paramMap as $prop => $param) {
          
            if (Jack::Help()->isPostvInt($param)) {
                $this->$prop = (int) $param;
            } else {
                $this->Spirit->record('invalid-' . $prop);
                return false;
            }
        }
        return true;
    }

    private function setRandLureBytes()
    {
        $maxLureBytes = ($this->encod_limits * 5) / 100;

        //$this->lure1Bytes = random_int(3, $maxLureBytes);
        //$this->lure2Bytes = random_int(6, $maxLureBytes);
        $this->lure1Bytes = 3;
        $this->lure2Bytes = 6;
    }

    public function setConfig($width = null, $height = null, $lure1Bytes = null, $lure2Bytes = null)
    {
        if (empty($width) || empty($height)) {
          
          $width = $this->Spirit->config('width');
            $height = $this->Spirit->config('height');
        }
        if(!$this->prcIntParam(['width' => $width, 'height' => $height])){           
            return false;
        }

        $this->encod_limits = $this->width * $this->height;
        $this->pos_len = Jack::Help()->intlen($this->encod_limits);

        if (empty($lure1Bytes) || empty($lure2Bytes)) {
         $this->setRandLureBytes();
        } elseif(!$this->prcIntParam(['lure1Bytes' => $lure1Bytes, 'lure2Bytes' => $lure2Bytes])){
            return false;
        }

        $this->lure1_len = Utils::b64length($this->lure1Bytes);
        $this->lure2_len = Utils::b64length($this->lure2Bytes);

        $this->extradata_len = $this->lure1_len + $this->lure2_len + $this->pos_len * 2;

        return true;
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }

}
