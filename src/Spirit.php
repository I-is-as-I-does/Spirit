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

    private $Templates;

    // @doc: model = ['YYYYMMDD','url', 'passtitle', 'username'];

    public function __construct($config)
    {
       
        if (is_array($config)) {
            $this->initOverload($config);
        } else {
            $this->logs[] = 'invalid config';
        }

        if(!empty($config['Sod']) && !empty($config['Sod']['cryptKey'])){
            $Sod = new Sod($config['Sod']);
            $this->setSod($Sod);
        }
        if(!empty($config['defaultTemplate'])){
            $this->setTemplate($config['defaultTemplate']);
        }
    }

    private function getTemplate($templateName)
    {
       if(array_key_exists($templateName, $this->config['templates'])){
           if(empty($this->Templates[$templateName])){
            $className = 'Templates\\'.$templateName;
            if(!class_exists($className)){
                $this->logs[] = 'unknown template class';
                return false;
            }
                $tmpltConfig = $this->config['templates'][$templateName];

                $this->Plate()->setConfig($tmpltConfig);

                $template = new $className($this);
                $template->setConfig($tmpltConfig);
                $this->Templates[$templateName] = $template;
                
            }
            return $this->Templates[$templateName];
           }
       $this->logs[] = 'unknown template';
       return false;
    }

    public function setSod($Sod)
    {
        if (is_object($Sod) && method_exists($Sod, 'hasCryptKey') && $Sod->hasCryptKey()) {
            $this->Sod = $Sod;
            return true;
        }
        $this->logs[] = 'invalid Sod';
        return false;
    }

    private function getRscImg($img)
    {
        if(is_file($img)){
            $img = Jack::Images()->fileTob64($img);
            if($img === false){
                return false;
            }
        }
            return Jack::Images()->b64ToRsrc($img);
    }

    public function readImg($templateName, $img)
    {
        if (empty($this->logs)) {
           $template = $this->getTemplate($templateName);
           if($template !== false){
            $rscImg = $this->getRscImg($img);
            if($rscImg !== false){
                return $this->Reader()->readImg($rscImg);
            }
            $this->logs[] = 'invalid image'; 
           }
          
        }
        return false;
    }

    public function printImg($templateName, $dataToInject)
    {
        if (empty($this->logs)) {
            $template = $this->getTemplate($templateName);
            if($template !== false){
                $rscImg = $template->getRscImage();
            return $this->Printer()->printImg($rscImg, $dataToInject);
            }
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


    public function setTestMode($adminKey)
    {
        foreach ($this->config['keyHash'] as $hash) {
            if (password_verify($adminKey, $hash)) {
                $this->testMode = true;
                return true;
            }
        }
        $this->logs[] = 'invalid admin key';
        return false;
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
