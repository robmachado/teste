<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

include_once '../bootstrap.php';

use NFe\ToolsNFe;
use App\Dates;

$siglaUF = 'SP';
$tpAmb = '1';
$aRetorno = array();

$nfe = new ToolsNFe('../config/config.json');
$resp = $nfe->sefazStatus('', '', $aRetorno);

echo json_encode($resp);
