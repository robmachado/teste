<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

//error_reporting(0);
//ini_set('display_errors', 'Off');

include_once '../bootstrap.php';

use App\DFe;
use NFePHP\Common\Files\FilesFolders;

/**
 * Rotina para manifestar os resumos selecionados
 * 
 * @category   Application
 * @package    robmachado\teste
 * @copyright  Copyright (c) 2008-2015
 * @license    http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author     Roberto L. Machado <linux.rlm at gmail dot com>
 * @link       http://github.com/robmachado/teste for the canonical source repository
 */

$lista = isset($_REQUEST['lista']) ? $_REQUEST['lista'] : array();
$aLista = explode(',', $lista);
$configJson = FilesFolders::readFile('../config/config.json');
$objConfig = json_decode($configJson);
//estabelece o ambiente
$ambiente = 'homologacao';
if ($objConfig->tpAmb == '1') {
    $ambiente = 'producao';
}
$caminho = 'recebidas'.DIRECTORY_SEPARATOR.'resumo';
$path = $objConfig->pathNFeFiles.DIRECTORY_SEPARATOR.$ambiente.DIRECTORY_SEPARATOR.$caminho;
$dfe = new DFe('../config/config.json');
$aInv = array_flip($aLista);
foreach ($aLista as $res) {
    $aResp = $dfe->manifesta($res);
    $cStat = $aResp['evento'][0]['cStat'];
    $aInv[$res] = "Falha evento não vinculado - $cStat";
    if ($cStat == 135 || $cStat == 573) {
        $aInv[$res] = "Processado  - $cStat";
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Manifestar</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <link rel="stylesheet" type="text/css" href="css/teste.css">
    </head>
    <body>
    <div class="container">
        <h2>manifestar</h2>
        <?php
        $iCount = 0;
        foreach ($aInv as $key => $res) {
            echo $key . ' --> ' . $res . '<br>';
        }
        ?>
    </div>
    </body>
</html>