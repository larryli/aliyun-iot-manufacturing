<?php

namespace app\filters;

use app\Config;
use Yii;
use yii\base\ActionFilter;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

/**
 * 检测是否配置应用
 */
class NotConfigFilter extends ActionFilter
{
    /**
     * @var Controller
     */
    public $owner;

    /**
     * @inheritdoc
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public function beforeAction($action)
    {
        /** @var Config $config */
        $config = Yii::$app->get('config');
        if ($config->empty()) {
            if (Yii::$app->request->isAjax) {
                throw new BadRequestHttpException('Not config.');
            }
            Yii::$app->session->setFlash('error', '请先配置应用参数。');
            $this->owner->redirect(['site/setup']);
            return false;
        }
        return true;
    }
}
