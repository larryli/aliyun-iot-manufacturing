<?php

use app\widgets\SetupActiveForm;
use yii\bootstrap\Html;
use yii\web\YiiAsset;

/* @var $this yii\web\View */
/* @var $model app\forms\SetupForm */

$this->title = '量产服务配置';

$id = Html::getInputId($model, 'dbPrefix');
$js = <<< EOF
yii.init();var s=jQuery('.db-sqlite'),m=jQuery('.db-mysql');
jQuery('#{$id}').change(o=>$(o.target).val()=='sqlite'?s.show()&&m.hide():m.show()&&s.hide());
jQuery('.setup-form>form').on('ajaxComplete',(e,xhr)=>xhr.responseJSON.productKeys!=undefined?
jQuery('.product-keys').replaceWith(xhr.responseJSON.productKeys):jQuery('.product-keys').hide())
EOF;
YiiAsset::register($this);
$this->registerJs($js);
?>
<div class="site-setup">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="setup-form">

        <?php $form = SetupActiveForm::begin(); ?>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">数据库</h3>
            </div>
            <div class="panel-body">
                <?= $form->field($model, 'dbPrefix')->dropDownList($model::$DB_PREFIXES) ?>
                <div class="db-sqlite"<?= $model->dbPrefix == 'sqlite' ? '' : ' style="display:none"' ?>>
                    <?= $form->field($model, 'dbFile') ?>
                </div>
                <div class="db-mysql"<?= $model->dbPrefix == 'mysql' ? '' : ' style="display:none"' ?>>
                    <?= $form->field($model, 'dbHost') ?>
                    <?= $form->field($model, 'dbPort')->textInput(['type' => 'number', 'min' => 1, 'max' => 65535]) ?>
                    <?= $form->field($model, 'dbName') ?>
                    <?= $form->field($model, 'dbUsername') ?>
                    <?= $form->field($model, 'dbPassword')->passwordInput() ?>
                    <?= $form->field($model, 'dbCharset') ?>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">阿里云物联网</h3>
            </div>
            <div class="panel-body">
                <?= $form->field($model, 'iotAccessKeyId', ['enableAjaxValidation' => true]) ?>
                <?= $form->field($model, 'iotAccessKeySecret', ['enableAjaxValidation' => true])->passwordInput() ?>
                <?= $form->field($model, 'iotRegionId', ['enableAjaxValidation' => true]) ?>
            </div>
        </div>
        <?= $this->render('_productKeys', ['form' => $form, 'model' => $model]) ?>

        <div class="form-group">
            <div class="col-lg-offset-2 col-lg-10">
                <?= Html::submitButton('设置', ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
        <?php SetupActiveForm::end(); ?>
    </div>
</div>
