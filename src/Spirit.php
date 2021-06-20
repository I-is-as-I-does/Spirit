<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit;

class Spirit
{
    use \SSITU\Copperfield\FacadeOverload;

    private $Sod;

    public function __construct()
    {
        $this->initOverload();
    }

    public function setSod($Sod)
    {
        if (method_exists($Sod, 'hasCryptKey') && $Sod->hasCryptKey()) {
            $this->Sod = $Sod;
            return true;
        }
        return false;
    }

    public function readImg($img, $spiritKey)
    {
        if (empty($this->Sod)) {
            $this->logs[] = 'Sod is not set';
        }
        if (!is_string($spiritKey)) {
            $this->logs[] = 'spiritKey must be a string';
        }
        if (empty($this->logs)) {
        
            return $this->Reader()->read($img, $spiritKey);
        }
        return false;
    }

    public function printImg($dataToInject, $config)
    {
        if (empty($this->Sod)) {
            $this->logs[] = 'Sod is not set';
        }
        if (!is_string($dataToInject)) {
            $this->logs[] = 'data to inject must be a string';
        }
        if (!is_array($config) || empty($config['width'])) {
            $this->logs[] = 'invalid config';
        }
        if (empty($this->logs)) {
            $this->config = $config;
            return $this->Printer()->print($dataToInject);
        }
        return false;
    }

    protected function getSod()
    {
        return $this->Sod;
    }

}
