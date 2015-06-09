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

use NFePHP\Common\Dom\Dom;
use NFePHP\Common\DateTime\DateTime;
use NFePHP\Common\Exception\RuntimeException;

class Dados
{
    public static $nCanc = 0;
    
    public static function extraiResumo($aList)
    {
        $aResp = array();
        foreach ($aList as $file) {
            $dom = null;
            try {
                //podem ser xml com
                //resumo *-resNFe.xml
                //cancelamento *-cancNFe.xml
                //carta de correção *-cce.xml
                $pos = explode('-', $file);
                $dom = new Dom();
                $dom->loadXMLFile($file);
                switch ($pos[1]) {
                    case '-resNFe.xml':
                        $dataemi = date('d/m/Y', DateTime::convertSefazTimeToTimestamp($dom->getNodeValue('dhEmi')));
                        $aResp[] = array(
                            'tipo' => 'NFe',
                            'chNFe' => $dom->getNodeValue('chNFe'),
                            'cnpj' => $dom->getNodeValue('CNPJ'),
                            'cpf' => $dom->getNodeValue('CPF'),
                            'xNome' => $dom->getNodeValue('xNome'),
                            'tpNF' => $dom->getNodeValue('tpNF'),
                            'vNF' => $dom->getNodeValue('vNF'),
                            'digval' => $dom->getNodeValue('digVal'),
                            'nprot' => $dom->getNodeValue('nProt'),
                            'cSitNFe' => $dom->getNodeValue('cSitNFe'),
                            'dhEmi' => $dataemi,
                            'dhRecbto' => $dom->getNodeValue('dhRecbto')
                        );
                        break;
                    case '-cancNFe.xml':
                        $aResp[] = array(
                            'tipo' => 'Cancelamento',
                            'chNFe' => $file,
                            'cnpj' => '',
                            'cpf' => '',
                            'xNome' => 'Cancelamento',
                            'tpNF' => '',
                            'vNF' => '',
                            'digval' => '',
                            'nprot' => '',
                            'cSitNFe' => '',
                            'dhEmi' => '',
                            'dhRecbto' => ''
                        );
                        break;
                    case '-cce.xml':
                        $aResp[] = array(
                            'tipo' => 'CCe',
                            'chNFe' => $file,
                            'cnpj' => '',
                            'cpf' => '',
                            'xNome' => 'CCe',
                            'tpNF' => '',
                            'vNF' => '',
                            'digval' => '',
                            'nprot' => '',
                            'cSitNFe' => '',
                            'dhEmi' => '',
                            'dhRecbto' => ''
                        );
                        break;
                }
            } catch (RuntimeException $e) {
                $aResp[] = array(
                    'chNFe' => '',
                    'cnpj' => '',
                    'cpf' => '',
                    'xNome' => $file,
                    'tpNF' => '',
                    'vNF' => '',
                    'digval' => '',
                    'nprot' => '',
                    'cSitNFe' => '3',
                    'dhEmi' => '',
                    'dhRecbto' => ''
                );
                
            }
        }
        return $aResp;
    }
    
    public static function extrai($aList, $cnpj = '')
    {
        $aResp = array();
        $totFat = 0;
        $totPeso = 0;
        $totIcms = 0;
        foreach ($aList as $file) {
            $dom = null;
            $ide = null;
            $emit = null;
            $dest = null;
            try {
                $dom = new Dom();
                $dom->loadXMLFile($file);
                $ide = $dom->getNode('ide');
                $emit = $dom->getNode('emit');
                $dest = $dom->getNode('dest');
                $icmsTot = $dom->getNode('ICMSTot');
                $vol = $dom->getNode('vol');
                $cStat = $dom->getNodeValue('cStat');
                if ($cStat != '100') {
                    self::$nCanc++;
                }
                $dhEmi = $dom->getValue($ide, 'dhEmi');
                if (empty($dhEmi)) {
                    $dhEmi = $dom->getValue($ide, 'dEmi');
                }
                //echo $file.'___'.$dhEmi.'<br>';
                $tsEmi = DateTime::convertSefazTimeToTimestamp($dhEmi);
                $data = '';
                if (is_numeric($tsEmi)) {
                    $data = date('d/m/Y', $tsEmi);
                }
                $emitCNPJ = $dom->getValue($emit, 'CNPJ');
                $emitRazao = $dom->getValue($emit, 'xNome');
                $destRazao = $dom->getValue($dest, 'xNome');
                $vNF = $dom->getValue($icmsTot, 'vNF');
                $vNFtext = $vNF;
                if (is_numeric($vNF)) {
                    $vNFtext = 'R$ '.number_format($vNF, '2', ',', '.');
                }
                $serie = $dom->getNodeValue('serie');
                $nProt = $dom->getNodeValue('nProt');
                $nome = $emitRazao;
                if ($emitCNPJ == $cnpj) {
                    $nome = $destRazao;
                }
                $email = $dom->getValue($dest, 'email');
                $aObscont = $dom->getElementsByTagName('obsCont');
                if (count($aObscont) > 0) {
                    foreach ($aObscont as $obsCont) {
                        $xCampo = $obsCont->getAttribute('xCampo');
                        if ($xCampo == 'email') {
                            $email .= ";" . $dom->getValue($obsCont, 'xTexto');
                        }
                    }
                }
                if (substr($email, 0, 1) == ';') {
                    $email = substr($email, 1, strlen($email)-1);
                }
                $vICMS = $dom->getValue($icmsTot, 'vICMS');
                $totIcms += $vICMS;
                $valorFat = 0;
                if ($vICMS != 0 && $cStat == '100') {
                    $valorFat = $vNF;
                }
                $totFat += $valorFat;
                $pesoL = $dom->getValue($vol, 'pesoL');
                if ($pesoL != '') {
                    $totPeso += $pesoL;
                }
                $aResp[] = array(
                    'nNF' => $dom->getValue($ide, 'nNF'),
                    'serie' => $serie,
                    'data' =>  $data,
                    'nome' => $nome,
                    'natureza' => $dom->getValue($ide, 'natOp'),
                    'cStat' => $cStat,
                    'vNF' => $vNFtext,
                    'nProt' => $nProt,
                    'email' => $email
                );
            } catch (RuntimeException $e) {
                $aResp[] = array(
                    'nNF' => '000000',
                    'serie' => '000',
                    'data' =>  '000',
                    'nome' => 'FALHA',
                    'natureza' => "$file",
                    'cStat' => '',
                    'vNF' => 0,
                    'nProt' => '',
                    'email' => ''
                );
            }
        }
        return array(
            'totFat' => $totFat,
            'totPeso' => $totPeso,
            'totIcms' => $totIcms,
            'aNF' => $aResp
        );
    }
}
