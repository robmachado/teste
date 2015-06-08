<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

//error_reporting(0);
//ini_set('display_errors', 'Off');


include_once '../bootstrap.php';

/**
 * Rotina para apresentar uma lista das NFe a serem manifestadas
 * gravadas na pasta indicada recebidas/resumos
 * Após serem manifestadas essas notas não aparecerão mais
 * 
 * @category   Application
 * @package    robmachado\teste
 * @copyright  Copyright (c) 2008-2015
 * @license    http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author     Roberto L. Machado <linux.rlm at gmail dot com>
 * @link       http://github.com/robmachado/teste for the canonical source repository
 */

use App\Dados;
use NFePHP\Common\Files\FilesFolders;
use NFePHP\Common\Exception\InvalidArgumentException;

//carrega os dados de configuração
$configJson = FilesFolders::readFile('../config/config.json');
$objConfig = json_decode($configJson);
//estabelece o ambiente
$ambiente = 'homologacao';
if ($objConfig->tpAmb == '1') {
    $ambiente = 'producao';
}
//cria uma lista vazia
$aList = array();
$caminho = 'recebidas'.DIRECTORY_SEPARATOR.'resumo';
$path = $objConfig->pathNFeFiles.DIRECTORY_SEPARATOR.$ambiente.DIRECTORY_SEPARATOR.$caminho;
try {
    $aList = FilesFolders::listDir($path, '*.xml', true);
} catch (InvalidArgumentException $exc) {
    $mensagem = $exc->getMessage();
}

$aDados = Dados::extraiResumo($aList);
$lista = '<form>';
$lista .= '<table width="75%"><thead><tr><th></th><th class=\"border\" data-sort=\"int\">NFe Número</th><th class=\"border\" data-sort=\"string\">Emitente</th><th class=\"border\" data-sort=\"string\">Data</th><th>Valor</th></tr></thead><tbody>';
$iCount = 0;
foreach ($aDados as $res) {
    $chkChave = "chk";
    $lista .= '<tr class=\"dados\">'
            . '<td><input type="checkbox" name="'.$chkChave.'" id="'.$chkChave.'" value="'.$res['chNFe'].'" ></td>'
            . '<td class="center">'.substr($res['chNFe'], 25, 9).'</td>'
            . '<td class="left">'.$res['xNome'].'</td>'
            . '<td class="center">'.$res['dhEmi'].'</td>'
            . '<td class="right">R$ '.number_format($res['vNF'], 2, ',', '.').'</td>'
            . '</tr>';
}
$lista .= '<tr><td colspan="4"><input type="button" value="Manifestar" onClick="manifestar();"></td></tr>';
$lista .= '</tbody></table></form>';

$html = "<!DOCTYPE html>
<html>
    <head>
        <title>Resumos das Notas Fiscais</title>
        <meta charset=\"UTF-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <script src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js\"></script>
        <script src=\"resources/stupidtable.js?dev\"></script>
        <link rel=\"stylesheet\" type=\"text/css\" href=\"css/teste.css\">
        <script>
        $(function() {
            //Helper function para valores formatados em R$
            var valor_from_string = function(str) {
                var newstr = str.replace(/(\\t*) */g, '');
                newstr = newstr.replace(/[A-Z]/g, '');
                newstr = newstr.replace(/[$]/g, '');
                newstr = newstr.replace(/\./g, '');
                newstr = newstr.replace(/[,]/g, '.');
                var valor = parseInt(newstr)*100;
                return valor;
            }
            var table = $(\"table\").stupidtable({
                \"valor\": function(a,b) {
                    // Get these into int objects for comparison.
                    aVal = valor_from_string(a);
                    bVal = valor_from_string(b);
                    return aVal - bVal;
                }
            });
            table.on(\"beforetablesort\", function (event, data) {
                // Apply a \"disabled\" look to the table while sorting.
                // Using addClass for testing as it takes slightly longer to render.
                $(\"#msg\").text(\"Organizando a tabela ...\");
                $(\"table\").addClass(\"disabled\");
            });
            table.on(\"aftertablesort\", function (event, data) {
                // Reset loading message.
                $(\"#msg\").html(\"&nbsp;\");
                $(\"table\").removeClass(\"disabled\");
                var th = $(this).find(\"th\");
                th.find(\".arrow\").remove();
                var dir = $.fn.stupidtable.dir;
                var arrow = data.direction === dir.ASC ? \"&uarr;\" : \"&darr;\";
                th.eq(data.column).append('<span class=\"arrow\">' + arrow +'</span>');
            });
        });    
        </script>
        <script>
        function OpenWindowWithPost(url, windowoption, name, params) {
            var form = document.createElement(\"form\");
            form.setAttribute(\"method\", \"post\");
            form.setAttribute(\"action\", url);
            form.setAttribute(\"target\", name);
             for (var i in params) {
                if (params.hasOwnProperty(i)) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = i;
                    input.value = params[i];
                    form.appendChild(input);
                }
            }
            document.body.appendChild(form);
            window.open(\"\", name, windowoption);
            form.submit();
            document.body.removeChild(form);
        }
        function manifestar() {
            var url = 'manifestarmarcadas.php';
            var name = 'page';
            var lista = [];
            var x = 0;
            var specs = 'scrollbars=no,menubar=no,height=260,width=650,resizable=yes,toolbar=no,status=no';
            var checkboxes = document.getElementsByName('chk');
            for (var i= 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    lista[x] = checkboxes[i].value;
                    x++;
                }
            }
            var param = {'lista' : lista};
            OpenWindowWithPost(url, specs, name, param);		
        }
        </script>
    </head>
    <body>
    <div class=\"container\">
        <center>
        <h2>Resumos de Notas</h2>
        <h3>Estas notas foram emitidas contra nós e podem ser maifestadas para permitir seu download</h3>
        <h3>Selecione e manifeste a ciencia dessa operação.</h3>
        $lista
        </center>    
    </div>
    </body>
</html>";

echo $html;
