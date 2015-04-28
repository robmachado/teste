<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
include_once '../bootstrap.php';

use App\DFe;

$dfe = new DFe();


$dfe->getNFe(2);

/*
?>
<!DOCTYPE html>
<html lang="pt_BR">
  <head>
    <meta charset="utf-8">
    <title>Busca NFe destinadas</title>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <style>
        .ui-progressbar {
            position: relative;
        }
        .progress-label {
            position: absolute;
            left: 50%;
            top: 4px;
            font-weight: bold;
            text-shadow: 1px 1px 0 #fff;
        }
    </style>
  </head>
  <body>
      <script>
          
        $(function() {
            
            var progressbar = $( "#progressbar" ),
            progressLabel = $( ".progress-label" );
            progressbar.progressbar({
                value: false,
                change: function() {
                    progressLabel.text( progressbar.progressbar( "value" ) + "%" );
                },
                complete: function() {
                    progressLabel.text( "Completo!" );
                }
            });
            
            function progress() {
                var val = progressbar.progressbar( "value" ) || 0;
                var oldVal = val;
                var i = 0;
                val = getVal(val);
                val = $("#valor").val();
                if (oldVal == val) {
                    i++;
                }
                if (i > 10) {
                    val = 99;
                }
                progressbar.progressbar( "value", val );
                if ( val < 99 ) {
                    setTimeout( progress, 2000 );
                }
            }
            
            function getVal(val) {
                var valorP = $.getJSON('../base/nsu.json')
                    .done(function( data ) {
                        var ultNSU = data['ultNSU'];
                        var maxNSU = data['maxNSU'];
                        var valor = 0;
                        if (maxNSU > 0) {
                           valor = ultNSU/maxNSU*100;
                        }
                        valor = valor.toFixed(0);
                        $("#valor").val(valor);
                });
                return val + 30;
            }
            $( "#target").click(function() {
               $( "div" ).remove( ".click" ); 
               setTimeout( progress, 2000 );
            });
        });
        
        </script>
      <div class="container">
        <h1>Buscando NFe destinadas</h1>
        <form id="prog" name="prog">
            <input type="text" id="valor" name="valor" value="0">
        <div class="click" id="target">Iniciar</div>
        <div id="progressbar"><div class="progress-label">Aguardando...</div></div>
        </form>
      </div>    
  </body>
</html>
 * 
 */