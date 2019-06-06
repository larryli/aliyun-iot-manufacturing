<?php

use app\Html;
use app\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\forms\EspNvsForm */
/* @var $form ActiveForm */

$this->title = '导出 ESP NVS 量产数据';
$this->params['breadcrumbs'][] = $this->title;

$id = Html::getInputId($model, 'nvsFileType');
$js = <<< EOF
yii.init();var s=jQuery('.nvsFileSize');
jQuery('#{$id}').change(o=>$(o.target).val()=='csv'?s.hide():s.show());
EOF;
$this->registerJs($js);
?>
<div class="device-export">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="export-form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'productKey')->dropDownList($model->products) ?>
        <?= $form->field($model, 'nvsFilename')->radioList($model::$nvsFilenames) ?>
        <?= $form->field($model, 'nvsFileType')->radioList($model::$nvsFileTypes) ?>
        <div class="nvsFileSize"<?= $model->nvsFileType == 'csv' ? ' style="display:none"' : '' ?>>
            <?= $form->field($model, 'nvsFileSize')
                ->textInput(['type' => 'number', 'min' => 12288, 'max' => 65536, 'step' => 4096])
                ->hint('必须为 4096 的倍数，最小值 12288(0x3000)，最大值 65536(0x10000)') ?>
        </div>
        <?= $form->field($model, 'namespace') ?>
        <?= $form->field($model, 'serialNoKeyName')->hint('可选，为空时不导出此字段。') ?>
        <?= $form->field($model, 'productKeyKeyName') ?>
        <?= $form->field($model, 'deviceNameKeyName') ?>
        <?= $form->field($model, 'deviceSecretKeyName') ?>

        <div class="form-group">
            <div class="col-lg-offset-2 col-lg-10">
                <?= Html::submitButton('导出量产数据文件', ['class' => 'btn btn-primary', 'icon' => 'download']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>

    </div>
</div>
