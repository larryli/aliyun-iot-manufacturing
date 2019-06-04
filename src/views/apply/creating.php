<?php /** @noinspection PhpUnhandledExceptionInspection */

use app\Html;
use yii\bootstrap\Progress;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Apply */

$this->title = '正在批量创建设备';
$this->params['breadcrumbs'][] = ['label' => '量产批次', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$waiting = Url::to(['waiting', 'id' => $model->id]);
$creating = Url::to(['creating', 'id' => $model->id]);
$success = Url::to(['view', 'id' => $model->id]);

$js = <<< EOF
yii.init();var f=x=>{if(x.responseJSON!=undefined&&x.responseJSON.message!=undefined){
jQuery('#info').text(x.responseJSON.message).attr('class','alert alert-danger')}},c=p=>{
jQuery.post('{$creating}&page='+p).fail(f).done(v=>{if(v<100){
jQuery('#progress').attr('style','width:'+v+'%').text(v+'%');c(p+1)}else{
jQuery(location).attr('href','{$success}')}})},w=()=>{jQuery.post('{$waiting}').fail(f).done(v=>{if(v){
jQuery('#info').text('正在从阿里云 IoT 批量创建设备，请稍候。');c(1)}else{w()}})};w()
EOF;
$this->registerJs($js);
?>
<div class="apply-creating">

    <h1><?= Html::encode($this->title) ?></h1>

    <div>
        <p id="info" class="alert alert-success">阿里云 IoT 正在批量创建设备，请稍候。</p>

        <?= Progress::widget([
            'barOptions' => ['id' => 'progress', 'class' => 'progress-bar-primary'],
            'options' => ['class' => 'active progress-striped'],
            'percent' => 0,
            'label' => '0%',
        ]) ?>
    </div>
</div>
