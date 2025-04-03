<?php

use app\helpers\Html;
use kartik\select2\Select2;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $forms array [id => name] of form models */
/* @var $formModel app\models\Form */
/* @var $formID int|null */

$this->title = $formModel->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Forms'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $formModel->name, 'url' => ['view', 'id' => $formModel->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Add-Ons');

$url = Url::to(['/addons/admin/get', 'id' => $formID]);
?>
<div class="form-addons">
    <div class="row">
        <div class="col-md-12">
            <div id="addons-form-grid" class="grid-view">
                <div class="panel info">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <?= Yii::t('app', 'Add-ons') .' <small class="panel-subtitle hidden-xs">'.
                            Yii::t('app', 'Extend and Expand the functionality of your form').'</small>'; ?>
                        </h3>
                        <div class="clearfix"></div>
                    </div>
                    <div class="kv-panel-before" style="padding: 25px; background-color: #F3F5F7">
                        <div class="row">
                            <div class="col-sm-12">
                                <h5 class="control-label" style="margin-top: 0; font-weight: bold">
                                    <?= Yii::t('app', 'Select a Form') ?>
                                    <?= Html::a(
                                        Html::tag('span', '', [
                                            'class' => 'glyphicon glyphicon-question-sign',
                                            'style' => '',
                                        ]),
                                        false,
                                        [
                                            'data-toggle' => 'tooltip',
                                            'data-placement'=> 'top',
                                            'title' => Yii::t(
                                                'app',
                                                'You can select other forms to manage the add-ons configuration faster.'
                                            ),
                                            'class' => '',
                                            'style' => 'color: black; font-size: 12px;'
                                        ]
                                    ) ?>
                                </h5>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <?php echo Select2::widget([
                                    'name' => 'forms',
                                    'attribute' => 'forms',
                                    'data' => $forms,
                                    'value' => isset($formModel->id) ? $formModel->id : '',
                                    'options' => ['placeholder' => 'Select a form ...'],
                                    'pluginOptions' => [
                                        'allowClear' => true
                                    ],
                                    'pluginEvents' => [
                                        "select2:select" => "function(e) {
                                        var data = e.params.data;
                                        getAddOns(data.id);
                                    }",
                                        "select2:unselect" => "function(e) {
                                        getAddOns();
                                    }",
                                    ],
                                ]); ?>
                            </div>
                            <div class="col-sm-6">
                                <a class="btn btn-primary" href="<?= Url::to(['/addons/admin/index']) ?>">
                                    <?= Yii::t('app', 'Add-Ons Management') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div id="addons-grid-container" class="kv-grid-container" style="padding: 10px 25px 25px 25px">
                        <div class="row">
                            <div class="col-sm-6">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th><?= Yii::t('app', 'Active Add-Ons') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody id="activeAddons">
                                        <tr>
                                            <td><?= Yii::t('app', 'Loading...') ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-sm-6">
                                <table class="table table-striped table-hover">
                                    <thead>
                                    <tr>
                                        <th><?= Yii::t('app', 'Available Add-Ons') ?></th>
                                    </tr>
                                    </thead>
                                    <tbody id="availableAddons">
                                    <tr>
                                        <td><?= Yii::t('app', 'Loading...') ?></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

$noResults = "<tr><td>" . Yii::t('app', 'No results found.'). "</td></tr>";

$script = <<<JS

    function addParamToURL(url, param, value){
        url += (url.split('?')[1] ? '&':'?') + param + '=' + value;
        return url;
    }

    function getAddOns(formID = null) {
        var url = formID !== null ? addParamToURL("{$url}", "id", formID) : "{$url}";
        $.ajax({url: url, success: function(result){
            var activeEl = $("#activeAddons");
            var availableEl = $("#availableAddons");
            activeEl.html('');
            availableEl.html('');
            if (result.active.length < 1) {
                activeEl.html('{$noResults}');
            }
            if (result.available.length < 1) {
                availableEl.html('{$noResults}');
            }
            $.each(result.active, function(key, value) {
                $.each(value, function(key, value) {
                    activeEl.append(value.html);                    
                });
            });
            $.each(result.available, function(key, value) {
                availableEl.append(value.html)
            });
        }});
    }

    $(function () {
        $("[data-toggle='tooltip']").tooltip();
    });;

    $(document).ready(function() {
        getAddOns();
    });
JS;

$this->registerJs($script, $this::POS_END);

?>