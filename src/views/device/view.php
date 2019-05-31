<?php /** @noinspection PhpUnhandledExceptionInspection */

use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Device */

$this->title = "{$model->productName}：{$model->device_name}";
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);
?>
<div class="device-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id:code',
            'apply_id:code',
            'applyTitle',
            'productName',
            'productKey:code',
            'serial_no:code',
            'device_name:code',
            'device_secret:code',
            'stateName',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

</div>
