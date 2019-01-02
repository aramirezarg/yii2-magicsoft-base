<?php
/**
 * @license http://www.apache.org/licenses/LICENSE-2.0
 */

namespace magicsoft\base;

use magicsoft\base\MagicSoftModule;
use magicsoft\base\TranslationTrait;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\helpers\Json;
use yii\i18n\MessageSource;
use yii\web\AssetBundle;
use yii\web\View;
use yii\i18n\I18N;

class MagicsoftAsset extends AssetBundle
{
    use TranslationTrait;
    public $sourcePath = '@vendor/magicsoft/yii2-magicsoft-base/src/assets';

    public $js = [
		'js/magic.modal.js',
		'js/dependencies/magic.helper.js',
		'js/dependencies/magic.message.js',
		'js/dependencies/spin.js',
		'js/dependencies/jquery.form.js',
		'js/dependencies/beep.js'
    ];

    public $css = [
        'css/magicsoft.css',
    ];

    public $depends = [
        'yii\web\JqueryAsset'
    ];

    public function init()
    {
        $this->initI18N(MagicSoftModule::getSorceLangage(), 'magicmodal');
        $this->initJsLanguage();
        parent::init();
    }

    private function initJsLanguage(){
        $messages = require(MagicSoftModule::getSorceLangage() . '/messages/es/magicmodal.php');

        $_messages = '{';
        foreach ($messages as $key => $message){
            $_messages .= '"' . $key . '":"' . \Yii::t('magicmodal', $key) . '",';
        }
        $_messages = substr($_messages, 0,strlen($_messages) - 1) . '}';

        $js = "
        var ClientMagicsoftMessases = JSON.parse('$_messages');
        function MagicsoftLanguage(t) {
            return ClientMagicsoftMessases[t];
        }";

        \Yii::$app->getView()->registerJs($js, View::POS_BEGIN);
    }
}