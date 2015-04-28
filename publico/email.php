<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$chave = isset($_POST['chave']) ? $_POST['chave'] : '';
$xml = isset($_POST['xml']) ? $_POST['xml'] : '';
$address = isset($_POST['address']) ? $_POST['address'] : '';

//$dxml = base64_decode($xml);
//$xml = gzdecode($dxml);
$daddress = base64_decode($address);
$address = gzdecode($daddress);

$html = "<!DOCTYPE html>
<html>
    <head>
        <title>Envio de emails de Notas Fiscais</title>
        <meta charset=\"UTF-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <link rel=\"stylesheet\" type=\"text/css\" href=\"css/teste.css\">
    </head>
    <body>
    <div class=\"container\">
        <form action='envia.php' method='post' >
            <input type='hidden' name='xml' id='xml' value='$xml' />
            <input type='hidden' name='chave' id='chave' value='$chave' />    
            <label>NFe</label><br>
            <input type='text' name='nfe' id='nfe' value='$chave' size='10' DISABLED /><br>
            <label>Para (destinatários)</label><br>            
            <input type='text' name='para' id='para' value='$address' size='75'/><br>
            <input type=\"checkbox\" name=\"comPdf\" value=\"1\">Enviar DANFE em anexo<br>
            <input type='submit' name='envia' id='envia' value='Enviar'>
        </form>
    </div>
    </body>
</html>";

echo $html;
