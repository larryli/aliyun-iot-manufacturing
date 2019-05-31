<?php

namespace app\filters;

use app\models\Device;
use Yii;
use yii\base\ActionFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

class NotExistsDeviceFilter extends ActionFilter
{
    /**
     * @var Controller
     */
    public $owner;

    /**
     * @inheritdoc
     * @throws BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (!Device::exists()) {
            if (Yii::$app->request->isAjax) {
                throw new BadRequestHttpException('Not exists device.');
            }
            Yii::$app->session->setFlash('info', '设备不存在，请先批量创建设备。');
            $this->owner->redirect(['apply/create']);
            return false;
        }
        return true;
    }
}
