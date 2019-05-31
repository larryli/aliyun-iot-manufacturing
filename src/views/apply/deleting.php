<?php /** @noinspection PhpUnhandledExceptionInspection */

use yii\bootstrap\Progress;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\YiiAsset;

/* @var $this yii\web\View */
/* @var $model app\models\Apply */

$this->title = '正在批量删除设备';
$this->params['breadcrumbs'][] = ['label' => '量产批次', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$deleting = Url::to(['deleting', 'id' => $model->id]);

$js = <<< EOF
yii.init();var d=()=>{jQuery.post('{$deleting}').fail(x=>{if(x.responseJSON!=undefined&&x.responseJSON.message!=undefined){
jQuery('#info').text(x.responseJSON.message).attr('class','alert alert-danger')}}).done(v=>{if(v<100){
jQuery('#progress').attr('style','width:'+v+'%').text(v+'%');d()}else{jQuery('#done').trigger('click')}})};d()
EOF;
YiiAsset::register($this);
$this->registerJs($js);
?>
<div class="apply-creating">

    <h1><?= Html::encode($this->title) ?></h1>

    <div>
        <p id="info" class="alert alert-success">正在从阿里云 IoT 删除设备，请稍候。</p>

        <?= Progress::widget([
            'barOptions' => ['id' => 'progress', 'class' => 'progress-bar-primary'],
            'options' => ['class' => 'active progress-striped'],
            'percent' => 0,
            'label' => '0%',
        ]) ?>

        <?= Html::a('', ['delete', 'id' => $model->id], ['data-method' => 'post', 'class' => 'hidden', 'id' => 'done']) ?>
    </div>
</div>
