<?php /** @noinspection PhpUnhandledExceptionInspection */

use app\Asset;
use app\widgets\Alert;
use app\widgets\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

/* @var $this yii\web\View */
/* @var $content string */
/* @var $config app\Config */

$status = '';
$config = Yii::$app->get('config');
if (!$config->empty()) {
    $status = Html::tag('p', app\models\Device::statusHtml(), ['class' => 'navbar-text']);
}

Asset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-default',
        ],
        'innerContainerOptions' => [
            'class' => 'container-fluid',
        ],
    ]);
    echo $status;
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
            ['label' => '设备列表', 'url' => ['/device/index'], 'icon' => 'scale'],
            ['label' => '量产批次', 'url' => ['/apply/index'], 'icon' => 'tasks'],
        ],
    ]);
    NavBar::end();
    ?>

    <div class="container-fluid">
        <?= Breadcrumbs::widget([
            'homeLink' => ['label' => '设备列表', 'url' => Yii::$app->homeUrl],
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
