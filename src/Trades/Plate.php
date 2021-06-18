<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

use \SSITU\Jack\Jack;

class Plate
{

    # common config defaults
    private $width = 286;
    private $height = 186;
    private $pos_len = 3;
    private $lure1_bytes = 3;
    private $lure2_bytes = 6;

    # common calculated values
    private $encod_limits;
    private $encod_y;
    private $lure1_len;
    private $lure2_len;
    private $info_len;


    public function __construct($Spirit)
    {
     
        foreach (['pos_len', 'lure2_bytes', 'lure1_bytes'] as $param) {
            if (Jack::Help()->isPostvInt($Spirit->config($param))) {
                $this->$param = $Spirit->config($param);
            }
        }
        
        $this->lure1_len = Utils::b64length($this->lure1_bytes);
        $this->lure2_len = Utils::b64length($this->lure2_bytes);
        $this->info_len = $this->lure1_len + $this->lure2_len + $this->pos_len * 2;

    }

    
    public function setConfig($config){
      
        foreach (['width' 'height'] as $param) {
            if (!empty($config[$param]) && Jack::Help()->isPostvInt($config[$param])) {
                $this->$param = $config[$param];
            }
        }
   
        $this->encod_y = floor($this->height / 3);
        $this->encod_limits = $this->width * $this->encod_y;      
   
}

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }

}