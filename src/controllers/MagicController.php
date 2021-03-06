<?php
/**
 * User: aramirezarg
 * Date: 27/5/2017
 * Time: 11:39 AM
 */

namespace magicsoft\base\controllers;

use magicsoft\base\MagicSelectHelper;
use magicsoft\base\MagicsoftModule;
use magicsoft\base\TranslationTrait;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

trait MagicController
{
    use TranslationTrait;

    public $errorSummaryCssClass = 'error-summary';
    public $encodeErrorSummary = true;

    public function init()
    {
        $this->initI18N(MagicsoftModule::getSorceLangage(), 'magicbase');
        parent::init();
    }

    public $baseView = '';

    protected function getErrors($_errors)
    {
        $title = Yii::t('yii', 'Please fix the following errors:');
        $errors = '';
        foreach ($_errors as $row) {
            foreach ( $row as $f){
                $errors .= '<span style=\'color: red\' class = \'glyphicon glyphicon-remove-sign\'></span> ' . $f . '<br>';
            }
        }
        return ['title' => $title, 'data' => $errors];
    }

    /**
     * @param $param
     * @throws \yii\base\InvalidConfigException
     * @return boolean
     */
    protected function getParam($param)
    {
        return ArrayHelper::getValue(\Yii::$app->request->getQueryParams(), $param, ArrayHelper::getValue(\Yii::$app->request->getBodyParams(), $param, null));
    }

    protected function getAction()
    {
        return Yii::$app->controller->action->id;
    }

    /*
     * $options = [
     *      'view'      => null or 'string',    //view to render other view
     *      'mode'      => [true or false, ['relationOne', 'relationTwo', '...']], //[0] = Define save or saveAll, [1] = Define relation to save in 0 = true
     *      'redirect'  => [
     *          'action'    => string,  //action in controller
     *          'param'     => string,  //param in actionController
     *          'attribute' => string,  //attribute in model to get value of param.
     *      ],
     *      'image'     =>  [
     *          'model'     => string,  //model to save image, null to same.
     *          'alias'     => string,  //path to save the file
     *          'attribute' => string,  //attribute in model for image or file
     *          'id'        => string   //attribute for to get id in model
     *      ],
     *      'returnUrl'  => [
     *          'action'    => string,  //action in controller
     *          'param'     => string,  //param in actionController
     *          'attribute' => string,  //attribute in model to get value of param.
     *      ],
     *      'call_back_functions' => [] //Function for after save
     * ]
     */

    /**
     * @param $model
     * @param array $options
     * @return array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\ErrorException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    protected function save($model, $options = [])
    {
        if(!$model) return $this->pageNotFound();
        $modal = $this->getParam('magic_modal_name');

        $view                   = ArrayHelper::getValue($options, 'view', null);
        $mode                   = ArrayHelper::getValue($options, 'mode', [false, []]);
        $redirect               = ArrayHelper::getValue($options, 'redirect', null);
        $returnUrl              = ArrayHelper::getValue($options, 'returnUrl', null);
        $call_back_functions    = ArrayHelper::getValue($options, 'call_back_functions', []);

        $load = $mode[0] ? 'loadAll' : 'load';
        $save = $mode[0] ? 'saveAll' : 'save';

        if( $model->{$load}(Yii::$app->request->post()))
        {
            $thisTrans = Yii::$app->getDb()->beginTransaction();

            if( $model->{$save}($save == 'saveAll' ? $this->getOnlyRelationsUpdate($model, $mode[1]) : true))
            {
                foreach ($call_back_functions as $function){
                    $params = ArrayHelper::getValue($function, 'params', []);
                    if(!$function['model']->{$function['function']}($params)){
                        if($function['use_transaction']){
                            $thisTrans->rollBack();
                            return $this->responseErrors($function['model']);
                        }
                    }
                }

                $thisTrans->commit();

                if(is_array($redirect)) {
                    return $this->redirect([$this->baseView . $redirect['action'], $redirect['param'] => $model->{$redirect['attribute']}] );
                }else{
                    if($modal){
                        if(is_array($returnUrl)){
                            $params = '';
                            if(is_array($returnUrl['param'])) {
                                foreach ($returnUrl['param'] as $key => $param){
                                    $params .= $key . '=' .  $model{$param} . '&';
                                }
                            }else{
                                $params = $returnUrl['param'] . '=' . $model{$returnUrl['attribute']} . '&';
                            }

                            $setUrl = [
                                'setUrl'    => true,
                                'url'       => Url::to($returnUrl['action'] . '?' . $params),
                                'close_parent' => ArrayHelper::getValue($returnUrl, 'close_parent', true),
                                'call_back_function' => ArrayHelper::getValue($returnUrl, 'call_back_function', '')
                            ];
                            return $this->responseSuccesss($setUrl);
                        }

                        $magic_response = [];
                        if($magic_select_attribute = $this->getParam('magic_select_attribute')){
                            $magic_response = [
                                'magic_select_attribute' => $magic_select_attribute,
                                'magic_select_value' => $model->id,
                                'magic_select_text' => MagicSelectHelper::getDataDescription($model, $this->getParam('magic_select_return_data'))
                            ];
                        }

                        return $this->responseSuccesss($magic_response);
                    }else{
                        return $this->redirect([ $this->baseView. 'view',
                            'id' => $model->id
                        ]);
                    }
                }
            }else{
                if( $modal ){
                    return $this->responseErrors($model);
                }else{
                    return $this->render(
                        ($view ? $this->baseView . $view : (($this->getAction() == 'create') ? $this->baseView . 'create' : $this->baseView .'update')), [
                            'model' => $model
                        ]
                    );
                }
            }
        } else {
            return $this->renderIsAjax(
                ($view ? $this->baseView . $view : (($this->getAction() == 'create') ? $this->baseView .'create' : $this->baseView .'update')), ['model' => $model]
            );
        }
    }

    /**
     * @param $model
     * @param $relationsUpdated
     * @return array
     */
    private function getOnlyRelationsUpdate($model, $relationsUpdated)
    {
        $allRelations =  $model->getRelationData();
        return array_diff(array_keys($allRelations), $relationsUpdated);
    }

