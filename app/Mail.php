<?php
namespace App;

use NFe\MailNFe;
use Common\Files;

class Mail
{
    public $aMailConf = array();
    public $error = '';
    
    public function __construct($configJson = '')
    {
        if ($configJson == '') {
            $configJson = Files\FilesFolders::readFile('../config/config.json');
        }
        $aConfig = (array) json_decode($configJson);
        $this->aMailConf  = (array) $aConfig['aMailConf'];
    }
    
    public function envia($fileNfePath = '', $addresses = '', $comPdf = false, $pathPdf = '')
    {
        $aPara = array();
        if (! is_file($fileNfePath)) {
            return '';
        }
        if (! is_array($addresses)) {
            $aPara = explode(';', $addresses);
        } else {
            $aPara = $addresses;
        }
        if (! is_file($pathPdf)) {
            $pathPdf = '';
            $comPdf = false;
        }
        $objMail = new MailNFe($this->aMailConf);
        $resp = $objMail->envia($fileNfePath, $aPara, $comPdf, $pathPdf);
        $this->error = $objMail->error;
        return $resp;
    }
}
