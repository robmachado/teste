<?php

namespace App;

/**
 * Classe para buscar os documentos destinados
 * 
 * @category   Application
 * @package    robmachado\teste
 * @copyright  Copyright (c) 2008-2015
 * @license    http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author     Roberto L. Machado <linux.rlm at gmail dot com>
 * @link       http://github.com/robmachado/teste for the canonical source repository
 */
    
class Dates
{
    
    /**
     * diasUteis
     * Obtêm o numero de dias úteis no mês
     * não são considerados os feriados
     * @param int $mes
     * @param int $ano
     * @return int
     */
    public static function diasUteis($mes = 1, $ano = 2015)
    {
        // Primeiro dia do mês
        $firstday = date("M-d-Y", mktime(0, 0, 0, $mes, 1, $ano));
        // Último dia do mês
        $lastday = date("M-d-Y", mktime(0, 0, 0, $mes + 1, 0, $ano));
        $count = 0;
        $workday = 0;
        while (($lastday > $firstday) && ($count <= 32)) {
            //$nowday = date("M-d-Y", mktime(0, 0, 0, $mes, $count + 1, $ano));
            $semday = date("w", mktime(0, 0, 0, $mes, $count + 1, $ano));
            if ($semday > 0 && $semday < 6) {
                $workday ++;
            }
            $count ++;
        }
        return $workday;
    }
    
    /**
     * diasUteisNow
     * Obtêm o numero de dias úteis passados no mês até a data atual
     * não são considerados os feriados
     * @param int $mes
     * @param int $ano
     * @return int
     */
    public static function diasUteisNow($mes = 1, $ano = 2015)
    {
        // Primeiro dia do mês
        $firstday = strtotime(date("Y-m-d", mktime(0, 0, 0, $mes, 1, $ano)));
        // dia de hoje
        $lastday = strtotime(date("Y-m-d"));
        $count = 0;
        $workday = 0;
        while (($lastday > $firstday) && ($count <= 32)) {
            $firstday = strtotime(date("Y-m-d", mktime(0, 0, 0, $mes, $count + 1, $ano)));
            $semday = date("w", mktime(0, 0, 0, $mes, $count + 1, $ano));
            if ($semday > 0 && $semday < 6) {
                $workday ++;
            }
            $count ++;
        }
        return $workday;
    }
}
