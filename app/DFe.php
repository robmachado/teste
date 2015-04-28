<?php

namespace App;

use NFePHP\NFe\ToolsNFe;
use NFePHP\Common\Dom\Dom;
use NFePHP\Common\Files\FilesFolders;
use NFePHP\Common\DateTime\DateTime;

if (!defined('PATH_ROOT')) {
    define('PATH_ROOT', dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR);
}

class DFe
{
    public $tools;
    public $ultNSU = 0;
    public $maxNSU = 0;
    public $nsuFilePath = '';
    public $nsuFileName = 'nsu.json';
    public $ambiente = 'homologacao';
    public $pathNFe = '';
    public $tpAmb = '2';
    
    public function __construct()
    {
        $this->tools = new ToolsNFe('../config/config.json');
        $this->tools->setModelo('55');
        $this->ambiente = $this->tools->ambiente;
        $this->pathNFe = $this->tools->aConfig['pathNFeFiles'];
        $this->tpAmb = $this->tools->aConfig['tpAmb'];
        $this->nsuFilePath = PATH_ROOT.'base'.DIRECTORY_SEPARATOR;
        $this->getNSU();
    }
    
    public function getNSU()
    {
        $file = $this->nsuFilePath . $this->nsuFileName;
        if (! is_file($file)) {
            $aNSU = array('ultNSU' => 0, 'maxNSU' => 0);
            $nsuJson = json_encode($aNSU);
            FilesFolders::saveFile($this->nsuFilePath, $this->nsuFileName, $nsuJson);
        }
        $nsuJson = json_decode(FilesFolders::readFile($this->nsuFilePath . $this->nsuFileName));
        $this->ultNSU = (int) $nsuJson->ultNSU;
        $this->maxNSU = (int) $nsuJson->maxNSU;
    }
    
    public function putNSU($ultNSU = 0, $maxNSU = 0)
    {
        $aNSU = array('ultNSU' => $ultNSU, 'maxNSU' => $maxNSU);
        $nsuJson = json_encode($aNSU);
        FilesFolders::saveFile($this->nsuFilePath, $this->nsuFileName, $nsuJson);
    }
    
    public function getNFe($limit = 10, $bIncludeAnomes = false)
    {
        if ($this->ultNSU == $this->maxNSU) {
            $this->maxNSU++;
        }
        if ($limit > 100 || $limit == 0) {
            $limit = 10;
        }
        $cnpj = ''; //deixando vazio irá pegar o CNPJ default do config
        $descompactar = true;
        $iCount = 0;
        while ($this->ultNSU < $this->maxNSU) {
            $iCount++;
            if ($iCount > ($limit - 1)) {
                break;
            }
            //limpar a variavel de retorno
            $aResposta = array();
            $xml = $this->tools->sefazDistDFe(
                'AN',
                $this->tpAmb,
                $cnpj,
                $this->ultNSU,
                $this->numNSU,
                $aResposta,
                $descompactar
            );
            //se houve retorno de documentos com cStat = 138 entao prosseguir
            if ($aResposta['cStat'] == 138) {
                //carregar as variaveis de controle com base no retorno da SEFAZ
                $this->ultNSU = (int) $aResposta['ultNSU'];
                $this->maxNSU = (int) $aResposta['maxNSU'];
                $this->putNSU($this->ultNSU, $this->maxNSU);
                $aDocs = $this->zExtractNFe($aResposta['aDoc']);
                $this->zSalva($aDocs, $bIncludeAnomes);
            }
            sleep(3);
        }
    }
    
    protected function zSalva($aDocs = array(), $bIncludeAnomes = false)
    {
        $path = $this->pathNFe .
            DIRECTORY_SEPARATOR .
            $this->ambiente .
            DIRECTORY_SEPARATOR .
            "recebidas";
        foreach ($aDocs as $doc) {
            $anomes = $doc['anomes'];
            $chave =  $doc['chave'];
            $xml = $doc['xml'];
            if ($bIncludeAnomes) {
                $path .= DIRECTORY_SEPARATOR.$anomes;
            }
            $filename = "$chave-nfe.xml";
            echo "Salvando $filename \n";
            FilesFolders::saveFile($path, $filename, $xml);
        }
    }

    protected function zExtractNFe($docs = array())
    {
        $aResp = array();
        //para cada documento retornado
        foreach ($docs as $resp) {
            $content = '';
            $xmldata = '';
            //verificar se é uma NFe
            if (substr($resp['schema'], 0, 7) == 'procNFe') {
                $content = $resp['doc'];
                $dom = new Dom();
                $dom->loadXMLString($content);
                $chave = $dom->getChave();
                $data = $dom->getNodeValue('dhEmi');
                if ($data == '') {
                    $data = $dom->getNodeValue('dEmi');
                }
                $tsdhemi = DateTime::convertSefazTimeToTimestamp($data);
                $anomes = date('Ym', $tsdhemi);
                $xmldata = $dom->saveXML();
                $xmldata = str_replace(
                    '<?xml version="1.0"?>',
                    '<?xml version="1.0" encoding="utf-8"?>',
                    $xmldata
                );
                $aResp[] = array(
                    'chave'=>$chave,
                    'anomes' => $anomes,
                    'xml' => $xmldata
                );
            }
        }
        return $aResp;
    }
}
