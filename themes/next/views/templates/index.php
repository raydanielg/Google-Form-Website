<?php

use app\components\widgets\ActionBar;
use app\components\widgets\GridView;
use app\components\widgets\PageSizeDropDownList;
use app\helpers\ArrayHelper;
use app\helpers\IconHelper;
use app\models\Template;
use app\models\TemplateCategory;
use kartik\switchinput\SwitchInput;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\TemplateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Templates');
$this->params['breadcrumbs'][] = $this->title;

// User Preferences
$showFilters = Yii::$app->user->preferences->get('GridView.filters.state') === '1';

$options = array(
    'currentPage' => Url::toRoute(['index']), // Used by filters
    'gridViewSettingsEndPoint' => Url::to(['/ajax/grid-view-settings']),
);

$bulkActionsItems = [];
if (Yii::$app->user->can('updateTemplates', ['listing' => true])) {
    $bulkActionsItems[Yii::t('app', 'Update Promotion')] = [
        'promoted' => Yii::t('app', 'Promoted'),
        'non-promoted' => Yii::t('app', 'Non-Promoted'),
    ];
}
if (Yii::$app->user->can('deleteTemplates', ['listing' => true])) {
    $bulkActionsItems[Yii::t('app', 'General')] = [
        'general-delete' => Yii::t('app', 'Delete')
    ];
}
if (empty($bulkActionsItems)) {
    $bulkActionsItems = [
        Yii::t('app', 'General') => [],
    ];
}

$templatesByCategoriesLink = '';
if (Yii::$app->user->can('manageTemplateCategories')) {
    $templateByCategoriesLink = Html::a(Yii::t('app', 'Templates by Categories'), ['/categories'],
        [
            'data-toggle' => 'tooltip',
            'data-placement'=> 'top',
            'title' => Yii::t('app', 'Templates organized by Categories'),
            'class' => 'text hidden-xs hidden-sm'
        ]
    );
}

