<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
include_once '../bootstrap.php';

use Extras\DanfeNFePHP;
use Common\Files\FilesFolders;

$xml = isset($_REQUEST['xml']) ? $_REQUEST['xml'] : '';
if ($xml == '') {
    exit();
}
$dxml = base64_decode($xml);
$xml = gzdecode($dxml);
$logo = 'images/logo.jpg';
if (strpos($xml, 'recebidas')) {
    $logo = '';
}
$docxml = FilesFolders::readFile($xml);
$danfe = new DanfeNFePHP($docxml, 'P', 'A4', $logo, 'I', '');
$id = $danfe->montaDANFE();
$teste = $danfe->printDANFE($id.'.pdf', 'I');
