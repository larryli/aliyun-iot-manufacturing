<?php

namespace app\controllers;

use app\filters\NotConfigFilter;
use yii\web\Controller;

class DeviceController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'setup' => [
                'class' => NotConfigFilter::class,
            ],
        ];
    }

    /**
     * @return mixed
     */
    public function actionIndex()
    {
        return 'TODO: ~';
    }
}
