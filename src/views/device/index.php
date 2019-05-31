<?php /** @noinspection PhpUnhandledExceptionInspection */

use app\models\Device;
use yii\bootstrap\Html;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\grid\SerialColumn;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DeviceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $query app\models\DeviceQuery */

$this->title = '设备列表';

$query = $dataProvider->query;
?>
<div class="device-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('批量创建设备', ['apply/create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('选择批次激活开始量产', ['apply/index'], ['class' => 'btn btn-info']) ?>
        <?php if (Device::existsSuccess()): ?>
            <?= Html::a('导出量产数据', ['export'], ['class' => 'btn btn-success']) ?>
        <?php endif; ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'summary' => '第 <b>{begin, number} - {end, number}</b> 个，共 <b>{totalCount, number}</b> 个设备。其中 ' . Device::summary($query) . '。',
        'columns' => [
            ['class' => SerialColumn::class],

            [
                'attribute' => 'id',
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'id' => null,
                    'style' => 'width: 6em',
                ],
                'format' => 'code',
            ],
            [
                'attribute' => 'apply_id',
                'filterInputOptions' => [
                    'class' => 'form-control',
                    'id' => null,
                    'style' => 'width: 6em',
                ],
                'format' => 'code',
            ],
            'applyTitle',
            'productName',
            'productKey:code',
            'serial_no:code',
            'device_name:code',
            [
                'attribute' => 'state',
                'filter' => Device::$states,
                'value' => 'stateName',
            ],
            'updated_at:datetime',

            [
                'class' => ActionColumn::class,
                'template' => '{view} {apply}',
                'buttons' => [
                    'apply' => function ($url, $model, $key) {
                        return Html::a(Html::icon('tasks'), ['apply/view', 'id' => $model->apply_id], [
                            'title' => '批次',
                            'aria-label' => '批次',
                            'data-pjax' => '0',
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>
</div>
