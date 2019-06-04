<?php

namespace app;

use yii\bootstrap\BootstrapAsset;
use yii\web\AssetBundle;
use yii\web\YiiAsset;

/**
 * Asset
 */
class Asset extends AssetBundle
{
    /**
     * @internal
     */
    public $css = [
        'site.css',
    ];
    /**
     * @internal
     */
    public $depends = [
        YiiAsset::class,
        BootstrapAsset::class,
    ];
}