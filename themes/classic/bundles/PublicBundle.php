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
use yii\web\View;

/**
 * Class PublicBundle
 *
 * @package app\themes\classic\bundles
 */
class PublicBundle extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web/themes/classic/assets';
    public $css = [
        'css/public.css',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
    public function init()
    {
        $this->jsOptions['position'] = View::POS_BEGIN;
        parent::init();
    }
}
