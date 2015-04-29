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
        //caso a versão do PHP não possa identificar automaticamente
        //o protocolo a ser usado durante o handshake. Defina o protocolo.
        $this->tools->setSSLProtocol('SSLv3');
        
        $this->ambiente = $this->tools->ambiente;
        $this->pathNFe = $this->tools->aConfig['pathNFeFiles'];
        $this->tpAmb = $this->tools->aConfig['tpAmb'];
        $this->nsuFilePath = PATH_ROOT.'base'.DIRECTORY_SEPARATOR;
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
        $nsuJson = json_decode(FilesFolders::readFile($this->nsuFilePath . $this->nsuFileName));
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
                $aDocs = $this->zExtractNFe($aResposta['aDoc']);
                $this->zSalva($aDocs, $bIncludeAnomes);
            }
            sleep(3);
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
            //echo "Salvando $filename \n";
            FilesFolders::saveFile($path, $filename, $xml);
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
