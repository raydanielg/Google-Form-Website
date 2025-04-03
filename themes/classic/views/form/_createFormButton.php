<?php

use app\models\Folder;
use yii\helpers\Html;
use yii\bootstrap\Dropdown;
use yii\helpers\Url;
use yii\web\View;

/* @var $templateItems array */
/* @var $folderName Folder */

?>
<?php if (Yii::$app->user->can('createForms')) : ?>
    <div class="btn-group">
        <?= Html::a(
            '<span class="glyphicon glyphicon-plus"></span> ' . Yii::t('app', 'Create Form'),
            ['create'], ['class' => 'btn btn-primary']) .
        '<button type="button" 
                 class="btn btn-primary dropdown-toggle"
                 data-toggle="dropdown" 
                 aria-haspopup="true" 
                 aria-expanded="false">
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
        </button>' .
        Dropdown::widget(['items' => $templateItems])
        ?>
    </div>
<?php endif; ?>
<div id="folders">
    <span data-toggle="tooltip" data-placement="top" title="<?= Yii::t('app', 'Folder Management') ?>">
        <a href="#" class="text folders" data-toggle="modal" data-target="#folders-modal"><i class="glyphicon glyphicon-folder-closed"> </i></a>
    </span>
    <div class="modal fade" id="folders-modal" tabindex="-1" role="dialog" aria-labelledby="folders-modal-title">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #6e8292; color: #ffffff;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="folders-modal-title"><?= Yii::t('app', 'Folder Management') ?></h4>
                </div>
                <div class="modal-body">
                    <p><?= Yii::t('app', 'Folders help you organize and filter forms.') ?></p>
                    <div class="well">
                        <div class="form-inline">
                            <div class="form-group">
                                <label for="folder-name">
                                    <?= Yii::t('app', 'Folder Name') ?>
                                </label>
                                <input x-model="folderName" @keyup.enter="addFolder" type="text" class="form-control input-sm" id="folder-name">
                            </div>
                            <button type="button" class="btn btn-primary btn-sm" @click="addFolder">
                                <?= Yii::t('app', 'Add Folder') ?>
                            </button>
                        </div>
                    </div>
                    <div>
                        <div class="table-responsive">
                            <table class="table table-condensed table-striped">
                                <thead>
                                <tr>
                                    <th>
                                        <?= Yii::t('app', 'Name') ?>
                                        <a href="#" data-toggle="tooltip" title="<?= Yii::t('app', 'Click on each name to edit it.') ?>"><small><i class="glyphicon glyphicon-question-sign"></i></small></a>
                                    </th>
                                    <th style="text-align: right"><?= Yii::t('app', 'Actions') ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <template x-for="(folder, idx) in folders" :key="idx">
                                    <tr>
                                        <td class="folders-column-data">
                                            <span x-show="edit !== folder.id" x-text="folder.name" @click="editFolder(folder, idx)" title="<?= Yii::t('app', 'Click here to edit') ?>"></span>
                                            <input x-show="edit === folder.id" x-model="folder.name" @click.away="saveFolder(folder, idx)" @keyup.enter="saveFolder(folder, idx)" type="text" class="form-control input-sm" />
                                        </td>
                                        <td class="folders-column-actions">
                                            <a @click="deleteFolder(folder, idx)" href="#" class="folders-delete-link" title="<?= Yii::t('app', 'Delete') ?>"><i class="glyphicon glyphicon-bin"></i> Delete</a>
                                        </td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="dropdown folders-dropdown" style="display: inline">
        <a href="#" class="dropdown-toggle text text-muted" type="button" id="folders-dropdown-menu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
            <?= $folderName ?>
            <i class="glyphicon glyphicon-chevron-down" style="font-size: 11px"> </i>
        </a>
        <ul class="dropdown-menu" aria-labelledby="folders-dropdown-menu">
            <li><a href="<?= Url::to(['/form']) ?>"><?= Yii::t('app', 'All Forms') ?></a></li>
            <li><a href="<?= Url::to(['/form', 'folder' => 'shared-with-me']) ?>"><?= Yii::t('app', 'Shared with me') ?></a></li>
            <li><a href="<?= Url::to(['/form', 'folder' => 'none']) ?>"><?= Yii::t('app', 'Uncategorized') ?></a></li>
            <li role="separator" class="divider"></li>
            <li class="dropdown-header"><?= Yii::t('app', 'Folders') ?></li>
            <template x-for="(folder, idx) in folders" :key="idx">
                <li><a x-bind:href="'<?= Url::to(['/form', 'folder' => '']) ?>' + folder.id" x-html="folder.name + '<span>' + folder.count + '</span>'"></a></li>
            </template>
        </ul>
    </div>
</div>
