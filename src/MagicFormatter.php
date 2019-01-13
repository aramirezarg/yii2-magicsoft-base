<?php
/**
 * Created by PhpStorm.
 * User: Alfredo Ramirez
 * Date: 23/4/2018
 * Time: 18:23
 */

namespace magicsoft\base;

use Yii;

class MagicFormatter
{
    public static function asCurrency($value){
        return Yii::$app->formatter->asCurrency(doubleval($value ? $value : 0));
    }

    public static function asDatetime($dateTime){
        return Yii::$app->formatter->asDatetime($dateTime);
    }

    public static function asDate($date){
        return Yii::$app->formatter->asDate($date);
    }

    public static function Uppercase($text){
        $variable = strtr(strtoupper($text),"àèìòùáéíóúçñäëïöü","ÀÈÌÒÙÁÉÍÓÚÇÑÄËÏÖÜ");
        return $variable;
    }

    public static function toString($value){
        return MagicFormatter::UpperCase(CifrasEnLetras::convertirMonedaEnLetras(floatval($value)));
    }
}