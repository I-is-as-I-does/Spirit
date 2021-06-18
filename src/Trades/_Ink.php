<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit\Trades;

use \SSITU\Spirit\Interfaces\_Template;

class _Ink implements _Template
{

    private $Spirit;
    private $Plate;


    public function __construct($Spirit)
    {
        $this->Spirit = $Spirit;
        $this->Plate = $this->Spirit->Plate();
    

    }

    public function setConfig($config)
    {
  
    }

 
    public function getRscImage()
    {

      

        return $image;
    }

   

}
