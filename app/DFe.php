<?php

namespace App;

/**
 * Classe para buscar os documentos destinados
 * 
 * @category   Application
 * @package    robmachado\teste
 * @copyright  Copyright (c) 2008-2015
 * @license    http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author     Roberto L. Machado <linux.rlm at gmail dot com>
 * @link       http://github.com/robmachado/teste for the canonical source repository
 */

use NFePHP\NFe\ToolsNFe;
use NFePHP\Common\Dom\Dom;
use NFePHP\Common\Files\FilesFolders;
use NFePHP\Common\DateTime\DateTime;
use \DOMDocument;

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
    public $pathRes = '';
    public $tpAmb = '2';
    
    public function __construct()
    {
        $this->tools = new ToolsNFe('../config/config.json');
        $this->tools->setModelo('55');
        //caso a versão do PHP não possa identificar automaticamente
        //o protocolo a ser usado durante o handshake. Defina o protocolo.
        $this->tools->setSSLProtocol('SSLv3');
        
        $this->ambiente = $this->tools->ambiente;
        $this->pathNFe = $this->tools->aConfig['pathNFeFiles'];
        $this->pathRes = $this->tools->aConfig['pathNFeFiles'].DIRECTORY_SEPARATOR.$this->ambiente.DIRECTORY_SEPARATOR.'recebidas'.DIRECTORY_SEPARATOR.'resumo';
        $this->tpAmb = $this->tools->aConfig['tpAmb'];
        $this->nsuFilePath = PATH_ROOT.'base';
        $this->getNSU();
    }
    
    /**
     * getNSU
     * 
     * Carrega os numeros do ultNSU e de maxNSU 
     * gravados no arquivo da pasta base
     * Esses numeros são usados para continuação das buscas
     * no webservice de forma a trazer apenas os ultimos documentos
     * ainda não importados
     */
    public function getNSU()
    {
        $file = $this->nsuFilePath . $this->nsuFileName;
        if (! is_file($file)) {
            $aNSU = array('ultNSU' => 0, 'maxNSU' => 0);
            $nsuJson = json_encode($aNSU);
            FilesFolders::saveFile($this->nsuFilePath, $this->nsuFileName, $nsuJson);
        }
        $nsuFile = $this->nsuFilePath . DIRECTORY_SEPARATOR . $this->nsuFileName;
        $nsuJson = json_decode(FilesFolders::readFile($nsuFile));
        $this->ultNSU = (int) $nsuJson->ultNSU;
        $this->maxNSU = (int) $nsuJson->maxNSU;
    }
    
    /**
     * putNSU
     * Grava os numeros de ultNSU e maxNSU em um arquivo em formato json
     * para serem utilizados posteriormente em outras buscas
     * 
     * @param integer $ultNSU
     * @param integer $maxNSU
     */
    public function putNSU($ultNSU = 0, $maxNSU = 0)
    {
        //o valor perc é destinado a ser usado com um "progress bar"
        //em uma solicitação manual do usuário via página web
        $perc = 0;
        if ($maxNSU > 0) {
            $perc = round(($ultNSU/$maxNSU)*100, 0);
        }
        $aNSU = array('ultNSU' => $ultNSU, 'maxNSU' => $maxNSU, 'perc' => $perc);
        $nsuJson = json_encode($aNSU);
        FilesFolders::saveFile($this->nsuFilePath, $this->nsuFileName, $nsuJson);
    }
    
    /**
     * getNFe
     * Usa o webservice DistDFe da SEFAZ AN para trazer os docuentos destinados ao
     * CNPJ do config.json e salvar as NFe retornadas na pasta recebidas/<anomes>
     * 
     * @param int $limit
     * @param boolean $bIncludeAnomes
     */
    public function getNFe($limit = 10, $bIncludeAnomes = false)
    {
        if ($this->ultNSU == $this->maxNSU) {
            $this->maxNSU++;
        }
        if ($limit > 100 || $limit == 0) {
            $limit = 10;
        }
        $numNSU = 0;
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
            $this->tools->sefazDistDFe(
                'AN',
                $this->tpAmb,
                $cnpj,
                $this->ultNSU,
                $numNSU,
                $aResposta,
                $descompactar
            );
            //se houve retorno de documentos com cStat = 138 entao prosseguir
            if ($aResposta['cStat'] == 138) {
                //carregar as variaveis de controle com base no retorno da SEFAZ
                $this->ultNSU = (int) $aResposta['ultNSU'];
                $this->maxNSU = (int) $aResposta['maxNSU'];
                $this->putNSU($this->ultNSU, $this->maxNSU);
                $this->zExtractDocs($aResposta['aDoc'], $bIncludeAnomes = false);
            }
            sleep(5);
        }
    }
    
    /**
     * zSalva
     * Recebe um array com a chave, data e o xml das NFe destinadas
     * e grava na pasta das recebidas/<anomes>
     * 
     * @param array $aDocs
     * @param boolean $bIncludeAnomes
     */
    protected function zSalva($aDocs = array(), $dir = 'recebidas', $bIncludeAnomes = false)
    {
        if (empty($aDocs)) {
            return;
        }
        $path = $this->pathNFe .
            DIRECTORY_SEPARATOR .
            $this->ambiente .
            DIRECTORY_SEPARATOR .
            $dir;
        $name = '-nfe.xml';
        if ($dir != 'recebidas') {
            $name = '-resNFe.xml';
        }
        foreach ($aDocs as $doc) {
            $anomes = $doc['anomes'];
            $chave =  $doc['chave'];
            $xml = $doc['xml'];
            $pathnfe = $path;
            if ($bIncludeAnomes) {
                $pathnfe = $path.DIRECTORY_SEPARATOR.$anomes;
            }
            $filename = "$chave$name";
            //echo "Salvando $filename \n";
            FilesFolders::saveFile($pathnfe, $filename, $xml);
        }
    }
    
    /**
     * zExtractNFe
     * Recebe o array com os documentos retornados pelo
     * webservice e caso sejam NFe retorna outro array com 
     * a chave, data e o xml
     * 
     * @param array $docs
     * @return array
     */
    protected function zExtractDocs($docs = array(), $bIncludeAnomes = false)
    {
        $aResp = array();
        //para cada documento retornado
        foreach ($docs as $resp) {
            $schema = substr($resp['schema'], 0, 6);
            switch ($schema) {
                case 'resNFe':
                    $aDocs = self::zTrataResNFe($resp);
                    //mostar as notas resumo e manifestar
                    $this->zSalva($aDocs, 'recebidas/resumo', false);
                    break;
                case 'procNF':
                    $aDocs = self::zTrataProcNFe($resp);
                    $this->zSalva($aDocs, 'recebidas', $bIncludeAnomes);
                    break;
                case 'procEv':
                    $aResp = self::zTrataProcEvent($resp);
                    break;
            }
        }
        return $aResp;
    }
    
    /**
     * zTrataResNFe
     * Trata os resumos recebidos de NFe que devem ser manifestadas
     * @param array $resp
     * @return array
     */
    private static function zTrataResNFe($resp = array())
    {
        $aResp = array();
        $content = $resp['doc'];
        $dom = new Dom();
        $dom->loadXMLString($content);
        $xmldata = $dom->saveXML();
        $xmldata = str_replace(
            '<?xml version="1.0"?>',
            '<?xml version="1.0" encoding="utf-8"?>',
            $xmldata
        );
        $anomes = date('Ym', DateTime::convertSefazTimeToTimestamp($dom->getNodeValue('dhEmi')));
        $aResp[] = array(
            'chNFe' => $dom->getNodeValue('chNFe'),
            'cnpj' => $dom->getNodeValue('CNPJ'),
            'cpf' => $dom->getNodeValue('CPF'),
            'xNome' => $dom->getNodeValue('xNome'),
            'tpNF' => $dom->getNodeValue('tpNF'),
            'vNF' => $dom->getNodeValue('vNF'),
            'digval' => $dom->getNodeValue('digVal'),
            'nprot' => $dom->getNodeValue('nProt'),
            'cSitNFe' => $dom->getNodeValue('cSitNFe'),
            'dhEmi' => $dom->getNodeValue('dhEmi'),
            'dhRecbto' => $dom->getNodeValue('dhRecbto'),
            'chave' => $dom->getNodeValue('chNFe'),
            'anomes' => $anomes,
            'xml' => $xmldata
        );
        return $aResp;
    }
    
    /**
     * zTrataProcNFe
     * @param array $resp
     * @return array
     */
    private static function zTrataProcNFe($resp = array())
    {
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
            'chave' => $chave,
            'anomes' => $anomes,
            'xml' => $xmldata
        );
        return $aResp;
    }
    
    /**
     * zTrataProcEvent
     * @param array $resp
     */
    private static function zTrataProcEvent($resp = array())
    {
        $content = $resp['doc'];
        $dom = new Dom();
        $dom->loadXMLString($content);
        $data = $dom->getNodeValue('dhEvento');
        $tsdhevento = DateTime::convertSefazTimeToTimestamp($data);
        $anomes = date('Ym', $tsdhevento);
        $tpEvento = $dom->getNodeValue('tpEvento');
        $chave = $dom->getNodeValue('chNFe');
        if ($tpEvento == '110111') {
            //confirmado cancelamento, localizar o xml da NFe recebida
            //na pasta anomes
            $path = $this->pathNFe .
                DIRECTORY_SEPARATOR .
                $this->ambiente .
                DIRECTORY_SEPARATOR .
                "recebidas".
                DIRECTORY_SEPARATOR .
                $anomes;
            $pathFile = $path . DIRECTORY_SEPARATOR . $chave . '-nfe.xml';
            self::zCancela($pathFile);
        }
        return array();
    }
    
    /**
     * manifesta
     * @param string $chNFe
     * @param string $tpEvento
     */
    public function manifesta($chNFe = '', $tpEvento = '210210')
    {
        $aRetorno = array();
        $xJust = '';
        $this->tools->sefazManifesta(
            $chNFe,
            $this->tpAmb,
            $xJust,
            $tpEvento,
            $aRetorno
        );
        $cStat = $aRetorno['evento'][0]['cStat'];
        if ($cStat == 135 || $cStat == 573 || $cStat == 650) {
            $path = $this->pathRes.DIRECTORY_SEPARATOR.$chNFe.'-resNFe.xml';
            if (is_file($path)) {
                unlink($path);
            }
        }
        return $aRetorno;
    }
    
    /**
     * zCancela
     * Edita a NFe recebida de terceiros indicando o cancelamento
     * @param string $pathFile
     */
    private static function zCancela($pathFile)
    {
        if (is_file($pathFile)) {
            //o arquivo foi localizado, então indicar o cancelamento
            //editando o xml da NFe e substituindo o cStat do protocolo por
            //135 ou 101
            $xml = FilesFolders::readFile($pathFile);
            $nfe = new \DOMDocument();
            $nfe->loadXML($xml);
            $infProt = $nfe->getElementsByTagName('infProt')->item(0);
            $infProt->getElementsByTagName('cStat')->item(0)->nodeValue = '101';
            $nfe->save($pathFile);
        }
    }
}
