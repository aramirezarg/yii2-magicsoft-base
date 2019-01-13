<?php
namespace magicsoft\base;

/**
 * MagicSelect Module
 */
class MagicsoftModule extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'magicsoft\base\controllers';
    
    public $modelsOptions;

    public $encryptOptions = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }
    
    public static function getSorceLangage(){
        $self = new self([]);
        $class = get_class($self);
        $reflector = new \ReflectionClass($class);
        return dirname($reflector->getFileName());
    }
}