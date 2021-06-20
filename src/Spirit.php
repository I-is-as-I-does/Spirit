<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit;

use \SSITU\Jack\Jack;
use \SSITU\Sod\Sod;

class Spirit
{
    use \SSITU\Copperfield\FacadeOverload;

    private $testMode = false;
    private $Sod;

    private $signature;

    public function __construct()
    {
        $this->initOverload();
      
    }

    public function initSod($cryptKey, $flavour)
    {
        if (is_string($cryptKey) && in_array($flavour, ['Sodium', 'Sugar'])) {
            $Sod = new Sod(['cryptKey'=>$cryptKey,'flavour'=> $flavour]);

            if (method_exists($Sod, 'hasCryptKey') && $Sod->hasCryptKey()) {
                $this->Sod = $Sod;
                foreach(['cryptKey'=>$cryptKey, 'flavour'=>$flavour] as $prop =>$param){
                    if($param != $this->config[$prop]){
                        $this->config[$prop] = $param;
                    }
                }
               
                return true;
            }
        }
        $this->logs[] = 'invalid Sod config'; 
    return false;
    }    
    
    public function readImg($img, $signature)
    {    
        if (empty($this->logs)) {
           
            return $this->Reader()->readImg($img, $signature);
        }
        return false;
    }
   

    public function printImg($dataToInject, $config)
    {  
        if (is_array($config) && !empty($config['cryptKey']) && !empty($config['flavour'])) {
           $this->config = $config;
            $this->initSod($config['cryptKey'], $config['flavour']);
        } else {
            $this->logs[] = 'invalid config';
        }
       
        if (!is_string($dataToInject)) {
            $this->logs[] = 'data to inject must be a string';
        }
        if (empty($this->logs)) {
            return $this->Printer()->printImg($dataToInject);
        }
        return false;
    }

    public function getSod()
    {
        return $this->Sod;
    }

    public function getManual($lang, $inHtml = true)
    {
        $path = '../spirit-manual-' . $lang . '.json';
        if (!file_exists($path)) {
            if ($lang != 'en' && file_exists('../spirit-manual-en.json')) {
                $path = '../spirit-manual-en.json';
            } else {
                $this->logs[] = 'unable to get manual';
                return false;
            }
        }
        $manual = Jack::File()->readJson($path);
        if ($inHtml) {
            return implode('<br>', $manual);
        }
        return $manual;
    }
}
