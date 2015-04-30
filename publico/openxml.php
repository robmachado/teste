<?php

/* 
 * abre o xml e expoe na tela
 */

$xml = isset($_REQUEST['xml']) ? $_REQUEST['xml'] : '';
if ($xml == '') {
    exit();
}
$dxml = base64_decode($xml);
$filename = gzdecode($dxml);
$xml = file_get_contents($filename);

header("Content-Type:text/xml");
echo $xml;
