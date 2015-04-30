<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
include_once '../bootstrap.php';

if (!defined('PATH_ROOT')) {
    define('PATH_ROOT', dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR);
}

/**
 * Rotina de busca das NFe destinadas, para ser usada via CRON com php-cli em modo console
 * 
 * @category   Application
 * @package    robmachado\teste
 * @copyright  Copyright (c) 2008-2015
 * @license    http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author     Roberto L. Machado <linux.rlm at gmail dot com>
 * @link       http://github.com/robmachado/teste for the canonical source repository
 * 
 */

use App\DFe;

$dfe = new DFe();

//50 é numero máximo de interações em uma única pesquisa
//true indica que desejo salvar os dados na pasta recebidas/<anomes>
//se false indica que desejo salvar os dados na pasta recebidas/
$dfe->getNFe(50, true);
