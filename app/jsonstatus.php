<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

include_once '../bootstrap.php';

use App\Status;

//carrega os dados de configuração
$configJson = FilesFolders::readFile('../config/config.json');
$objConfig = json_decode($configJson);

$htmlStatus = Status::verifica($objConfig);

//TODO: usar o jquery para atualizar os dados
