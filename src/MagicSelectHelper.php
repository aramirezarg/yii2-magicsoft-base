<?php
/**
 * Created by PhpStorm.
 * User: ethan
 * Date: 11/5/18
 * Time: 6:15 PM
 */

namespace magicsoft\base;

use Yii;
use yii\helpers\ArrayHelper;

class MagicSelectHelper
{
    public static $modelsDefaultOptions = [
        'default' => [
            'icon' => 'fa fa-list',
            'modal' => [true]
        ]
    ];

    /**
     * @return array
     */
    public static function mergeConfiguration()
    {
        return array_merge(self::$modelsDefaultOptions, Yii::$app->getModule('magicsoft')->modelsOptions);
    }

    /**
     * @param null $model
     * @return mixed
     */
    public static function getIcon($model = null)
    {
        return ArrayHelper::getValue(self::getModel($model), 'icon', self::getDefaultModel()['icon']);
    }

    
    public static function getParent($model = null)
    {
        $group = ArrayHelper::getValue(self::getModel($model), 'group', self::getDefaultModel()['group']);
        
        return ArrayHelper::getValue($group, 'parent', null);
    }

    public static function getShield($model = null)
    {
        $group = ArrayHelper::getValue(self::getModel($model), 'group', self::getDefaultModel()['group']);

        return ArrayHelper::getValue($group, 'shield', null);
    }

    public static function getSingularTitle($model = null)
    {
        $array_title = ArrayHelper::getValue(self::getModel($model), 'title', []);
        return ArrayHelper::getValue($array_title, 'singular', is_array($array_title) ? Yii::$app->controller->id : $array_title);
    }

    public static function getPluralTitle($model = null)
    {
        $array_title = ArrayHelper::getValue(self::getModel($model), 'title', []);
        return ArrayHelper::getValue($array_title, 'plural', is_array($array_title) ? Yii::$app->controller->id : $array_title);
    }

    public static function isAjax($model = null)
    {
        $ajax = ArrayHelper::getValue(self::getModel($model), 'modal', []);

        return ArrayHelper::getValue($ajax, 0, false);
    }

    public static function isFreeAjax($model = null, $action)
    {
        $ajax = ArrayHelper::getValue(self::getModel($model), 'modal', []);

        if (self::isAjax($model)) {
            return in_array($action, ArrayHelper::getValue($ajax, 'free', []));
        }
        
        return true;
    }
    
    /**
     * @param null $model
     * @return mixed
     */
    public static function getModel($model = null)
    {
        $model = $model ? $model : Yii::$app->controller->id;
        return ArrayHelper::getValue(self::mergeConfiguration(), $model, self::getDefaultModel());
    }

    /**
     * @return mixed
     */
    public static function getDefaultModel()
    {
        return self::mergeConfiguration()['default'];
    }

    public static function getDataReturnType($data)
    {
        if(is_array($array = explode(':', $data))){
            return current($array);
        }else{
            return 'attr';
        }
    }

    public static function getDataReturnQuery($data_return_type, $data_return)
    {
        if(is_array($array = explode(':', $data_return))){
            return ($data_return_type == 'field' ? 'id,' : '') . end($array) . ($data_return_type == 'field' ? ' as text' : '');
        }else{
            return ($data_return_type == 'field' ? 'id,' : '') . $data_return . ($data_return_type == 'field' ? ' as text' : '');
        }
    }

    public static function getDataSelect($resultModel, $join, $dataReturn)
    {
        $data_return_type = self::getDataReturnType(MagicCrypto::decrypt($dataReturn));
        $column_description = self::getDataReturnQuery($data_return_type, MagicCrypto::decrypt($dataReturn));

        if($data_return_type == 'field'){
            $resultModel->select($column_description);
            $out['results'] = $resultModel->asArray()->all();
        }else{
            if($data_return_type == 'attr' || $data_return_type == 'join'){
                $data = ArrayHelper::map( $resultModel->all(),
                    function ($model){
                        return $model->id;
                    },
                    function ($model) use($join, $dataReturn) {
                        return self::getDataDescription(($join ? $model->{$join} : $model), $dataReturn);
                    }
                );

                foreach ($data as $key => $_data){
                    $out['results'][] = ['id' => $key, 'text' => $_data];
                }
            }
        }

        return isset($out) ? $out : ['results' => ['id' => null, 'text' => $data_return_type]];
    }

    public static function getDataDescription($model, $dataReturn)
    {
        $data_return_type = self::getDataReturnType(MagicCrypto::decrypt($dataReturn));
        $column_description = self::getDataReturnQuery($data_return_type, MagicCrypto::decrypt($dataReturn));

        if($data_return_type == 'attr' || $data_return_type == 'join'){
            $data_return = '';
            foreach ($columns = explode(',', $column_description) as $column){
                $data_return .= $model->{$column} . ' | '  ;
            }
            return substr($data_return, 0, strlen($data_return) - 2);
        }
        return $model->{$column_description};
    }
}