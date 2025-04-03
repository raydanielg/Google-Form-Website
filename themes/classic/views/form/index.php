<?php

use app\components\widgets\ActionBar;
use app\components\widgets\GridView;
use app\components\widgets\PageSizeDropDownList;
use app\helpers\ArrayHelper;
use app\helpers\Html;
use app\helpers\Language;
use app\models\Folder;
use kartik\switchinput\SwitchInput;
use yii\helpers\Url;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\FormSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $this yii\web\View */
/* @var $templates array */
/* @var $folderName Folder */
/* @var $folders Folder[] */

$this->title = Yii::t('app', 'Forms');
$this->params['breadcrumbs'][] = $this->title;

// Prepare dropdown with templates array
$templateItems = array();
if (count($templates) > 0) {
    // Set data for dropdown widget
    foreach ($templates as $template) {
        $item = [
            'label' => $template['name'],
            'url' => Url::to(['create', 'template' => $template['slug']]),
        ];
        array_push($templateItems, $item);
    }
    $itemDivider = [
        'label' => '<li role="presentation" class="divider"></li>',
        'encode' => false,
    ];
    array_push($templateItems, $itemDivider);
}

// Add link to templates
$itemMoreTemplates = [
    'label' => Yii::t('app', 'More Templates'),
    'url' => Url::to(['/templates']),
];
array_push($templateItems, $itemMoreTemplates);

// User Preferences
$showFilters = Yii::$app->user->preferences->get('GridView.filters.state') === '1';

// Folder EndPoints
$folderEndPoint = Url::to(["/folder"]);
$createFolderEndPoint = Url::to(["/folder/create"]);
$updateFolderEndPoint = Url::to(["/folder/update"]);
$deleteFolderEndPoint = Url::to(["/folder/delete"]);

$options = array(
    'currentPage' => Url::toRoute(['index']), // Used by filters
    'gridViewSettingsEndPoint' => Url::to(['/ajax/grid-view-settings']),
    'enablePrettyUrl' => Yii::$app->urlManager->enablePrettyUrl,
    'folderEndPoint' => $folderEndPoint,
    'createFolderEndPoint' => $createFolderEndPoint,
    'updateFolderEndPoint' => $updateFolderEndPoint,
    'deleteFolderEndPoint' => $deleteFolderEndPoint,
);

// Bulk Actions Items
$bulkActionsItems = [];
if (Yii::$app->user->can('deleteForms', ['listing' => true])) {
    $bulkActionsItems[Yii::t('app', 'General')] = [
        'general-delete' => Yii::t('app', 'Delete')
    ];
}
if (Yii::$app->user->can('updateForms', ['listing' => true])) {
    $bulkActionsItems[Yii::t('app', 'Update Status')] = [
        'status-active' => Yii::t('app', 'Active'),
        'status-inactive' => Yii::t('app', 'Inactive'),
    ];
}
// Bulk Actions Options
$bulkActionsOptions = [
    'status-active' => [
        'url' => Url::toRoute(['update-status', 'status' => 1]),
    ],
    'status-inactive' => [
        'url' => Url::toRoute(['update-status', 'status' => 0]),
    ],
    'general-delete' => [
        'url' => Url::toRoute('delete-multiple'),
        'data-confirm' => Yii::t('app', 'Are you sure you want to delete these forms? All stats, submissions, conditional rules and reports data related to each item will be deleted. This action cannot be undone.'),
    ],
];

// Adds Folders to Bulk Actions
$folderItems = [
    'folder-0' => Yii::t('app', 'Uncategorized'),
];
$folderOptions = [
    'folder-0' => [
        'url' => Url::toRoute(['move-to', 'folder' => 0]),
    ],
];
if (count($folders) > 0) {
    foreach ($folders as $folder) {
        $key = 'folder-' . $folder->id;
        $folderItems[$key] = $folder->name;
        $folderOptions[$key] = [
            'url' => Url::toRoute(['move-to', 'folder' => $folder->id]),
        ];
    }
}
$bulkActionsItems[Yii::t('app', 'Move To')] = $folderItems;
$bulkActionsOptions = $bulkActionsOptions + $folderOptions;

