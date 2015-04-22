<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
include_once '../../bootstrap.php';

//esta e a rotina para baixar as NFe pelo DistDFe
//deve ser executada via CRONTAB a cada 2 ou 3 horas no maximo

use NFe\ToolsNFe;
use Common\Dom\Dom;
use Common\Files\FilesFolders;
use Common\DateTime\DateTime;

$nfe = new ToolsNFe('../config/config.json');
$nfe->setModelo('55');

//pegar o ultNSU
include_once 'nsu.php';
if (isset($ultNSUg)) {
    $ultNSU = $ultNSUg;
} else {
    $ultNSU = 0;
}
if (isset($maxNSUg)) {
    $maxNSU = $maxNSUg;
} else {
    $maxNSU = $ultNSU;
}
//força maxNSU ser maior que ultNSU
//isso permite que entre no loop
if ($maxNSU == $ultNSU) {
    $maxNSU = $ultNSU + 1;
}
$numNSU = 0;
$tpAmb = '1';
$cnpj = ''; //deixando vazio irá pegar o CNPJ default do config
$descompactar = true;
$iCount = 0;
while ($ultNSU < $maxNSU) {
    //no maximo 10 interaçoes desse loop sao permitidas de cada vez
    //isso e para evitar em casso de alguma falha o loop infinito
    $iCount++;
    if ($iCount > 9) {
        break;
    }
    //limpar a variavel de retorno
    $aResposta = array();
    $xml = $nfe->sefazDistDFe('AN', $tpAmb, $cnpj, $ultNSU, $numNSU, $aResposta, $descompactar);
    //se houve retorno de documentos com cStat = 138 entao prosseguir
    if ($aResposta['cStat'] == 138) {
        //carregar as variaveis de controle com base no retorno da SEFAZ
        $ultNSU = $aResposta['ultNSU'];
        $maxNSU = $aResposta['maxNSU'];
        //salvar as informaçoes para a proxima vez que a rotina for chamada
        $data = "<?php\n".'$ultNSUg='.$ultNSU.";\n".'$maxNSUg='.$maxNSU.";\n";
        file_put_contents('nsu.php', $data);
        //se documentos foram retornados entao avaliar
        if (count($aResposta['aDoc']) > 0) {
            //para cada documentos retornado
            foreach ($aResposta['aDoc'] as $resp) {
                //verificar se e uma NFe 
                if (substr($resp['schema'], 0, 7) == 'procNFe') {
                    $content = $resp['doc'];
                    $dom = new Dom('1.0', 'utf-8');
                    $dom->loadXMLString($content);
                    $chave = $dom->getChave();
                    $data = $dom->getNodeValue('dhEmi');
                    if ($data == '') {
                        $data = $dom->getNodeValue('dEmi');
                    }
                    $tsdhemi = DateTime::convertSefazTimeToTimestamp($data);
                    $anomes = date('Ym', $tsdhemi);
                    //monta o caminho para salvar o arquivo
                    $path = "/var/www/nfe/producao/recebidas/$anomes";
                    $filename = "$chave-nfe.xml";
                    $content = str_replace('<?xml version="1.0"?>', '<?xml version="1.0" encoding="utf-8"?>', $content);
                    FilesFolders::saveFile($path, $filename, $content);
                }
            }
        }
    }
    //manter um intervalo entre cada busca do loop
    sleep(5);
}
