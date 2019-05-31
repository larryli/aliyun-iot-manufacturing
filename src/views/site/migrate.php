<?php

use yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $content string */

$this->title = '执行数据库迁移';
?>
<div class="site-migrate">

    <h1><?= Html::encode($this->title) ?></h1>
    <pre><?= Html::encode($content) ?></pre>
    <div class="col-lg-offset-2 col-lg-10">
        <?= Html::a('跳转首页', Yii::$app->homeUrl, ['class' => 'btn btn-success btn-lg']) ?>
    </div>
</div>
