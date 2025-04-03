<?php

namespace app\components\widgets;

use kartik\base\Config;
use kartik\depdrop\DepDrop as BaseDepDrop;
use yii\base\InvalidConfigException;

class DepDrop extends BaseDepDrop
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        if (empty($this->pluginOptions['url'])) {
            throw new InvalidConfigException("The 'pluginOptions[\"url\"]' property has not been set.");
        }
        if (empty($this->pluginOptions['depends']) || !is_array($this->pluginOptions['depends'])) {
            throw new InvalidConfigException("The 'pluginOptions[\"depends\"]' property must be set and must be an array of dependent dropdown element identifiers.");
        }
        if (empty($this->options['class']) || $this->options['class'] === 'form-control') {
            $this->options['class'] = 'form-select';
        }
        if ($this->type === self::TYPE_SELECT2) {
            Config::checkDependency('select2\Select2', 'yii2-widget-select2', 'for dependent dropdown for Select2');
        }
        if ($this->type !== self::TYPE_SELECT2 && !empty($this->options['placeholder'])) {
            $this->data = ['' => $this->options['placeholder']] + $this->data;
        }
        $this->registerAssets();
    }
}