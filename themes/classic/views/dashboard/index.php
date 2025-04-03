<?php

use app\helpers\ArrayHelper;
use app\models\FormSubmission;
use Carbon\Carbon;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use kartik\date\DatePicker;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $stats array */
/* @var $formsByUsers array */
/* @var $formsByStarters array */
/* @var $formsByConversions array */
/* @var $updatedForms array */
/* @var $unreadSubmissions FormSubmission[] */
/* @var $myLastSubmissions FormSubmission[] */
/* @var $data array */
/* @var $startDate string */
/* @var $endDate string */

$this->title = Yii::t('app', 'Dashboard');
$this->params['breadcrumbs'][] = Yii::t('app', 'Overview');

Carbon::setLocale(substr(Yii::$app->language, 0, 2)); // eg. en-US to en

// Chart data
$users = ArrayHelper::getColumn($data, 'users');
$users = array_map(function($v){
    return $v ?: 0;
},$users);
$starters = ArrayHelper::getColumn($data, 'starters');
$starters = array_map(function($v){
    return $v ?: 0;
},$starters);
$conversions = ArrayHelper::getColumn($data, 'conversions');
$conversions = array_map(function($v){
    return $v ?: 0;
},$conversions);
$labels = ArrayHelper::getColumn($data, 'labels');

$options = [
    'users' => $users,
    'starters' => $starters,
    'conversions' => $conversions,
    'labels' => $labels,
];

// Pass php options to javascript
$this->registerJs("var options = ".json_encode($options).";", View::POS_BEGIN, 'dashboard-options');

?>

<?php if (@file_exists(Yii::getAlias('@app/install.php'))): ?>
<div class="alert-danger alert fade in">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <span class="glyphicon glyphicon-remove-sign"> </span> <?= Yii::t(
        'app',
        "For security reasons you must remove the 'install.php' file from your application directory."
    ) ?>
</div>
<?php endif; ?>

<?php if (@file_exists(Yii::getAlias('@app/easy_forms.sql'))): ?>
<div class="alert-danger alert fade in">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <span class="glyphicon glyphicon-remove-sign"> </span> <?= Yii::t(
        'app',
        "For security reasons you must remove the 'easy_forms.sql' file from your application directory."
    ) ?>
</div>
<?php endif; ?>

<?php if (Yii::$app->getModule('update')): ?>
<div class="alert-danger alert fade in">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <span class="glyphicon glyphicon-remove-sign"> </span> <?= Yii::t(
        'app',
        "For security reasons you must disable the application updates. Add-ons features are disabled to avoid unexpected behaviour in the meantime."
    ) ?>
</div>
<?php endif; ?>

