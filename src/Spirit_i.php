<?php

namespace SSITU\Spirit;

interface Spirit_i
{
    public function setSod($Sod);
    public function readImg($img, $spiritKey);
    public function printImg($dataToInject, $config, $spiritKey = null);
    public function getLogs();
}
