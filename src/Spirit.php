<?php
/* This file is part of Spirit | SSITU | (c) 2021 I-is-as-I-does */
namespace SSITU\Spirit;

use \SSITU\Jack\Jack;

class Spirit
{
    use \SSITU\Copperfield\FacadeOverload;

    private $testMode = false;
    private $errLog = [];

    private $Sod;

    // @doc: model = ['YYYYMMDD','url', 'passtitle', 'username'];

    public function __construct($config)
    {
        if (is_array($config)) {
            $tradesPattern = __DIR__ . '/Trades/*[!(_i)|(_a)].php';
            $subNameSpace = __NAMESPACE__ . '\Trades';
            $this->initOverload($config, $tradesPattern, $subNameSpace);
        } else {
            $this->errLog[] = 'invalid config';
        }
    }

    public function addToLog($err)
    {
        $this->errLog[] = $err;
    }

    public function setSod($Sod)
    {
        if (is_object($Sod) && method_exists($Sod, 'hasCryptKey') && $Sod->hasCryptKey()) {
            $this->Sod = $Sod;
            return true;
        }
        $this->errLog[] = 'invalid Sod';
        return false;
    }

    public function readPassport($img)
    {
        if (empty($this->errLog)) {
           return $this->Reader()->readPassport($img);
        }
        return false;
    }

    public function getSod()
    {
        return $this->Sod;
    }

    public function inTestMode()
    {
       return $this->testMode;
    }

    public function printPassport($accessUrl, $parentNickname, $nextNicknames)
    {
        if (empty($this->errLog)) {

            return $this->Printer()->printPassport($accessUrl, $parentNickname, $nextNicknames);
        }
        return false;
    }

    public function getErrLog()
    {
        return $this->errLog;
    }

    public function setTestMode($adminKey)
    {
        foreach ($this->config['adminKeyHashes'] as $hash) {
            if (password_verify($adminKey, $hash)) {
                $this->testMode = true;
                return true;
            }
        }
        $this->errLog[] = 'invalid admin key';
        return false;
    }

    public function getManual($lang, $inHtml = true)
    {
        $path = '../spirit-manual-' . $lang . '.json';
        if (!file_exists($path)) {
            if ($lang != 'en' && file_exists('../spirit-manual-en.json')) {
                $path = '../spirit-manual-en.json';
            } else {
                $this->errLog[] = 'unable to get manual';
                return false;
            }
        }
        $manual = Jack::File()->readJson($path);
        if ($inHtml) {
            return implode('<br>', $manual);
        }
        return $manual;
    }

/* todo: feedabck
if ($is_super or in_array($last_nicknm, SUPERS)) {
$this->rslt = 'Nouveau compte "super-user" dûment enregistré.<br>Nouveau Pass généré ; vous pouvez cliquer sur l\'image pour le télécharger.';
$this->rslt_en = 'New "super-user" account duly registered.<br>New Pass generated; you can click on the image to download it.';
$storage = '.';
$storage_en = '.';
} else {
$this->rslt = 'Pass prêt ; vous pouvez cliquer sur l\'image pour le télécharger.';
$this->rslt_en = 'Pass ready; you can click on the image to download it.';
$storage = ' <span class="aside">(car nous ne stockons aucune donnée vous concernant de notre côté !)</span>';
$storage_en = ' <span class="aside">(since we do not store any data about you on our side!)</span>';
}
$manl_fr = '<p><span class="aside">Mini-manuel :</span><br>
&rarr;  Un Pass peut être dupliqué et renommé à volonté<br>
&rarr;  Un Pass ne doit toutefois pas être retouché, compressé, ou enregistré sous un autre format<br>
&rarr;  Un Pass perdu requiert la création d\'un nouveau Pass' . $storage . '</p>';
$manl_en = '<p class="eng"><span class="aside">Mini-manual:</span><br>
&rarr;  A Pass can be duplicated and renamed at will<br>
&rarr;  However, a Pass must not be edited, compressed, or saved in another format <br>
&rarr;  A lost Pass requires the creation of a new Pass' . $storage_en . '</p>';
$this->rslt_adt = $manl_fr . $manl_en . '<a href="' . $b64pass . '" download="blaank.page.' . $last_nicknm . '.pass.png"><img src="' . $b64pass . '" class="pass-img" alt="blaank.page.' . $last_nicknm . '.pass"></a>';
$this->rslt_status = ' newpass-success';
$this->Feedback();
 */

}
