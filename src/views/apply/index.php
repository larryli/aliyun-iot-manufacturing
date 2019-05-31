<?php /** @noinspection PhpUnhandledExceptionInspection */

use app\models\Apply;
use yii\bootstrap\Html;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\grid\SerialColumn;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ApplySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '量产批次';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="apply-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('批量创建设备', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'summary' => '第 <b>{begin, number} - {end, number}</b> 个，共 <b>{totalCount, number}</b> 个批次。',
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
            'title:text:批次',
            'product_name:text:产品',
            'product_key:code',
            'start_serial_no:code',
            'count:integer:设备总数',
            'countNewDevices',
            'countReadyDevices',
            'countSuccessDevices',
            'countDoneDevices',
            'created_at:datetime',

            [
                'class' => ActionColumn::class,
                'template' => '{view} {active} {delete}',
                'buttons' => [
                    'active' => function ($url, $model, $key) {
                        return Html::a(Html::icon('ok'), $url, [
                            'title' => '激活',
                            'aria-label' => '激活',
                            'data-pjax' => '0',
                            'data-confirm' => "确定要激活此批次的设备开始量产么？\n此操作同时会将其他批次的未量产设备取消等待量产状态。",
                            'data-method' => 'post',
                        ]);
                    },
                ],
                'visibleButtons' => [
                    'active' => function ($model, $key, $index) {
                        /** @var $model Apply */
                        return $model->getDevices()->new()->exists();
                    },
                    'delete' => function ($model, $key, $index) {
                        /** @var $model Apply */
                        $query = $model->getDevices();
                        return !$query->exists() || $query->unused()->exists();
                    },
                ],
            ],
        ],
    ]); ?>
</div>
