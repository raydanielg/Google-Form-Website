<?php
/**
 * Copyright (C) Baluart.COM - All Rights Reserved
 *
 * @since 1.1
 * @author Baluart E.I.R.L.
 * @copyright Copyright (c) 2015 - 2023 Baluart E.I.R.L.
 * @license http://codecanyon.net/licenses/faq Envato marketplace licenses
 * @link https://easyforms.dev/ Easy Forms
 */

namespace app\modules\update\controllers;

use Exception;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\Cookie;
use yii\helpers\Url;
use yii\httpclient\Client;
use app\modules\update\helpers\Setup;

class StepController extends Controller
{
    public $layout = 'setup';

    private $activatePurchaseCode;

    private $activateDomain;

    /**
     * @inheritdoc
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        Yii::$app->language = isset(Yii::$app->request->cookies['language']) ?
            (string)Yii::$app->request->cookies['language'] : 'en-US';

        if (!parent::beforeAction($action)) {
            return false;
        }

        if ($this->action->id != '1') {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
        }

        $this->activateDomain = Url::home(true);
        $this->activatePurchaseCode = base64_decode(Setup::$purchaseCode);

        return true; // or false to not run the action
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Language selector
     *
     * @return string
     */
    public function action1()
    {
        if (Yii::$app->request->post('language')) {

            $language = Yii::$app->request->post('language');
            Yii::$app->language = $language;

            $languageCookie = new Cookie([
                'name' => 'language',
                'value' => $language,
                'expire' => time() + 60 * 60 * 24, // 1 day
            ]);

            Yii::$app->response->cookies->add($languageCookie);

			return $this->redirect(['step/2']);
        }

        return $this->render('1');
    }

    /**
     * Requirements
     *
     * @return string
     */
    public function action2()
    {
        return $this->render('2');
    }

    /**
     * Run update
     *
     * @return string
     */
    public function action3()
    {
        return $this->render('3');
    }

    /**
     * Run Migrations vía ajax request
     *
     * @return array
     */
    public function action4()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return Setup::runMigrations();
        }

        return ['success' => 0];
    }

    /**
     * Congratulations
     *
     * @return string
     */
    public function action5()
    {
        // Update DB version
        Yii::$app->settings->set('app.version', Yii::$app->version);

        return $this->render('5');
    }
}
