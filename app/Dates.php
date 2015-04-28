<?php

namespace App;

class Dates
{
    //dias uteis
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
