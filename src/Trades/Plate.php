<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

trait Plate
{
    private $Sod;
    private $config;

    private $width;
    private $height;
    private $keyLength;

    private $poslen;
    private $encod_limits;

    
    private function isValidKey($spiritKey)
    {
       if(preg_match('/^[A-Za-z\d+\/]{'.$this->keyLength.'}$/',$spiritKey)){
           return true;
       }
       $this->Spirit->record('invalid-key');
       return false;
    }

    private function setPlateParam()
    {
        $this->Sod = $this->Spirit->getSod();
        if(empty($this->Sod)){
            return false;
        }
        foreach (['width', 'height', 'keyLength'] as $prop) {
            if (!$this->isValidParam($prop)) {
                $this->Spirit->record('invalid-' . $prop);
                return false;
            }
            $this->$prop = $this->config[$prop];
        }
        $this->setLimits();
        return true;
    }

    private function setLimits()
    {
        $this->encod_limits = $this->config['width'] * $this->config['height'];
        $this->poslen = strlen($this->encod_limits);
    }

    private function isValidParam($prop)
    {
        return (!empty($this->config[$prop]) && Utils::isPostvInt($this->config[$prop]));
    }

}
