<?php

/* @var $this yii\web\View */
/* @var $model app\forms\SetupForm */
/* @var $form app\widgets\ActiveForm */
?>
<div class="panel panel-default product-keys"<?= empty($model->products) ? ' style="display:none"' : '' ?>>
    <div class="panel-heading">
        <h3 class="panel-title">量产产品</h3>
    </div>
    <div class="panel-body">
        <?= $form->field($model, 'productKeys')->checkboxList($model->products) ?>
    </div>
</div>
