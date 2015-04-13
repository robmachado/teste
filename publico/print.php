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
//echo $xml;
//exit();
//$xml = "H4sIAAAAAAAAAyWMSwqAMAwFT6RJGpPW4wQ/IGgriq3Ht+rbzDCLB9kOKKVAnCfYjzRegyWYYl5stBOspvyZQxJkYHkhwZOKY0Qk6kXwH3ehZ9JOnFP02tTP9t7WB6ZcRltkAAAA";
$dxml = base64_decode($xml);
$xml = gzdecode($dxml);

$docxml = FilesFolders::readFile($xml);
$danfe = new DanfeNFePHP($docxml, 'P', 'A4', 'images/logo.jpg', 'I', '');
$id = $danfe->montaDANFE();
$teste = $danfe->printDANFE('/var/www/teste/base/'.$id.'.pdf', 'F');
