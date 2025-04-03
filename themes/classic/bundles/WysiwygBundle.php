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
 * Class WysiwygBundle
 *
 * @package app\themes\classic\bundles
 */
class WysiwygBundle extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web/themes/classic/assets/js/form.builder/lib/tinymce';
    public $js = [
        'tinymce.min.js',
    ];
    public $depends = [
        'app\themes\classic\bundles\AppBundle', // Load jquery.js and bootstrap.js first
    ];
}
