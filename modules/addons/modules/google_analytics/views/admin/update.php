<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\addons\modules\google_analytics\models\Account */
/* @var $forms array [id => name] of Form models */

$this->title = Yii::t('app', 'Update Form Tracking');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Add-ons'), 'url' => ['/addons']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Google Analytics'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Form Tracking') .' '.
    $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<!-- Page header -->
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <?= $this->render('@app/themes/next/views/partials/_breadcrumbs') ?>
            </div>
        </div>
    </div>
</div>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row">
            <div class="col">
                <h2 class="page-title">
                    <?= Html::encode($this->title) ?>
                </h2>
            </div>
        </div>
    </div>
</div>
<!-- Page body -->
<div class="page-body">
    <div class="container-xl">
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <?= Html::encode($this->title) ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <?= $this->render('_form', [
                            'model' => $model,
                            'forms' => $forms,
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
