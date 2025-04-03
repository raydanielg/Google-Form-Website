<?php
/**
 * Copyright (C) Baluart.COM - All Rights Reserved
 *
 * @since 1.0
 * @author Baluart E.I.R.L.
 * @copyright Copyright (c) 2015 - 2023 Baluart E.I.R.L.
 * @license http://codecanyon.net/licenses/faq Envato marketplace licenses
 * @link https://easyforms.dev/ Easy Forms
 */

namespace app\themes\classic\bundles;

use yii\web\AssetBundle;

/**
 * Class ThemeEditorBundle
 *
 * @package app\themes\classic\bundles
 */
class ThemeEditorBundle extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web/themes/classic/assets';
    public $css = [
    ];
    public $js = [
        'js/libs/ace.js',
        'js/theme.editor.min.js',
    ];
    public $depends = [
        'yii\bootstrap\BootstrapPluginAsset', // Load jquery.js and bootstrap.js first
    ];
}
