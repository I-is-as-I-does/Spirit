<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

use \SSITU\Spirit\Spirit;

class Plate extends Spirit
{

    private $Spirit;

    private $width;
    private $height;

    private $minlen;
    private $poslen;
    private $minfillerBytes;
    private $encod_limits;
    private $keyLength; # min. 8

    protected function __construct($Spirit)
    {
        $this->Spirit = $Spirit;
    }

    public function setReadConfig($config)
    {    
        foreach($config as $prop => $param){
            if(!$this->prcIntParam($prop, $param)){
                return false;
            }
        }
        $this->minfillerBytes = 1;
        return $this->setLimits();
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }

    public function setPrintConfig()
    {
        foreach (['width', 'height', 'minfillerBytes', 'keyLength'] as $prop) {
            $param = $this->Spirit->config($prop);
            if(!$this->prcIntParam($prop, $param)){
                return false;
            }
        }
        return $this->setLimits();
    }

    private function setLimits()
    {
        $this->encod_limits = $this->width * $this->height;
        $this->poslen = strlen($this->encod_limits);
        $this->minlen = $this->poslen + ($this->minfillerBytes*2);
        if($this->minlen + 1 > $this->encod_limits){
            $this->Spirit->record('image-is-too-small');
            return false;
        }
        return true;     
    }

    private function prcIntParam($prop, $param)
    {     
            if (Utils::isPostvInt($param) && ($prop != 'keyLength' || $param > 8)) {
                $this->$prop = (int) $param;
                return true;
            } 
                $this->Spirit->record('invalid-' . $prop);
                return false;
    }

}
