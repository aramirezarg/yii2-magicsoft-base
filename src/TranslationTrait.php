<?php

/**
 * @package   yii2-magicsoft-base
 * @author    Alfredo Ramirez <alfredrz2012@gmail.com>
 * @copyright Copyright &copy; Alfredo Ramirez, 2017- 2018
 * @version   1.0.0
 */

namespace magicsoft\base;

use ReflectionClass;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * TranslationTrait manages methods for all translations used in Magicsoft extensions
 *
 * @author Alfreo Ramirez <alfredrz2012@gmail.com>
 */
trait TranslationTrait
{
    /**
     * @var array the the internalization configuration for this widget.
     *
     * @see [[\yii\i18n\I18N]] component for understanding the configuration details.
     */
    public $i18n = [];

    /**
     * @var string translation message file category name for i18n.
     *
     * @see [[\yii\i18n\I18N]]
     */
    protected $_langCat = '';

    /**
     * Yii i18n messages configuration for generating translations
     *
     * @param string $dir the directory path where translation files will exist
     * @param string $cat the message category
     *
     * @throws \ReflectionException
     */
    public function initI18N($dir = '', $cat = '')
    {
        if (empty($cat) && empty($this->_langCat)) {
            return;
        }
        if (empty($cat)) {
            $cat = $this->_langCat;
        }
        if (empty($dir)) {
            $class = get_class($this);
            $reflector = new ReflectionClass($class);
            $dir = dirname($reflector->getFileName());
        }
        Yii::setAlias("@{$cat}", $dir);
        $config = [
            'class' => 'yii\i18n\PhpMessageSource',
            'basePath' => "@{$cat}/messages",
            'forceTranslation' => true,
        ];
        $globalConfig = ArrayHelper::getValue(Yii::$app->i18n->translations, "{$cat}*", []);
        if (!empty($globalConfig)) {
            $config = array_merge($config, is_array($globalConfig) ? $globalConfig : (array)$globalConfig);
        }
        if (!empty($this->i18n) && is_array($this->i18n)) {
            $config = array_merge($config, $this->i18n);
        }
        Yii::$app->i18n->translations["{$cat}*"] = $config;
    }
}
