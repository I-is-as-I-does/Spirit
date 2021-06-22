<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit;

class Spirit implements Spirit_i
{
    private $Reader;
    private $Printer;

    private $Sod;
    private $logs = [];

    public function __construct($Sod = null)
    {
        if ($Sod !== null) {
            $this->setSod($Sod);
        }
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
        if (empty($this->Reader)) {
            $this->Reader = new Trades\Reader($this);
        }
        return $this->Reader->read($img, $spiritKey);
    }

    public function printImg($dataToInject, $config)
    {
        if (empty($this->Printer)) {
            $this->Printer = new Trades\Printer($this);
        }
        return $this->Printer->print($dataToInject, $config);
    }

    public function getLogs()
    {
        return $this->logs;
    }

    public function record($log)
    {
        $this->logs[] = $log;
    }

    public function getSod()
    {
        if (empty($this->Sod)) {
            $this->logs[] = 'Sod is not set';
            return false;
        }
        return $this->Sod;
    }

}
