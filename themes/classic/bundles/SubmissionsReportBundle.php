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
 * Class SubmissionsReportBundle
 *
 * @package app\themes\classic\bundles
 */
class SubmissionsReportBundle extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web/themes/classic/assets';
    public $css = [
        'css/gridstack.css',
    ];
    public $js = [
        'js/libs/jquery-ui.js',
        'js/libs/lodash.min.js',
        'js/libs/gridstack.js',
        'js/submissions.report.min.js',
    ];
    public $depends = [
        'app\themes\classic\bundles\VisualizationBundle', // Load d3.js, crossfilter.js and dc.js first
    ];
}
