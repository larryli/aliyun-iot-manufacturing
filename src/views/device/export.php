<?php

use app\Html;
use app\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\forms\ExportForm */
/* @var $form ActiveForm */

$this->title = '导出已量产完成数据';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="device-export">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="export-form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'productKey')->dropDownList($model->products) ?>
        <?= $form->field($model, 'serialNoKeyName') ?>
        <?= $form->field($model, 'productKeyKeyName')->hint('可选，为空时不导出此字段。') ?>
        <?= $form->field($model, 'deviceNameKeyName') ?>
        <?= $form->field($model, 'deviceSecretKeyName')->hint('可选，为空时不导出此字段。') ?>

        <div class="form-group">
            <div class="col-lg-offset-2 col-lg-10">
                <?= Html::submitButton('导出量产数据文件', ['class' => 'btn btn-primary', 'icon' => 'download-alt']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>

    </div>
</div>
