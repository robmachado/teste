<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
include_once '../bootstrap.php';

use Extras\DanfeNFePHP;
use Common\Files\FilesFolders;

$xml = isset($_GET['xml']) ? $_GET['xml'] : '';

if ($xml == '') {
    exit();
}
$docxml = FilesFolders::readFile($xml);
$danfe = new DanfeNFePHP($docxml, 'P', 'A4', 'images/logo.jpg', 'I', '');
$id = $danfe->montaDANFE();
$teste = $danfe->printDANFE($id.'.pdf', 'I');
