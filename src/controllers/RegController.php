<?php

namespace app\controllers;

use app\models\Device;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\ContentNegotiator;
use yii\mutex\Mutex;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class RegController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    /**
     * @return Device
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function actionIndex()
    {
        /** @var Mutex $mutex */
        $mutex = Yii::$app->get('mutex');
        do {
            $acquire = $mutex->acquire('DEVICE_REG', 1);
        } while (!$acquire);
        return Device::reg(); // auto release
    }
}
