<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
include_once '../bootstrap.php';

/**
 * Rotina de impressão da DANFE
 *
 * Esta rotina recebe como parâmetro o caminho para xml da NFe compactado e codificado em Base64
 *
 * @category   Application
 * @package    robmachado\teste
 * @copyright  Copyright (c) 2008-2015
 * @license    http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author     Roberto L. Machado <linux.rlm at gmail dot com>
 * @link       http://github.com/robmachado/teste for the canonical source repository
 */

use NFePHP\Extras\Danfe;
use NFePHP\Common\Files\FilesFolders;

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
$danfe = new Danfe($docxml, 'P', 'A4', $logo, 'I', '');
$id = $danfe->montaDANFE();
$teste = $danfe->printDANFE($id.'.pdf', 'I');