<div class="dashboard-page">
    <div class="page-header">
        <h1><?= Yii::t('app', 'Dashboard') ?>
            <small><?= Yii::t('app', 'Overview') ?></small>
        </h1>
    </div>

    <div class="row">
        <?= Html::beginForm(['/dashboard'], 'get') ?>
        <div class="col-sm-3" style="margin-bottom: 20px">
            <?php
            echo DatePicker::widget([
                'name' => 'start_date',
                'type' => DatePicker::TYPE_COMPONENT_APPEND,
                'removeButton' => false,
                'value' => $startDate,
                'options' => [
                    'placeholder' => Yii::t('app', 'Select start date...'),
                ],
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                ]
            ]);
            ?>
        </div>
        <div class="col-sm-3" style="margin-bottom: 20px">
            <?php
            echo DatePicker::widget([
                'name' => 'end_date',
                'type' => DatePicker::TYPE_COMPONENT_APPEND,
                'removeButton' => false,
                'value' => $endDate,
                'options' => [
                    'placeholder' => Yii::t('app', 'Select end date...'),
                ],
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd'
                ]
            ]);
            ?>
        </div>
        <div class="col-sm-6" style="margin-bottom: 20px">
            <button type="submit" class="btn btn-default"><?= Yii::t('app', 'Go!') ?></button>
        </div>
        <?= Html::endForm() ?>
    </div>

    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="panel panel-counter today-views">
                <div class="panel-counter-info">
                    <div class="panel-counter-icon"><i class="glyphicon glyphicon-parents"></i></div>
                    <div class="panel-counter-title">
                        <div class="counter"><?= $stats['form_users'] ?></div>
                        <div class="counter-title"><?= Yii::t('app', 'Unique Users') ?></div>
                    </div>
                </div>
                <div class="panel-counter-sub">
                    <h5>
                        <span class="total-counter"><?= $stats['start_typing_rate'] ?>%</span>
                        <?= Yii::t('app', 'Starter Rate') ?>
                        <span class="glyphicon glyphicon-play"></span>
                    </h5>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="panel panel-counter today-submissions">
                <div class="panel-counter-info">
                    <div class="panel-counter-icon"><i class="glyphicon glyphicon-send"></i></div>
                    <div class="panel-counter-title">
                        <div class="counter"><?= $stats['form_starters'] ?></div>
                        <div class="counter-title"><?= Yii::t('app', 'Form Starters') ?></div>
                    </div>
                </div>
                <div class="panel-counter-sub">
                    <h5>
                        <span class="total-counter"><?= $stats['completion_rate'] ?>%</span>
                        <?= Yii::t('app', 'Completion Rate') ?>
                        <span class="glyphicon glyphicon-play"></span>
                    </h5>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="panel panel-counter submission-rate">
                <div class="panel-counter-info">
                    <div class="panel-counter-icon"><i class="glyphicon glyphicon-charts"></i></div>
                    <div class="panel-counter-title">
                        <div class="counter"><?= $stats['form_conversions'] ?></div>
                        <div class="counter-title"><?= Yii::t('app', 'Conversions') ?></div>
                    </div>
                </div>
                <div class="panel-counter-sub">
                    <h5>
                        <span class="total-counter"><?= $stats['conversion_rate'] ?>% </span>
                        <?= Yii::t('app', 'Conversion Rate') ?>
                    </h5>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="panel panel-counter create-form">
                <?php if (Yii::$app->user->can('createForms')) : ?>
                    <h1>
                        <a href="<?= Url::to(['form/create']) ?>"><span class="glyphicon glyphicon-plus"></span></a>
                    </h1>
                    <h5>
                        <a href="<?= Url::to(['form/create']) ?>"><?= Yii::t('app', 'Create form') ?></a>
                    </h5>
                <?php elseif (Yii::$app->user->can('viewForms', ['listing' => true])) : ?>
                    <h1><a href="<?= Url::to(['/form']) ?>"><span class="glyphicon glyphicon-list-alt"></span></a></h1>
                    <h5><a href="<?= Url::to(['/form']) ?>"><?= Yii::t('app', 'View forms') ?></a></h5>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.5.1/chart.min.js" integrity="sha512-Wt1bJGtlnMtGP0dqNFH1xlkLBNpEodaiQ8ZN5JLA5wpc1sUlk/O5uuOMNgvzddzkpvZ9GLyYNa8w2s7rqiTk5Q==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading"><?= Yii::t('app', 'Timeline') ?></div>
                <div class="panel-body" style="padding: 20px">
                    <div id="overview">
                        <div id="overview-chart">
                            <div class="chart-container">
                                <canvas id="chart" width="1110" height="300"></canvas>
                            </div>
                            <script>
                                var ctx = document.getElementById('chart').getContext('2d');
                                var myChart = new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        labels: options.labels,
                                        datasets: [
                                            {
                                                label: '<?= Yii::t('app', 'Users') ?>',
                                                data: options.users,
                                                pointRadius: 2,
                                                pointHoverRadius: 4,
                                                backgroundColor: [
                                                    'rgba(222, 227, 250, 1)',
                                                ],
                                                borderColor: 'rgba(71, 94, 152, 0.9)',
                                                borderWidth: 2
                                            },
                                            {
                                                label: '<?= Yii::t('app', 'Starters') ?>',
                                                data: options.starters,
                                                pointRadius: 2,
                                                pointHoverRadius: 4,
                                                backgroundColor: [
                                                    'rgba(245, 238, 247, 1)',
                                                ],
                                                borderColor: [
                                                    'rgba(155, 89, 182, 0.9)',
                                                ],
                                                borderWidth: 2
                                            },
                                            {
                                                label: '<?= Yii::t('app', 'Conversions') ?>',
                                                data: options.conversions,
                                                fill: true,
                                                pointRadius: 2,
                                                pointHoverRadius: 4,
                                                backgroundColor: [
                                                    '#fef6e0',
                                                ],
                                                borderColor: [
                                                    'rgb(248, 183, 0, 0.9)',
                                                ],
                                                borderWidth: 2
                                            }
                                        ]
                                    },
                                    options: {
                                        plugins: {
                                            legend: {
                                                labels: {
                                                    fontColor: '#313941',
                                                    boxWidth: 12,
                                                    boxHeight: 6
                                                },
                                            }
                                        },
                                        scales: {
                                            x: {
                                                grid: {
                                                    display: false
                                                },
                                                ticks: {
                                                    // For a category axis, the val is the index so the lookup via getLabelForValue is needed
                                                    callback: function(value, index, values) {
                                                        // Hide the label of every 2nd dataset
                                                        return values.length !== index + 1 ? this.getLabelForValue(value) : '';
                                                    },
                                                    align: 'start',
                                                },
                                            },
                                            y: {
                                                beginAtZero: true
                                            }
                                        }
                                    }
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-4">
            <div class="panel panel-default panel-users">
                <div class="panel-heading"><?= Yii::t('app', 'More Users') ?></div>
                <div class="list-group">
                    <?php if (count($formsByUsers) === 0): ?>
                        <div class="list-group-item"><?= Yii::t('app', 'No forms') ?></div>
                    <?php else: ?>
                        <?php foreach ($formsByUsers as $form): ?>
                            <a href="<?= Url::to(['form/analytics', 'id' => $form['id']]) ?>" class="list-group-item">
                                <span class="badge"><?= $form['users'] ?></span> <?= Html::encode($form['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading"><?= Yii::t('app', 'Last updated') ?></div>
                <div class="list-group">
                    <?php if (count($updatedForms) === 0) : ?>
                        <div class="list-group-item"><?= Yii::t('app', 'No data') ?></div>
                    <?php else: ?>
                        <?php foreach ($updatedForms as $form) : ?>
                            <a href="<?= Url::to(['form/view', 'id' => $form['id']]) ?>"
                               class="list-group-item"><?= Html::encode($form['name']) ?>
                                <span class="label label-info">
                                <?= Carbon::createFromTimestampUTC($form['updated_at'])->diffForHumans() ?>
                            </span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-4">
            <div class="panel panel-default panel-starters">
                <div class="panel-heading"><?= Yii::t('app', 'More Starters') ?></div>
                <div class="list-group">
                    <?php if (count($formsByStarters) === 0): ?>
                        <div class="list-group-item"><?= Yii::t('app', 'No form starters') ?></div>
                    <?php else: ?>
                        <?php foreach ($formsByStarters as $form): ?>
                            <a href="<?= Url::to(['form/analytics', 'id' => $form['id']]) ?>" class="list-group-item">
                                <span class="badge"><?= $form['starters'] ?></span> <?= Html::encode($form['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading"><?= Yii::t('app', 'Submit Form') ?></div>
                <div class="list-group">
                    <?php if (count($myLastSubmissions) == 0) : ?>
                        <div class="list-group-item"><?= Yii::t('app', 'No forms') ?></div>
                    <?php else: ?>
                        <?php foreach ($myLastSubmissions as $submission) : ?>
                            <a href="<?= Url::to(['/app/form', 'id' => $submission->form->hashId]) ?>"
                               class="list-group-item" title="<?= Html::encode($submission->form->name) ?>">
                                <?= StringHelper::truncateWords(Html::encode($submission->form->name), 5) ?>
                                <span class="label label-info">
                                <?= Carbon::createFromTimestampUTC($submission->created_at)->diffForHumans() ?>
                            </span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-4">
            <div class="panel panel-default panel-conversions">
                <div class="panel-heading"><?= Yii::t('app', 'More Conversions') ?></div>
                <div class="list-group">
                    <?php if (count($formsByConversions) === 0): ?>
                        <div class="list-group-item"><?= Yii::t('app', 'No conversions') ?></div>
                    <?php else: ?>
                        <?php foreach ($formsByConversions as $form): ?>
                            <a href="<?= Url::to(['form/analytics', 'id' => $form['id']]) ?>" class="list-group-item">
                                <span class="badge"><?= $form['conversions'] ?></span> <?= Html::encode($form['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading"><?= Yii::t('app', 'View Submissions') ?></div>
                <div class="list-group">
                    <?php if (count($unreadSubmissions) == 0) : ?>
                        <div class="list-group-item"><?= Yii::t('app', 'No new submissions') ?></div>
                    <?php else: ?>
                        <?php foreach ($unreadSubmissions as $submission) : ?>
                            <a href="<?= Url::to(['/form/submissions', 'id' => $submission->form->id, '#' => 'view/' . $submission->id]) ?>"
                               class="list-group-item" title="<?= Html::encode($submission->form->name) ?> #<?= $submission->id ?>">
                                <?= StringHelper::truncateWords(Html::encode($submission->form->name), 5) ?> #<?= $submission->id ?>
                                <span class="label label-info">
                                <?= Carbon::createFromTimestampUTC($submission->created_at)->diffForHumans() ?>
                            </span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
