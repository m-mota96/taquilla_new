<?php

namespace App\Traits;

trait DateFormatEs {
    public static function nameDay($fecha, $abreviado = false) { // Esta función de usa dentro de los controller para saber el nombre del día de la semana
        $dias = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
        $diasCortos = ['Dom','Lun','Mar','Mie','Jue','Vie','Sab'];
        $dia = ($abreviado == false) ? $dias[date('w', strtotime($fecha))] : $diasCortos[date('w', strtotime($fecha))];
        return $dia;
    }

    public static function nameMonth($fecha, $abreviado = false) { // Esta función de usa dentro de los controller para saber el nombre del mes
        $mes = substr($fecha, 5, 2);
        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $mesesCortos = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $mes = ($abreviado == false) ? $meses[intval($mes) - 1] : $mesesCortos[intval($mes) - 1];
        return $mes;
    }
}