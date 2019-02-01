<?php

namespace magicsoft\base;

use yii\helpers\ArrayHelper;

class MagicCrypto
{
    public static function encrypt($string)
    {
        return \Yii::$app->security->maskToken($string);
    }

    public static function decrypt($string)
    {
        return \Yii::$app->security->unmaskToken($string);
    }
}