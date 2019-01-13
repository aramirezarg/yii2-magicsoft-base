<?php

namespace magicsoft\base;

use yii\helpers\ArrayHelper;

class MagicCrypto
{
    CONST ENCRYPT_METHOD = "AES-256-CBC";

    CONST SECRET_KEY = '205bdf05272043d';
    CONST SECRET_IV = '205bdf05272043d';

    public static function encrypt($string)
    {
        $key = hash('sha256', self::getSecretKey());
        $iv = substr(hash('sha256', self::getSecretIv()), 0, 16);
        $output = openssl_encrypt($string, self::ENCRYPT_METHOD, $key, 0, $iv);

        return base64_encode($output);
    }

    public static function decrypt($string)
    {
        $key = hash('sha256', self::getSecretKey());
        $iv = substr(hash('sha256', self::getSecretIv()), 0, 16);

        return openssl_decrypt(base64_decode($string), self::ENCRYPT_METHOD, $key, 0, $iv);
    }

    public static function getSecretKey(){
        return ArrayHelper::getValue(\Yii::$app->getModule('magicsoft')->encryptOptions, 'secretKey', self::SECRET_KEY);
    }

    public static function getSecretIv(){
        return ArrayHelper::getValue(\Yii::$app->getModule('magicsoft')->encryptOptions, 'secretIv', self::SECRET_IV);
    }
}