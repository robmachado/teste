<?php
namespace App;

/**
 * Classe para preparar e enviar o email aos destinatarios das NFe
 * 
 * @category   Application
 * @package    robmachado\teste
 * @copyright  Copyright (c) 2008-2015
 * @license    http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author     Roberto L. Machado <linux.rlm at gmail dot com>
 * @link       http://github.com/robmachado/teste for the canonical source repository
 */

use NFePHP\NFe\MailNFe;
use NFePHP\Common\Files;

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
    
    /**
     * envia
     * rotina de envio do email com o xml da NFe
     * @param string $fileNfePath
     * @param string $addresses
     * @param boolean $comPdf
     * @param string $pathPdf
     * @return string
     */
    public function envia($fileNfePath = '', $addresses = '', $comPdf = false, $pathPdf = '')
    {
        $aPara = array();
        if (! is_file($fileNfePath)) {
            return '';
        }
        $addresses = str_replace(',', ';', $addresses);
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
