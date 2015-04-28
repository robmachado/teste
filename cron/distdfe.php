<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
if (!defined('PATH_ROOT')) {
    define('PATH_ROOT', dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR);
}

include_once '../bootstrap.php';

use App\DFe;

$dfe = new DFe();
$dfe->getNFe(2);
