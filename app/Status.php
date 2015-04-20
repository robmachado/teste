<?php

namespace App;

use nfephp\NFe\ToolsNFe;
use nfephp\Common\DateTime\DateTime;
use nfephp\Common\Files\FilesFolders;

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(dirname(__FILE__)));
}
class Status
{
    public static $certTS = 0;
    
    public static function verifica($config)
    {
        $aRetorno = array();
        if (is_file(APP_ROOT.'/base/status.json')) {
            $aRetorno = (array) json_decode(FilesFolders::readFile(APP_ROOT.'/base/status.json'));
        }
        $nfe = new ToolsNFe($config);
        self::$certTS = $nfe->certExpireTimestamp;

        $tstmp = DateTime::convertSefazTimeToTimestamp($aRetorno['dhRecbto']);
        $tsnow = time();
        $dif = ($tsnow - $tstmp);
        if ($dif > 3600) {
            $nfe->sefazStatus('', '', $aRetorno);
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
    
    public static function getExpirDate()
    {
        $data = date('d/m/Y', self::$certTS);
        $hoje = date('Y-m-d');
        $diferenca = self::$certTS - strtotime($hoje);
        $dias = floor($diferenca / (60 * 60 * 24));
        $htmlCert = "<p class=\"smallgreen\">Certificado expira em $dias dias [$data]</p>";
        if ($dias < 31) {
            $htmlCert = "<p class=\"smallred\">Certificado expira em $dias dias [$data]</p>";
        }
        return $htmlCert;
    }
}
