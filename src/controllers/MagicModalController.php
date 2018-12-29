<?php
/**
 * Created by PhpStorm.
 * User: ethan
 * Date: 12/21/18
 * Time: 8:16 AM
 */

namespace magicsoft\base\controllers;

use magicsoft\base\MagicSoftModule;
use magicsoft\base\TranslationTrait;
use yii\web\Controller;

class MagicModalController extends Controller
{
    use TranslationTrait;

    public function init()
    {
        $this->initI18N(MagicSoftModule::getSorceLangage(), 'magicmodal');
        parent::init();
    }

    public function actionGetModalLanguage(){

        if(!\Yii::$app->request->isAjax) throw new NotFoundHttpException('The requested page does not exist.');
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return [
            'confirmToLoad' => [
                'title' => \Yii::t('magicmodal', 'Confirmation to load'),
                'message' => \Yii::t('magicmodal', 'Execute this action?'),
            ],
            'confirmToSend' => [
                'title' => \Yii::t('magicmodal', 'Confirmation to send'),
                'message' => \Yii::t('magicmodal', 'Send data to server?'),
            ],
            'confirmToClose' => [
                'title' => \Yii::t('magicmodal', 'Confirmation to close'),
                'message' => \Yii::t('magicmodal', 'If you close the screen you may lose important information. <br> <br> <strong> Do you want to continue?</strong>'),
            ],
            'confirmToReload' => [
                'title' => \Yii::t('magicmodal', 'Reload this window'),
                'message' => \Yii::t('magicmodal', 'This action will return your data to the latest update. <br> <br> <strong> Recent changes will be lost.</strong>'),
            ],
            'confirmTexts' => [
                'ok' => \Yii::t('magicmodal', 'Ok'),
                'cancel' => \Yii::t('magicmodal', 'Cancel'),
            ],
        ];
    }
}