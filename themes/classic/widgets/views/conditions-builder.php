<?php

use yii\helpers\Html;
use yii\web\JqueryAsset;

/**
 * @var string $id
 * @var string $label
 * @var array $labelOptions
 * @var array $options
 */

$this->registerCssFile('@web/themes/classic/assets/css/rules.builder.css');
$this->registerJs("var options = ".json_encode($options).";", $this::POS_BEGIN, 'conditions-builder-options');
$this->registerJsFile('@web/themes/classic/assets/js/rules.builder.operators.js', ['depends' => JqueryAsset::class]);
$this->registerJsFile('@web/themes/classic/assets/js/rules.builder.conditions.js', ['depends' => JqueryAsset::class]);
$this->registerJsFile('@web/themes/classic/assets/js/widgets/conditions.builder.js', ['depends' => JqueryAsset::class]);

?>
<div class="conditions-builder">
    <?= Html::label($label, $id, $labelOptions) ?>
    <div class="rule-builder">
        <div class="rules-group-container">
            <div id="<?= $id ?>" class="rule-builder-conditions"></div>
        </div>
    </div>
</div>
