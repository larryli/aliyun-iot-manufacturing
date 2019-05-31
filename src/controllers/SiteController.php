<?php

namespace app\controllers;

use app\Config;
use app\forms\SetupForm;
use app\widgets\SetupActiveForm;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\InvalidRouteException;
use yii\caching\FileCache;
use yii\console\Application;
use yii\console\Exception as ConsoleException;
use yii\log\FileTarget;
use yii\web\Controller;
use yii\web\ErrorAction;
use yii\web\NotAcceptableHttpException;
use yii\web\Response;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => ErrorAction::class,
            ],
        ];
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function actionSetup()
    {
        /** @var Config $config */
        $config = Yii::$app->get('config');
        if (!$config->empty()) {
            return $this->goHome();
        }

        $model = new SetupForm();

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $model->setScenario('ajax');
            if ($model->load(Yii::$app->request->post())) {
                if (!empty($validate = SetupActiveForm::validate($model))) {
                    return $validate;
                }
                return ['productKeys' => $this->renderAjax('_productKeys', [
                    'form' => SetupActiveForm::begin(),
                    'model' => $model,
                ])];
            }
            throw new NotAcceptableHttpException();
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->cache->set('migrate', $this->migrate(), 60);
            return $this->redirect(['migrate']);
        }

        return $this->render('setup', [
            'model' => $model,
        ]);
    }

    /**
     * @return mixed
     */
    public function actionMigrate()
    {
        $content = Yii::$app->cache->get('migrate');
        if (!empty($content)) {
            Yii::$app->cache->delete('migrate');
            return $this->render('migrate', [
                'content' => $content,
            ]);
        }
        return $this->goHome();
    }

    /**
     * @throws InvalidConfigException
     */
    protected function migrate()
    {
        $oldApp = Yii::$app;
        /** @var Config $config */
        $config = Yii::$app->get('config');
        $config->bootstrap($oldApp);
        new Application([
            'id' => 'yii-web-console',
            'basePath' => Yii::getAlias('@app'),
            'runtimePath' => Yii::getAlias('@runtime'),
            'vendorPath' => Yii::getAlias('@vendor'),
            'bootstrap' => ['log'],
            'components' => [
                'db' => $oldApp->db,
                'cache' => [
                    'class' => FileCache::class,
                ],
                'log' => [
                    'targets' => [
                        [
                            'class' => FileTarget::class,
                            'levels' => ['error', 'warning'],
                        ],
                    ],
                ],
            ],
        ]);
        Yii::setAlias('@migrations', '@app/migrations/');
        try {
            ob_start();
            Yii::$app->runAction('migrate/up', ['migrationPath' => '@migrations', 'interactive' => false]);
            return ob_get_flush();
        } catch (InvalidRouteException $e) {
            return $e->getMessage();
        } catch (ConsoleException $e) {
            return $e->getMessage();
        } finally {
            Yii::$app = $oldApp;
        }
    }
}
