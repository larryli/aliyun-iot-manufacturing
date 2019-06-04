<?php /** @noinspection PhpUnhandledExceptionInspection */

use app\Html;
use app\models\Device;
use app\widgets\ActionColumn;
use yii\grid\GridView;
use yii\grid\SerialColumn;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Apply */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $model->title . '批次';
$this->params['breadcrumbs'][] = ['label' => '量产批次', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="apply-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if ($model->getDevices()->new()->exists()): ?>
            <?= Html::a('激活批次', ['active', 'id' => $model->id], [
                'class' => 'btn btn-warning',
                'data-confirm' => "确定要激活此批次的设备开始量产么？\n此操作同时会将其他批次的未量产设备取消等待量产状态。",
                'data-method' => 'post',
                'icon' => 'ok',
            ]) ?>
        <?php endif; ?>
        <?php if (!$model->getDevices()->exists() || $model->getDevices()->unused()->exists()): ?>
            <?= Html::a('删除批次', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data-confirm' => '确定要删除此批次么？',
                'data-method' => 'post',
                'icon' => 'trash',
            ]) ?>
        <?php endif; ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id:code',
            'title:text:批次',
            'description:ntext',
            'product_name:text:产品',
            'product_key:code',
            'start_serial_no:code',
            'created_at:datetime',
        ],
    ]) ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => '第 <b>{begin, number} - {end, number}</b> 个，共 <b>{totalCount, number}</b> 个设备。其中 ' . Device::summary($model->getDevices()) . '。',
        'columns' => [
            ['class' => SerialColumn::class],

            'id:code',
            'serial_no:code',
            'device_name:code',
            [
                'attribute' => 'state',
                'value' => 'stateName',
            ],
            'created_at:datetime',
            'updated_at:datetime',

            [
                'class' => ActionColumn::class,
                'controller' => 'device',
                'template' => '{view}',
            ],
        ],
    ]); ?>
</div>
