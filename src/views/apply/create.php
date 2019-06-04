<?php

use app\Html;
use app\models\Product;
use app\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Apply */

$this->title = '批量创建设备';
$this->params['breadcrumbs'][] = ['label' => '量产批次', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="apply-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="apply-form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>
        <?= $form->field($model, 'product_key')->dropDownList(Product::texts())->label('产品') ?>
        <?= $form->field($model, 'start_serial_no') ?>
        <?= $form->field($model, 'count')->textInput(['type' => 'number', 'min' => 1, 'max' => 1000, 'value' => $model->count ?? 1]) ?>

        <div class="form-group">
            <div class="col-lg-offset-2 col-lg-10">
                <?= Html::submitButton('批量创建', ['class' => 'btn btn-primary', 'icon' => 'plus']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

</div>
