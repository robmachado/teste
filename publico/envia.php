<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
include_once '../bootstrap.php';

use App\Mail;
use NFePHP\Extras\DanfeNFePHP;
use NFePHP\Common\Files\FilesFolders;

$chave = isset($_POST['chave']) ? $_POST['chave'] : '';
$xml = isset($_POST['xml']) ? $_POST['xml'] : '';
$para = isset($_POST['para']) ? $_POST['para'] : '';
$comPdf = isset($_POST['comPdf']) ? $_POST['comPdf'] : '0';

$dxml = base64_decode($xml);
$xml = gzdecode($dxml);

$pathPdf = '';
$bPdf = false;

if ($comPdf === '1') {
    $bPdf = true;
    $logo = 'images/logo.jpg';
    if (strpos($xml, 'recebidas')) {
        $logo = '';
    }
    $docxml = FilesFolders::readFile($xml);
    $danfe = new DanfeNFePHP($docxml, 'P', 'A4', $logo, 'I', '');
    $id = $danfe->montaDANFE();
    $pathPdf = '../base/'.$id.'.pdf';
    $pdf = $danfe->printDANFE($pathPdf, 'F');
}

$mail = new Mail();
$resp = $mail->envia($xml, $para, $bPdf, $pathPdf);
if ($resp === true) {
    echo "SUCESSO NFe n. $chave, enviada para $para.";
} else {
    echo "FRACASSO!! houve algum problema. $mail->error";
}

if ($comPdf && is_file($pathPdf)) {
    unlink($pathPdf);
}
