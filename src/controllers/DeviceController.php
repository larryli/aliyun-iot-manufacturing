<?php

namespace app\controllers;

use app\filters\NotConfigFilter;
use app\filters\NotExistsDeviceFilter;
use app\forms\DownloadForm;
use app\forms\EspNvsForm;
use app\forms\ExportForm;
use app\models\Device;
use app\models\DeviceSearch;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\mutex\Mutex;
use yii\web\ConflictHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\RangeNotSatisfiableHttpException;

/**
 * DeviceController implements the CRUD actions for Device model.
 */
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
            'device' => [
                'class' => NotExistsDeviceFilter::class,
            ],
        ];
    }

    /**
     * Lists all Device models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DeviceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param string $filename
     * @return mixed
     * @throws NotFoundHttpException
     * @throws RangeNotSatisfiableHttpException
     */
    public function actionDownload($filename)
    {
        if (DownloadForm::existsFile($filename)) {
            return Yii::$app->response->sendContentAsFile(DownloadForm::getFile($filename), $filename, [
                'mimeType' => FileHelper::getMimeTypeByExtension($filename),
            ]);
        }
        throw new NotFoundHttpException("File {$filename} not exists.");
    }

    /**
     * @return mixed
     */
    public function actionEspNvs()
    {
        if (!Device::existsNew()) {
            return $this->redirect(['index']);
        }

        $model = new EspNvsForm();

        if ($model->load(Yii::$app->request->post()) && $model->download()) {
            Yii::$app->session->setFlash('success',
                '量产数据导出成功。请在一个小时内' . Html::a(
                    '下载数据文件', ['download', 'filename' => $model->filename]) . '。');
            return $this->redirect(['index']);
        }

        return $this->render('esp-nvs', [
            'model' => $model,
        ]);
    }

    /**
     * @return mixed
     * @throws ConflictHttpException
     * @throws InvalidConfigException
     */
    public function actionExport()
    {
        if (!Device::existsSuccess()) {
            return $this->redirect(['index']);
        }

        /** @var Mutex $mutex */
        $mutex = Yii::$app->get('mutex');
        if (!$mutex->acquire('DEVICE_REG')) {
            throw new ConflictHttpException('发生冲突，当前正在量产。');
        }

        $model = new ExportForm();

        if ($model->load(Yii::$app->request->post()) && $model->download()) {
            Yii::$app->session->setFlash('success',
                '量产数据导出成功。请在一个小时内' . Html::a(
                    '下载数据文件', ['download', 'filename' => $model->filename]) . '。');
            return $this->redirect(['index']);
        }

        return $this->render('export', [
            'model' => $model,
        ]);
    }

    /**
     * Displays a single Device model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Finds the Device model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Device the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Device::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('没有找到对应的设备。');
    }
}