    /**
     * @param $view
     * @param array $params
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    protected function renderIsAjax($view, $params = [])
    {
        $modal = $this->getParam('magic_modal_name');
        if ( $modal && Yii::$app->request->isAjax ) {
            return $this->renderAjax($view, $params);
        } else {
            if(!MagicSelectHelper::isFreeAjax(Yii::$app->controller->id, $this->action->id)){
                throw new NotFoundHttpException("The request page does no exist.");
            }
            return $this->render($view, $params);
        }
    }

    /**
     * @param $model
     * @param array $params
     * @param null $data
     * @return array
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    protected function responseErrors($model, $params = [], $data = null)
    {
        $target = $this->getParam('target');
        if ( $target !== '_blank' && Yii::$app->request->isAjax ) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            return array_merge( [
                'error' => true,
                'data'  => (( $data && is_array( $data ) ) ? $data : $this->getErrors( $model ? $model->getErrors() : null ) )
            ], $params );
        }elseif ($target !== '_blank'){
            throw new ForbiddenHttpException(ArrayHelper::getValue($data, 'data', \Yii::t('magicbase', 'The operation was not completed')));
        }
    }

    /**
     * @param $model
     * @param array $params
     * @param null $data
     * @return array
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    protected function responseError($onFlash = false, $title = 'Operation not completed', $message = 'The action not completed')
    {
        if (Yii::$app->request->isAjax && !$onFlash) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return [
                'type' => 'warning',
                'error' => true,
                'data' =>  [
                    'title' => $title,
                    'message' => $message
                ]
            ];
        }else{
            Yii::$app->session->setFlash('error', [
                'type' => 'warning',
                'title' => $title,
                'message' => $message
            ]);
        }
    }

    protected function setFlashErrosFromModel($model){
        Yii::$app->session->setFlash('error', [
            'type' => 'warning',
            'title' => Yii::t('yii', 'Please fix the following errors:'),
            'message' => $this->errorSummary($model)
        ]);
    }

    protected function errorSummary($models, $options = [])
    {
        Html::addCssClass($options, $this->errorSummaryCssClass);
        $options['encode'] = $this->encodeErrorSummary;
        return Html::errorSummary($models, $options);
    }

    protected function responseSuccess($onFlash = false, $title = 'Operation completed', $message = 'The action has successfull')
    {
        if (Yii::$app->request->isAjax && !$onFlash) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return [
                'error' => true,
                'data' =>  [
                    'title' => $title,
                    'message' => $message
                ]
            ];
        }else{
            Yii::$app->session->setFlash('success', [
                'type' => 'success',
                'title' => $title,
                'message' => $message
            ]);
        }
    }

    protected function responseSuccesss($params = [])
    {
        $target = $this->getParam('target');
        if ( $target !== '_blank' && Yii::$app->request->isAjax ) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return array_merge([
                'error' => false,
                'data'  => [
                    'title' => \Yii::t('magicbase', 'Completed'),
                    'data' => \Yii::t('magicbase', 'The operation was completed successfully'),
                ]
            ], $params);
        }elseif ($target !== '_blank'){
            $this->successMgs();
        }
    }

    public function requestIsAjax()
    {
        return Yii::$app->request->isAjax;
    }

    protected function successMgs($msg = null, $title = null)
    {
        \Yii::$app->getSession()->setFlash('success', [
            'type' => 'success',
            'message' => ($msg)? $msg : \Yii::t('magicbase', 'Completed'),
            'title' => ($title)? $title : \Yii::t('magicbase', 'The operation was not completed'),
        ]);
    }

    protected function errorMgs($msg = null, $title = null)
    {
        \Yii::$app->getSession()->setFlash('success', [
            'type' => 'danger',
            'message' => ($msg)? $msg : \Yii::t('magicbase', 'Not completed'),
            'title' => ($title)? $title : \Yii::t('magicbase', 'The operation was not completed'),
        ]);
    }
}