// Adds General Bulk Actions
if (empty($bulkActionsItems)) {
    $bulkActionsItems = [
        Yii::t('app', 'General') => [],
    ];
}

// Pass php options to javascript
$this->registerJs("var options = ".json_encode($options).";", View::POS_BEGIN, 'form-options');
?>
<?php

    $gridColumns = [
        [
            'class' => '\kartik\grid\CheckboxColumn',
            'headerOptions' => ['class'=>'kartik-sheet-style'],
            'rowSelectedClass' => GridView::TYPE_WARNING,
        ],
        [
            'attribute'=> 'name',
            'format' => 'raw',
            'value' => function ($model) {
                return Html::a(Html::encode($model->name), ['form/view', 'id' => $model->id]);
            },
        ],
        [
            'attribute'=>'language',
            'value'=> 'languageLabel',
            'filter' => Html::activeDropDownList(
                $searchModel,
                'language',
                Language::supportedLanguages(),
                ['class'=>'form-control', 'prompt' => '']
            ),
        ],
        [
            'class'=>'kartik\grid\BooleanColumn',
            'attribute'=>'status',
            'trueIcon'=>'<span class="glyphicon glyphicon-ok text-success"></span>',
            'falseIcon'=>'<span class="glyphicon glyphicon-remove text-danger"></span>',
            'vAlign'=>'middle',
            'noWrap'=>true,
        ],
        [
            'class'=>'kartik\grid\BooleanColumn',
            'attribute'=>'honeypot',
            'trueIcon'=>'<span class="glyphicon glyphicon-ok text-success"></span>',
            'falseIcon'=>'<span class="glyphicon glyphicon-remove text-danger"></span>',
            'vAlign'=>'middle',
            'noWrap'=>true,
        ],
        [
            'attribute' => 'shared',
            'label' => Yii::t('app', 'Sharing'),
            'format' => 'raw',
            'hAlign'=>'center',
            'value' => function ($model) {
                $icon = '';
                $currentUser = Yii::$app->user;
                if ($currentUser->id === $model->created_by || $currentUser->can('manageForms')) {
                    $icon = Html::tag('span', ' ', [
                        'title' => $currentUser->id === $model->created_by ? Yii::t('app', 'Only you can access to this item') : Yii::t('app', 'Only you and the author can access to this item'),
                        'class' => 'glyphicon glyphicon-lock text-default',
                    ]);
                    if ((int) $model->shared === \app\models\Form::SHARED_EVERYONE) {
                        $icon = Html::tag('span', ' ', [
                            'title' => Yii::t('app', 'Everyone can access to this item'),
                            'class' => 'glyphicon glyphicon-unlock text-danger',
                        ]);
                    } elseif ((int) $model->shared === \app\models\Form::SHARED_WITH_USERS) {
                        $icon = Html::tag('span', ' ', [
                            'title' => Yii::t('app', 'Specific users can access to this item'),
                            'class' => 'glyphicon glyphicon-group text-default',
                        ]);
                    }
                } else if ($currentUser->id !== $model->created_by) {
                    $icon = Html::tag('span', ' ', [
                        'title' => Yii::t('app', 'This item was shared with me'),
                        'class' => 'glyphicon glyphicon-share-alt text-default',
                    ]);
                }
                return $icon;
            },
            'filter' => Html::activeDropDownList(
                $searchModel,
                'shared',
                \app\models\Form::sharedOptions(),
                ['class'=>'form-control', 'prompt' => '']
            ),
            'visible' => Yii::$app->user->can('shareForms', ['listing' => true]),
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
            <i class="glyphicon glyphicon-calendar"></i>&nbsp;
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
        ['class' => '\kartik\grid\ActionColumn',
            'controller' => 'form',
            // Visible for all users
            'visible' => true,
            'dropdown'=>true,
            'dropdownButton' => ['class'=>'btn btn-primary'],
            'dropdownMenu' => ['class' => 'dropdown-menu-right text-left'],
            'buttons' => [
                //update button
                'update' => function ($url) {
                    return '<li>'.Html::a(
                        '<span class="glyphicon glyphicon-pencil"> </span> '. Yii::t('app', 'Update'),
                        $url,
                        ['title' => Yii::t('app', 'Update')]
                    ) .'</li>';
                },
                //settings button
                'settings' => function ($url) {
                    return '<li>'.Html::a(
                        '<span class="glyphicon glyphicon-cogwheel"> </span> '. Yii::t('app', 'Settings'),
                        $url,
                        ['title' => Yii::t('app', 'Settings')]
                    ) .'</li>';
                },
                //rule button
                'rules' => function ($url) {
                    return '<li>'.Html::a(
                        '<span class="glyphicon glyphicon-flowchart"> </span> '. Yii::t('app', 'Conditional Rules'),
                        $url,
                        ['title' => Yii::t('app', 'Conditional Rules')]
                    ) .'</li>';
                },
                //preview form button
                'view' => function ($url) {
                    return '<li>'.Html::a(
                            '<span class="glyphicon glyphicon-eye-open"> </span> ' . Yii::t('app', 'View Record'),
                            $url,
                            ['title' => Yii::t('app', 'View Record')]
                        ) .'</li>';
                },
                //copy button
                'copy' => function ($url, $model) {
                    $options = array_merge([
                        'title' => Yii::t('app', 'Copy'),
                        'aria-label' => Yii::t('app', 'Copy'),
                        'data-pjax' => '0',
                        'data-toggle' => 'modal',
                        'data-target' => '#copy-modal',
                        'data-form-id' => $model->id,
                        'data-form-name' => $model->name,
                    ], []);
                    return '<li>'.Html::a(
                            '<span class="glyphicon glyphicon-duplicate"> </span> '.
                            Yii::t('app', 'Copy'),
                            $url,
                            $options
                        ).'</li>';
                },
                //share form button
                'share' => function ($url) {
                    return '<li>'.Html::a(
                        '<span class="glyphicon glyphicon-share"> </span> '. Yii::t('app', 'Publish & Share'),
                        $url,
                        ['title' => Yii::t('app', 'Publish & Share')]
                    ) .'</li>';
                },
                //form submissions button
                'submissions' => function ($url) {
                    return '<li>'.Html::a(
                        '<span class="glyphicon glyphicon-send"> </span> '. Yii::t('app', 'Submissions'),
                        $url,
                        ['title' => Yii::t('app', 'Submissions')]
                    ) .'</li>';
                },
                //form add-ons button
                'addons' => function ($url) {
                    return '<li>'.Html::a(
                        '<span class="glyphicon glyphicon-puzzle"> </span> '. Yii::t('app', 'Add-Ons'),
                        $url,
                        ['title' => Yii::t('app', 'Add-Ons')]
                    ) .'</li>';
                },
                //form report button
                'report' => function ($url) {
                    return '<li>'.Html::a(
                        '<span class="glyphicon glyphicon-pie-chart"> </span> '. Yii::t('app', 'Submissions Report'),
                        $url,
                        ['title' => Yii::t('app', 'Submissions Report')]
                    ) .'</li>';
                },
                //form analytics button
                'analytics' => function ($url) {
                    return '<li>'.Html::a(
                        '<span class="glyphicon glyphicon-charts"> </span> '. Yii::t('app', 'Form Analytics'),
                        $url,
                        ['title' => Yii::t('app', 'Form & Submissions Analytics')]
                    ) .'</li>';
                },
                //reset stats button
                'reset_stats' => function ($url) {
                    $options = array_merge([
                        'title' => Yii::t('app', 'Reset Stats'),
                        'aria-label' => Yii::t('app', 'Reset Stats'),
                        'data-confirm' => Yii::t('app', 'Are you sure you want to delete these stats? All stats related to this item will be deleted. This action cannot be undone.'),
                        'data-method' => 'post',
                        'data-pjax' => '0',
                    ], []);
                    return '<li>'.Html::a(
                        '<span class="glyphicon glyphicon-refresh"> </span> '.
                        Yii::t('app', 'Reset Stats'),
                        $url,
                        $options
                    ).'</li>';
                },
                //delete button
                'delete' => function ($url) {
                    $options = array_merge([
                        'title' => Yii::t('app', 'Delete'),
                        'aria-label' => Yii::t('app', 'Delete'),
                        'data-confirm' => Yii::t('app', 'Are you sure you want to delete this form? All stats, submissions, conditional rules and reports data related to this item will be deleted. This action cannot be undone.'),
                        'data-method' => 'post',
                        'data-pjax' => '0',
                    ], []);
                    return '<li>'.Html::a(
                        '<span class="glyphicon glyphicon-bin"> </span> '.
                        Yii::t('app', 'Delete'),
                        $url,
                        $options
                    ).'</li>';
                },
            ],
            'urlCreator' => function ($action, $model) {
                if ($action === 'update') {
                    $url = Url::to(['form/update', 'id' => $model->id]);
                    return $url;
                } elseif ($action === "settings") {
                    $url = Url::to(['form/settings', 'id' => $model->id]);
                    return $url;
                } elseif ($action === "rules") {
                    $url = Url::to(['form/rules', 'id' => $model->id]);
                    return $url;
                } elseif ($action === "view") {
                    $url = Url::to(['form/view', 'id' => $model->id]);
                    return $url;
                } elseif ($action === "copy") {
                    return '#';
                } elseif ($action === "share") {
                    $url = Url::to(['form/share', 'id' => $model->id]);
                    return $url;
                } elseif ($action === "submissions") {
                    $url = Url::to(['form/submissions', 'id' => $model->id]);
                    return $url;
                } elseif ($action === "addons") {
                    $url = Url::to(['form/addons', 'id' => $model->id]);
                    return $url;
                } elseif ($action === "report") {
                    $url = Url::to(['form/report', 'id' => $model->id]);
                    return $url;
                } elseif ($action === "analytics") {
                    $url = Url::to(['form/analytics', 'id' => $model->id]);
                    return $url;
                } elseif ($action === "reset_stats") {
                    $url = Url::to(['form/reset-stats', 'id' => $model->id]);
                    return $url;
                } elseif ($action === "delete") {
                    $url = Url::to(['form/delete', 'id' => $model->id]);
                    return $url;
                }
                return '';
            },
            'visibleButtons' => [
                //update button
                'update' => function ($model, $key, $index) {
                    return Yii::$app->user->can('updateForms', ['model' => $model]);
                },
                //settings button
                'settings' => function ($model, $key, $index) {
                    return Yii::$app->user->can('configureForms', ['model' => $model]);
                },
                'rules' => function ($model, $key, $index) {
                    return Yii::$app->user->can('configureForms', ['model' => $model]);
                },
                'view' => function ($model, $key, $index) {
                    return Yii::$app->user->can('viewForms', ['model' => $model]);
                },
                'copy' => function ($model, $key, $index) {
                    return Yii::$app->user->can('copyForms', ['model' => $model]);
                },
                'share' => function ($model, $key, $index) {
                    return Yii::$app->user->can('publishForms', ['model' => $model]);
                },
                'submissions' => function ($model, $key, $index) {
                    return Yii::$app->user->can('viewFormSubmissions', ['model' => $model]);
                },
                'addons' => function ($model, $key, $index) {
                    return Yii::$app->user->can('viewAddons', ['model' => $model]);
                },
                'report' => function ($model, $key, $index) {
                    return Yii::$app->user->can('accessFormReports', ['model' => $model]);
                },
                'analytics' => function ($model, $key, $index) {
                    return Yii::$app->user->can('accessFormStats', ['model' => $model]);
                },
                'reset_stats' => function ($model, $key, $index) {
                    return Yii::$app->user->can('resetFormStats', ['model' => $model]);
                },
                'delete' => function ($model, $key, $index) {
                    return Yii::$app->user->can('deleteForms', ['model' => $model]);
                },
            ],
            'template' => '{update} {settings} {rules} {view} {copy} {share} {submissions} {addons} {report} {analytics} {reset_stats} {delete}',
        ],
    ];

?>

<div class="form-index">
    <script>
        function folders() {
            return {
                edit: null,
                folderName: '',
                folders: [],
                // endpoints
                enablePrettyUrl: options.enablePrettyUrl,
                folderEndPoint: options.folderEndPoint,
                createFolderEndPoint: options.createFolderEndPoint,
                updateFolderEndPoint: options.updateFolderEndPoint,
                deleteFolderEndPoint: options.deleteFolderEndPoint,
                // methods
                init() {
                    var that = this;

                    fetch(that.folderEndPoint)
                        .then(function (response) {
                            return response.json();
                        })
                        .then(function (response) {
                            that.folders = response.map(function (item) {
                                return item;
                            });
                        });
                },
                addFolder() {
                    if (this.folderName.trim() !== "") {
                        var that = this;
                        var endPoint = options.enablePrettyUrl ? options.folderEndPoint : options.createFolderEndPoint;
                        fetch(endPoint, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                name: that.folderName
                            })
                        })
                            .then(function (response) {
                                return response.json();
                            })
                            .then(function (data) {
                                that.folders.push({
                                    id: data.id,
                                    name: data.name,
                                    count: 0
                                });
                            })
                            .catch(function (error) {
                                console.error('Error:', error);
                            });
                        that.folderName = "";
                    }
                },
                editFolder(folder, idx) {
                    this.edit = this.edit !== folder.id ? folder.id : null;
                },
                saveFolder(folder, idx) {
                    this.edit = null;
                    if (typeof folder.name !== 'undefined' && folder.name.trim() !== '') {
                        var endPoint = options.enablePrettyUrl ? options.folderEndPoint + '/' + folder.id : options.updateFolderEndPoint + "&id=" + folder.id;
                        fetch(endPoint, {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                name: folder.name
                            })
                        })
                            .then(function (response) {
                                return response.json();
                            })
                            .catch(function (error) {
                                console.error('Error:', error);
                            });
                    }
                },
                deleteFolder(folder, idx) {
                    var that = this;
                    BootstrapDialog.show({
                        type: BootstrapDialog.TYPE_WARNING,
                        title: "<?= Yii::t('app', 'Confirmation') ?>",
                        message: "<?= Yii::t('app', 'Are you sure you want to delete this folder? Forms will moved to the default page.') ?>",
                        buttons: [
                            {
                                label: '<span class="glyphicon glyphicon-ban-circle"></span> <?= Yii::t('app', 'Cancel') ?>',
                                cssClass: 'btn-default',
                                action: function(dialogItself){
                                    dialogItself.close();
                                }
                            },
                            {
                                label: '<span class="glyphicon glyphicon-ok"></span> <?= Yii::t('app', 'Ok') ?>',
                                cssClass: 'btn-warning',
                                action: function(dialogItself){
                                    that.folders.splice(idx, 1);
                                    dialogItself.close();
                                    var endPoint = options.enablePrettyUrl ? options.folderEndPoint + '/' + folder.id : options.deleteFolderEndPoint + "&id=" + folder.id;
                                    fetch(endPoint, {
                                        method: 'DELETE',
                                        headers: { 'Content-Type': 'application/json' },
                                        body: JSON.stringify({
                                            name: folder.name
                                        })
                                    })
                                        .then(function (response) {
                                            return response.json();
                                        })
                                        .catch(function (error) {
                                            console.error('Error:', error);
                                        });
                                }
                            }]
                    });
                }
            }
        }
    </script>
    <div class="row">
        <div class="col-md-12">
            <?= GridView::widget([
                'id' => 'form-grid',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => $gridColumns,
                'resizableColumns' => false,
                'pjax' => false,
                'export' => false,
                'responsive' => true,
                'responsiveWrap' => false,
                'bordered' => false,
                'striped' => true,
                'containerOptions' => [
                    'class' => $showFilters ? 'table-with-filters' : '',
                ],
                'panelTemplate' => Html::tag('div', '{panelHeading}{panelBefore}{items}{panelFooter}', ['class' => 'panel {type}']),
                'panel' => [
                    'type' => GridView::TYPE_INFO,
                    'heading' => Yii::t('app', 'Forms') .' <small class="panel-subtitle hidden-xs">'.
                        Yii::t('app', 'Build any type of online form').'</small>',
                    'before'=>
                        ActionBar::widget([
                            'grid' => 'form-grid',
                            'options' => [
                                'x-data' => "folders()",
                            ],
                            'templates' => Yii::$app->user->can('viewBulkActionsInForms') ? [
                                '{create}' => ['class' => 'col-xs-8 col-sm-6'],
                                '{filters}' => ['class' => 'col-xs-4 col-sm-3 col-lg-4'],
                                '{bulk-actions}' => ['class' => 'col-sm-3 col-lg-2 hidden-xs'],
                            ] : [
                                '{create}' => ['class' => 'col-xs-8 col-sm-6'],
                                '{filters}' => ['class' => 'col-xs-4 col-sm-6'],
                            ],
                            'elements' => [
                                'create' => $this->render('_createFormButton', [
                                    'folderName' => $folderName,
                                    'templateItems' => $templateItems,
                                ]),
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
                                        'containerOptions' => ['style' => 'margin-top: 6px; text-align: right'],
                                    ]
                                ),
                            ],
                            'bulkActionsItems' => $bulkActionsItems,
                            'bulkActionsOptions' => [
                                'options' => $bulkActionsOptions,
                                'class' => 'form-control',
                            ],

                            'class' => 'form-control',
                        ]),
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
                    <div class="kv-panel-pager">
                        {pageSize}
                        {pager}
                    </div>
                ',
                'toolbar' => false
            ]); ?>
        </div>
    </div>
    <div class="modal fade" id="copy-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="copy-form" method="post" action="<?= Url::to(['form/copy']) ?>">
                    <div class="modal-header" style="background-color: #6e8292; color: #ffffff;">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">
                            <?= Yii::t('app', 'Copy Form') ?>
                        </h4>
                    </div>
                    <div class="modal-body">
                            <input type="hidden" name="copy-form-id" id="copy-form-id">
                            <div class="row">
                                <div class="col-xs-12 col-sm-8">
                                    <div class="form-group">
                                        <label for="copy-form-name" class="control-label">
                                            <?= Yii::t('app', 'Name') ?>
                                        </label>
                                        <input type="text" class="form-control"
                                               id="copy-form-name" name="copy-form-name"
                                               placeholder="<?= Yii::t('app', 'Form Name') ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="form-group">
                                        <label><?= Yii::t('app', 'Include') ?></label>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="copy-form-options[]" value="form" checked>
                                                <?= Yii::t('app', 'Form Settings') ?>
                                            </label>
                                        </div>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="copy-form-options[]" value="confirmation" checked>
                                                <?= Yii::t('app', 'Confirmation Settings') ?>
                                            </label>
                                        </div>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="copy-form-options[]" value="notification" checked>
                                                <?= Yii::t('app', 'Notification Settings') ?>
                                            </label>
                                        </div>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="copy-form-options[]" value="ui" checked>
                                                <?= Yii::t('app', 'UI Settings') ?>
                                            </label>
                                        </div>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="copy-form-options[]" value="rules" checked>
                                                <?= Yii::t('app', 'Conditional Rules') ?>
                                            </label>
                                        </div>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="copy-form-options[]" value="report" checked>
                                                <?= Yii::t('app', 'Report') ?>
                                            </label>
                                        </div>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="copy-form-options[]" value="add-ons" checked>
                                                <?= Yii::t('app', 'Add-On Settings') ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <?= Yii::t('app', 'Close') ?>
                        </button>
                        <button type="submit" id="copy-form" class="btn btn-primary">
                            <?= Yii::t('app', 'Copy') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php

$copy = Yii::t('app', 'Copy');
$copyUrl = Url::to(['/ajax/copy-form']);

$js = <<<SCRIPT

$(function () {
    // Tooltips
    $("[data-toggle='tooltip']").tooltip();
    
    // Modal
    $('#copy-modal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var formID = button.data('form-id');
        var formName = button.data('form-name');
        var modal = $(this)
        modal.find('#copy-form-id').val(formID)
        modal.find('#copy-form-name').val(formName)
    });

    // Copy Form
    $("#copy-form").submit(function(){
        var formData = $("#copy-form").serialize();
        $.ajax({
            type: "POST",
            url: "{$copyUrl}",
            data: formData,
            dataType: "json",
            encode: true,
        }).done(function (data) {
            location.reload();
        });
        event.preventDefault();
    });
});

SCRIPT;
// Register tooltip/popover initialization javascript
$this->registerJs($js);
$this->registerJsFile("@web/themes/classic/assets/js/libs/alpine.min.js", ['position' => View::POS_HEAD, 'defer' => true]);
