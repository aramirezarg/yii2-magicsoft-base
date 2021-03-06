<?php

namespace magicsoft\base\controllers;

use magicsoft\base\MagicSelectHelper;
use magicsoft\base\MagicCrypto;
use magicsoft\base\TranslationTrait;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * MagicSelectController implements methods for the select.
 */
class MagicSelectController extends Controller
{
    /**
     * @var array
     * @return bool
     * @throws ForbiddenHttpException
     */
    public function beforeAction($action){
        if(Yii::$app->user->isGuest) {
            $this->redirect(Yii::$app->getHomeUrl());
        } else {
            return true;
        }
    }

    /**
     * @param $class
     * @param $search_data
     * @param $return_data
     * @param null $parent
     * @param null $parent_value
     * @param null $own_function_search
     * @param null $join
     * @param null $q
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionGetData(
        $class,
        $search_data,
        $return_data,
        $parent = null,
        $parent_value = null,
        $join = null,
        $own_function_search = null,
        $q = null
    ){
        if(!Yii::$app->request->isAjax) throw new NotFoundHttpException('The requested page does not exist.');
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $class = MagicCrypto::decrypt($class);
        $own_function_search = MagicCrypto::decrypt($own_function_search);

        $join = strtolower($join);

        if(!is_null($q)){
            if ($own_function_search) {
                $resultModel = $class::{$own_function_search}($q);
            } else {
                $resultModel = $class::find();

                if ($join) $resultModel->joinWith($join);

                $resultModel->where(['like', ($join ? $join . '.' : '') . ' concat(' . MagicCrypto::decrypt($search_data) . ')', $q]);
            }
        }else{
            if ($own_function_search) {
                $resultModel = $class::{$own_function_search}($q);
            } else {
                $resultModel = $class::find();
            }
        }

        if ($parent) $resultModel->andWhere([MagicCrypto::decrypt($parent) => $parent_value]);

        $resultModel->limit(20);

        return MagicSelectHelper::getDataSelect($resultModel, $join, $return_data);
    }
}
