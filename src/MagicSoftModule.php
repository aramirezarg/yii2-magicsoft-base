<?php
namespace magicsoft\base;

/**
 * MagicSelect Module
 */
class MagicSoftModule extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'magicsoft\base\controllers';

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