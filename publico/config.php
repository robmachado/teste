<?php
require_once('../bootstrap.php');

$filename = "/dados/projetos/robmachado/teste/vendor/nfephp-org/nfephp/install/";
if (is_file($filename)) {}
    $conf = file_get_contents($filename);
    echo $conf;
}


