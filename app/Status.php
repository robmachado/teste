<?php

namespace App;

/**
 * Classe para buscar o status da SEFAZ e a validade do certificado digital
 * 
 * @category   Application
 * @package    robmachado\teste
 * @copyright  Copyright (c) 2008-2015
 * @license    http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author     Roberto L. Machado <linux.rlm at gmail dot com>
 * @link       http://github.com/robmachado/teste for the canonical source repository
 */

use NFePHP\NFe\ToolsNFe;
use NFePHP\Common\DateTime\DateTime;
use NFePHP\Common\Files\FilesFolders;

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(dirname(__FILE__)));
}

class Status
{
    public static $certTS = 0;
    protected static $nfe;
    protected static $config;
    
    /**
     * verifica
     * Verifica o status da SEFAZ com o webservice e retorna tags html com o resultado
     * @param string $config json do arquivo de configuração
     * @return string
     */
    public static function verifica($config = '')
    {
        $aRetorno = array();
        if (empty($config)) {
            return '';
        }
        self::$config = $config;
        if (is_file(APP_ROOT.'/base/status.json')) {
            $aRetorno = (array) json_decode(FilesFolders::readFile(APP_ROOT.'/base/status.json'));
        }
        $tstmp = DateTime::convertSefazTimeToTimestamp($aRetorno['dhRecbto']);
        $tsnow = time();
        $dif = ($tsnow - $tstmp);
        //caso tenha passado mais de uma hora desde a ultima verificação
        if ($dif > 3600) {
            self::$nfe = new ToolsNFe($config);
            self::$certTS = self::$nfe->certExpireTimestamp;
            self::$nfe->sefazStatus('', '', $aRetorno);
            $retJson = json_encode($aRetorno);
            FilesFolders::saveFile(APP_ROOT.'/base', 'status.json', $retJson);
        }
        $tstmp = DateTime::convertSefazTimeToTimestamp($aRetorno['dhRecbto']);
        $dhora = date('d/m/Y H:i:s', $tstmp);
        $htmlStatus = "<p class=\"smallred\">OFF-LINE</p>\n<p class=\"smallred\">$dhora</p>";
        if ($aRetorno['cStat'] == '107') {
            $htmlStatus = "<p class=\"smallgreen\">On-Line</p>\n<p class=\"smallgreen\">$dhora</p>";
        }
        return $htmlStatus;
    }
    
    /**
     * getExpirDate
     * Busca a data de expiração do certificado usado
     * e retorna uma tag html formatada
     * @return string
     */
    public static function getExpirDate()
    {
        if (empty(self::$nfe) && ! empty(self::$config)) {
            self::$nfe = new ToolsNFe(self::$config);
            self::$certTS = self::$nfe->certExpireTimestamp;
        }
        $data = date('d/m/Y', self::$certTS);
        $hoje = date('Y-m-d');
        if (self::$certTS) {
            
        }
        $diferenca = self::$certTS - strtotime($hoje);
        $dias = floor($diferenca / (60 * 60 * 24));
        $htmlCert = "<p class=\"smallgreen\">Certificado expira em $dias dias [$data]</p>";
        if ($dias < 31) {
            $htmlCert = "<p class=\"smallred\">Certificado expira em $dias dias [$data]</p>";
        }
        return $htmlCert;
    }
}
