<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
include_once '../bootstrap.php';

use App\Status;
use App\Dates;
use App\Dados;
use Common\Files\FilesFolders;

//carrega os dados de configuração
$configJson = FilesFolders::readFile('../config/config.json');
$objConfig = json_decode($configJson);
//estabelece o ambiente
$ambiente = 'homologacao';
if ($objConfig->tpAmb == '1') {
    $ambiente = 'producao';
}
//verifica o status
$htmlStatus = Status::verifica($configJson);
//verifica certificado
$htmlCert = Status::getExpirDate();
//cria uma lista vazia
$aList = array();
//carrega as entradas em post ou get
$pasta = isset($_REQUEST['pasta']) ? $_REQUEST['pasta'] : '';
$ano = isset($_REQUEST['ano']) ? $_REQUEST['ano'] : '';
$mes = isset($_REQUEST['mes']) ? $_REQUEST['mes'] : '';

//$pasta = 'APROVADAS';
//$ano = '2015';
//$mes = '02';

//cria lista de anos desde 2008 que foi o incio dos dados
$anoAtual = date('Y');
$selAnos = "<select size=\"1\" name=\"ano\" id=\"ano\">\n";
for ($xCon = 2008; $xCon <= $anoAtual; $xCon++) {
    $sel = '';
    if ($xCon == $ano) {
        $sel = 'SELECTED ';
    }
    $selAnos .= "<option ".$sel."value=\"$xCon\">$xCon</option>\n";
}
$selAnos .= "</select>\n";
//cria lista de meses
$selMeses = "<select size=\"1\" name=\"mes\" id=\"mes\">\n";
for ($xCon = 1; $xCon <= 12; $xCon++) {
    $sel = '';
    $txtMes = str_pad($xCon, 2, "0", STR_PAD_LEFT);
    if ($xCon == (int) $mes) {
        $sel = 'SELECTED ';
    }
    $selMeses .= "<option ".$sel."value=\"$txtMes\">$txtMes</option>\n";
}
$selMeses .= "</select>\n";
$chkAprovadas = '';
$chkEnviadas = '';
$chkRecebidas = '';
$aDados = array();
//caso tenha sido passados os dados
$dias = Dates::diasUteis(date('m'), date('Y'));
$hoje = date('d/m/Y');
$titulo = "Notas Fiscais";
$htmlMsgPasta = "<i>[hoje $hoje com $dias dias úteis no mês.]</i>";
if (!empty($pasta) && !empty($ano) && !empty($mes)) {
    if ($pasta == 'APROVADAS') {
        $caminho = 'enviadas'.DIRECTORY_SEPARATOR.'aprovadas'.DIRECTORY_SEPARATOR.$ano.$mes;
        $chkAprovadas = 'SELECTED ';
    } elseif ($pasta == 'ENVIADAS') {
        $caminho = 'enviadas'.DIRECTORY_SEPARATOR;
        $chkEnviadas = 'SELECTED ';
    } else {
        $caminho = 'recebidas'.DIRECTORY_SEPARATOR.$ano.$mes;
        $chkRecebidas = 'SELECTED ';
    }
    $titulo .= " $mes/$ano";
    $path = $objConfig->pathNFeFiles.DIRECTORY_SEPARATOR.$ambiente.DIRECTORY_SEPARATOR.$caminho;
    $aList = FilesFolders::listDir($path, '*-nfe.xml', true);
    $aDados = Dados::extrai($aList, $objConfig->cnpj);
    $numNF = count($aList);
    $numCanc = Dados::$nCanc;
    $dias = Dates::diasUteis($mes, $ano);
    $media = round($numNF/$dias, 0);
    $htmlMsgPasta = "<i>Total de $numNF notas no mês $mes. "
            . "{ $numCanc notas canceladas} [ $media NFe/dia (até hoje $hoje) "
            . "e $dias dias úteis no mês.]</i>";
    
    
}
$selPasta = "<select size=\"1\" name=\"pasta\" id=\"pasta\">
    <option ".$chkEnviadas."value=\"ENVIADAS\">ENVIADAS</option>
    <option ".$chkAprovadas."value=\"APROVADAS\">APROVADAS</option>
    <option ".$chkRecebidas."value=\"RECEBIDAS\">RECEBIDAS</option>
</select>";

$htmlNotas = "";
$i = 0;
foreach ($aDados as $dado) {
    $pathDanfe = "./print.php?xml=".$aList[$i];
    $htmlLinhaNota = "<tr class=\"dados\">\n";
    if ($dado['cStat'] != '100') {
        $htmlLinhaNota = "<tr class=\"cancel\">\n";
    }
    $htmlLinhaNota .= "<td class=\"center\"><a href=\"$pathDanfe\" target=\"_blank\">".$dado['nNF']."</a></td>
        <td class=\"center\">".$dado['serie']."</td>
        <td class=\"center\">".$dado['data']."</td>
        <td class=\"left\">".$dado['nome']."</td>
        <td class=\"right\">".$dado['vNF']."</td>
        <td class=\"left\">".$dado['natureza']."</td>
        </tr>\n";
    $htmlNotas .= $htmlLinhaNota;
    $i++;
}

$html = "<!DOCTYPE html>
<html>
    <head>
        <title>Notas Fiscais</title>
        <meta charset=\"UTF-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <link rel=\"stylesheet\" type=\"text/css\" href=\"css/teste.css\">
    </head>
    <body>
        <div class=\"container\">
            <div class=\"left\"><img src=\"images/logo.jpg\" alt=\"logo\" height=\"62\"></div>
            <div class=\"left\">
                <h1 align=\"center\">$titulo</h1>
                $htmlStatus
                $htmlCert
            </div>
            <div class=\"right\">
                <form method=\"POST\" name=\"myform\" action=\"\">
                    <label>Origem</label><br>$selPasta<br>
                    <table><tr><td>
                    <label>Ano</label><br>$selAnos
                    </td>
                    <td>
                    <label>Mês</label><br>$selMeses
                    </td>
                    </tr>
                    </table><br>
                    <input type=\"submit\" value=\"Procurar Notas\" name=\"B1\">
                </form>
            </div>
        </div>
        <div class=\"container\">
            <table width=\"95%\">
                <tr>
                    <td>$htmlMsgPasta</td>
                </tr>
            </table>    
            <table width=\"95%\">
                <tr>
                    <th class=\"dados\">Número</th>
                    <th class=\"dados\">Série</th>
                    <th class=\"dados\">Data</th>
                    <th class=\"dados\">Destinatário/Emitente</th>
                    <th class=\"dados\">Valor</th>
                    <th class=\"dados\">Natureza da Operação</th>
                </tr>
                $htmlNotas
            </table>
        </div>
        <div class=\"container\">
            <center>
            <table border=\"0\" cellspacing=\"1\" width=\"40%\">
                <tr>
                    <td class=\"right\">Total Faturado</td>
                    <td class=\"right\">R$ 222222</td>
                </tr>
                <tr>	
                    <td class=\"right\">Total ICMS</td>
                    <td class=\"right\">R$ 222222</td>
                </tr>
                <tr>
                    <td class=\"right\">Peso Liquido Total Faturado</td>
                    <td class=\"right\">222222 kg</td>
                </tr>
                <tr>
                    <td class=\"right\">Fat. Médio Diário</td>
                    <td class=\"right\">R$ 222222</td>
                </tr>
                <tr>
                    <td class=\"right\"><i>Fat. Projetado</i></td>
                    <td class=\"right\"><i>R$ 22222</i></td>
                </tr>
            </table>
            </center>
        </div>    
    </body>
</html>
";

echo $html;