// Pass php options to javascript
$this->registerJs("var options = ".json_encode($options).";", View::POS_BEGIN, 'form-options');
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
    <!-- Page body -->
    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-md-12">
                    <?= GridView::widget([
                        'id' => 'template-grid',
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'resizableColumns' => false,
                        'pjax' => false,
                        'export' => false,
                        'responsive' => true,
                        'responsiveWrap' => false,
                        'bordered' => false,
                        'striped' => true,
                        'tableOptions' => [
                            'class' => $showFilters
                                ? 'table-with-filters table-vcenter card-table'
                                : 'table-vcenter card-table',
                        ],
                        'panelTemplate' =>'{panelHeading}{panelBefore}{items}{panelFooter}',
                        'panel' => [
                            'type' => GridView::TYPE_DEFAULT,
                            'headingOptions' => [
                                'class' => 'card-header',
                            ],
                            'footerOptions' => [
                                'class' => 'card-footer d-flex align-items-center',
                            ],
                            'heading' => Yii::t('app', 'Templates')
                                . ' <small class="ms-3 text-muted d-none d-sm-inline">'
                                . Yii::t('app', 'Looks & feels amazing on any device')
                                . ' </small>',
                            'before'=> ActionBar::widget([
                                'grid' => 'template-grid',
                                'templates' => Yii::$app->user->can('viewBulkActionsInTemplates') ? [
                                    '{create}' => ['class' => 'col-xs-6 col-sm-6'],
                                    '{filters}' => ['class' => 'col-xs-6 col-sm-3 col-lg-4'],
                                    '{bulk-actions}' => ['class' => 'col-sm-3 col-lg-2 d-none d-sm-block'],
                                ] : [
                                    '{create}' => ['class' => 'col-xs-6 col-sm-6'],
                                    '{filters}' => ['class' => 'col-xs-6 col-sm-6'],
                                ],
                                'elements' => [
                                    'create' => Yii::$app->user->can('createTemplates') ?
                                        Html::a(
                                            IconHelper::show('plus') . ' ' . Yii::t('app', 'Create Template'),
                                            ['create'],
                                            ['class' => 'btn btn-primary']
                                        ) . ' ' .
                                        $templatesByCategoriesLink : $templatesByCategoriesLink,
                                    'filters' => SwitchInput::widget(
                                        [
                                            'name'=>'filters',
                                            'type' => SwitchInput::CHECKBOX,
                                            'value' => $showFilters,
                                            'pluginOptions' => [
                                                'size' => 'mini',
                                                'animate' => false,
                                                'labelText' => Yii::t('app', 'Filter'),
                                            ],
                                            'pluginEvents' => [
                                                "switchChange.bootstrapSwitch" => "function(event, state) {
                                                    var show = (typeof state !== 'undefined' && state == 1) ? 1 : 0;
                                                    $.post(options.gridViewSettingsEndPoint, { 'show-filters': show })
                                                        .done(function(response) {
                                                            if (response.success) {
                                                                if (show) {
                                                                    $('.filters').fadeIn();
                                                                } else {
                                                                    $('.filters').fadeOut();
                                                                    window.location = options.currentPage;
                                                                }                   
                                                            }
                                                        });
                                                }",
                                            ],
                                            'containerOptions' => ['class' => 'text-end mt-2'],
                                        ]
                                    ),
                                ],
                                'bulkActionsItems' => $bulkActionsItems,
                                'bulkActionsOptions' => [
                                    'options' => [
                                        'promoted' => [
                                            'url' => Url::toRoute(['update-promotion', 'promoted' => 1]),
                                        ],
                                        'non-promoted' => [
                                            'url' => Url::toRoute(['update-promotion', 'promoted' => 0]),
                                        ],
                                        'general-delete' => [
                                            'url' => Url::toRoute('delete-multiple'),
                                            'data-confirm' => Yii::t('app', 'Are you sure you want to delete these templates? All data related to each item will be deleted. This action cannot be undone.'),
                                        ],
                                    ],
                                    'class' => 'form-select',
                                ],
                                'class' => 'form-control',
                            ]),
                        ],
                        'toolbar' => false,
                        'columns' => [
                            [
                                'class' => '\kartik\grid\CheckboxColumn',
                                'headerOptions' => ['class'=>'kartik-sheet-style'],
                                'rowSelectedClass' => GridView::TYPE_WARNING,
                            ],
                            [
                                'attribute'=> 'name',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $name = Html::encode($model->name);
                                    if (Yii::$app->user->can('viewTemplates', ['model' => $model])) {
                                        return Html::a($name, ['templates/view', 'id' => $model->id]);
                                    }
                                    return $name;
                                },
                            ],
                            [
                                'attribute' => 'category_id',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    if (isset($model->category, $model->category->name)) {
                                        return Html::encode($model->category->name);
                                    }
                                    return null;
                                },
                                'label' => Yii::t('app', 'Category'),
                                'filter' => Html::activeDropDownList(
                                    $searchModel,
                                    'category_id',
                                    ArrayHelper::map(
                                        TemplateCategory::find()->asArray()->all(),
                                        'id',
                                        'name'
                                    ),
                                    ['class'=>'form-select', 'prompt' => '']
                                ),
                            ],
                            [
                                'class'=>'kartik\grid\BooleanColumn',
                                'attribute'=>'promoted',
                                'trueIcon'=>'<span class="text-success">'.IconHelper::show('check').'</span>',
                                'falseIcon'=>'<span class="text-danger">'.IconHelper::show('x').'</span>',
                                'vAlign'=>'middle',
                            ],
                            [
                                'attribute' => 'shared',
                                'label' => Yii::t('app', 'Sharing'),
                                'format' => 'raw',
                                'hAlign'=>'center',
                                'width' => '125px',
                                'value' => function ($model) {
                                    $icon = '';
                                    $currentUser = Yii::$app->user;
                                    if ($currentUser->id === $model->created_by || $currentUser->can('manageTemplates')) {
                                        $icon = Html::tag('span', IconHelper::show('lock'), [
                                            'title' => $currentUser->id === $model->created_by ? Yii::t('app', 'Only you can access to this item') : Yii::t('app', 'Only you and the author can access to this item'),
                                            'class' => 'text-default',
                                        ]);
                                        if ((int) $model->shared === Template::SHARED_EVERYONE) {
                                            $icon = Html::tag('span', IconHelper::show('lock-open'), [
                                                'title' => Yii::t('app', 'Everyone can access to this item'),
                                                'class' => 'text-danger',
                                            ]);
                                        } elseif ((int) $model->shared === Template::SHARED_WITH_USERS) {
                                            $icon = Html::tag('span', IconHelper::show('users-group'), [
                                                'title' => Yii::t('app', 'Specific users can access to this item'),
                                                'class' => 'text-default',
                                            ]);
                                        }
                                    } else if ($currentUser->id !== $model->created_by) {
                                        $icon = Html::tag('span', ' ', [
                                            'title' => Yii::t('app', 'This item was shared with me'),
                                            'class' => 'fad fa-share-all text-muted',
                                            'data-toggle' => 'tooltip',
                                            'data-placement' => 'top',
                                        ]);
                                    }
                                    return $icon;
                                },
                                'filter' => Html::activeDropDownList(
                                    $searchModel,
                                    'shared',
                                    Template::sharedOptions(),
                                    ['class'=>'form-select', 'prompt' => '']
                                ),
                                'visible' => Yii::$app->user->can('shareTemplates', ['listing' => true]),
                            ],
                            [
                                'attribute' => 'lastEditor',
                                'value' => function ($model) {
                                    return isset($model->lastEditor, $model->lastEditor->username) ? Html::encode($model->lastEditor->username) : null;
                                },
                                'label' => Yii::t('app', 'Updated by'),
                                'noWrap'=>true,
                            ],
                            [
                                'attribute'=> 'updated_at',
                                'value' => function ($model) {
                                    return $model->updated;
                                },
                                'label' => Yii::t('app', 'Updated'),
                                'width' => '150px',
                                'filterType'=> \kartik\grid\GridView::FILTER_DATE_RANGE,
                                'filterWidgetOptions' => [
                                    'presetDropdown' => false,
                                    'convertFormat' => true,
                                    'containerTemplate' => '
                                        <div class="form-control kv-drp-dropdown">
                                            '. IconHelper::show('calendar') .'&nbsp;
                                            <span class="range-value">{value}</span>
                                            <span><b class="caret"></b></span>
                                        </div>
                                        {input}
                                    ',
                                    'pluginOptions' => [
                                        'showDropdowns' => true,
                                        'linkedCalendars' => false,
                                        'locale' => [
                                            'format' => 'Y-m-d',
                                            'separator' => ' - ',
                                        ],
                                        'opens' => 'left'
                                    ]
                                ],
                            ],
                            [
                                'class' => 'kartik\grid\ActionColumn',
                                'visible' => true,
                                'dropdown' => true,
                                'dropdownButton' => [
                                    'class'=>'btn btn-primary',
                                    'data-bs-config' => '{"popperConfig":{"strategy":"fixed"}}',
                                ],
                                'dropdownMenu' => [
                                    'class' => 'dropdown-menu dropdown-menu-end',
                                ],
                                'buttons' => [
                                    //update button
                                    'update' => function ($url) {
                                        return '<li>'.Html::a(
                                                '<span class="me-2">' . IconHelper::show('pencil') . '</span>' . Yii::t('app', 'Update'),
                                                $url,
                                                ['title' => Yii::t('app', 'Update'), 'class' => 'dropdown-item']
                                            ) .'</li>';
                                    },
                                    //settings button
                                    'settings' => function ($url) {
                                        return '<li>'.Html::a(
                                                '<span class="me-2">' . IconHelper::show('settings') . '</span>' . Yii::t('app', 'Settings'),
                                                $url,
                                                ['title' => Yii::t('app', 'Settings'), 'class' => 'dropdown-item']
                                            ) .'</li>';
                                    },
                                    //create form button
                                    'createForm' => function ($url) {
                                        return '<li>'.Html::a(
                                                '<span class="me-2">' . IconHelper::show('plus') . '</span>' . Yii::t('app', 'Create Form'),
                                                $url,
                                                ['title' => Yii::t('app', 'Create Form'), 'class' => 'dropdown-item']
                                            ) .'</li>';
                                    },
                                    //view button
                                    'view' => function ($url) {
                                        return '<li>'.Html::a(
                                                '<span class="me-2">' . IconHelper::show('file-info') . '</span>' . Yii::t('app', 'View Record'),
                                                $url,
                                                ['title' => Yii::t('app', 'View Record'), 'class' => 'dropdown-item']
                                            ) .'</li>';
                                    },
                                    //delete button
                                    'delete' => function ($url) {
                                        $options = array_merge([
                                            'title' => Yii::t('app', 'Delete'),
                                            'aria-label' => Yii::t('app', 'Delete'),
                                            'data-confirm' => Yii::t('app', 'Are you sure you want to delete this item? All data related to this item will be deleted. This action cannot be undone.'),
                                            'data-method' => 'post',
                                            'data-pjax' => '0',
                                            'class' => 'dropdown-item'
                                        ], []);
                                        return '<li>'.Html::a(
                                                '<span class="me-2">' . IconHelper::show('trash') . '</span>' .
                                                Yii::t('app', 'Delete'),
                                                $url,
                                                $options
                                            ).'</li>';
                                    },
                                ],
                                'urlCreator' => function ($action, $model) {
                                    if ($action === 'update') {
                                        $url = Url::to(['update', 'id' => $model->id]);
                                        return $url;
                                    } elseif ($action === "settings") {
                                        $url = Url::to(['settings', 'id' => $model->id]);
                                        return $url;
                                    } elseif ($action === "createForm") {
                                        $url = Url::to(['form/create', 'template' => $model->slug]);
                                        return $url;
                                    } elseif ($action === "view") {
                                        $url = Url::to(['view', 'id' => $model->id]);
                                        return $url;
                                    } elseif ($action === "delete") {
                                        $url = Url::to(['delete', 'id' => $model->id]);
                                        return $url;
                                    }
                                    return '';
                                },
                                'visibleButtons' => [
                                    //update button
                                    'update' => function ($model, $key, $index) {
                                        return Yii::$app->user->can('updateTemplates', ['model' => $model]);
                                    },
                                    //settings button
                                    'settings' => function ($model, $key, $index) {
                                        return Yii::$app->user->can('updateTemplates', ['model' => $model]);
                                    },
                                    //create form button
                                    'createForm' => function ($model, $key, $index) {
                                        return Yii::$app->user->can('createForms');
                                    },
                                    //view button
                                    'view' => function ($model, $key, $index) {
                                        return Yii::$app->user->can('viewTemplates', ['model' => $model]);
                                    },
                                    //delete button
                                    'delete' => function ($model, $key, $index) {
                                        return Yii::$app->user->can('deleteTemplates', ['model' => $model]);
                                    },
                                ],
                                'template' => '{update} {settings} {createForm} {view} {delete}',
                            ],
                        ],
                        'replaceTags' => [
                            '{pageSize}' => function($widget) {
                                $html = '';
                                if ($widget->panelFooterTemplate !== false) {
                                    $selectedSize = Yii::$app->user->preferences->get('GridView.pagination.pageSize');
                                    return PageSizeDropDownList::widget(['selectedSize' => $selectedSize]);
                                }
                                return $html;
                            },
                        ],
                        'panelFooterTemplate' => '
                            {pager}{pageSize}
                        ',
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
<?php
$js = <<< 'SCRIPT'

$(function () {
    // Tooltips
    $("[data-toggle='tooltip']").tooltip();
});

SCRIPT;
// Register tooltip/popover initialization javascript
$this->registerJs($js);