<?php

namespace app\controllers;

use app\aliyun\Exception as AliException;
use app\aliyun\Iot;
use app\filters\NotConfigFilter;
use app\filters\NotExistsDeviceFilter;
use app\models\Apply;
use app\models\ApplySearch;
use app\models\Device;
use app\models\Product;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidValueException;
use yii\data\ActiveDataProvider;
use yii\db\Exception as DbException;
use yii\db\StaleObjectException;
use yii\filters\AjaxFilter;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\mutex\Mutex;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * ApplyController implements the CRUD actions for Apply model.
 */
class ApplyController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'setup' => [
                'class' => NotConfigFilter::class,
            ],
            'device' => [
                'class' => NotExistsDeviceFilter::class,
                'only' => ['index'],
            ],
            'ajax' => [
                'class' => AjaxFilter::class,
                'only' => ['creating', 'deleting', 'waiting'],
            ],
            'json' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
                'only' => ['creating', 'deleting', 'waiting'],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'active' => ['POST'],
                    'creating' => ['POST'],
                    'deleting' => ['POST'],
                    'delete' => ['POST'],
                    'waiting' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Apply models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ApplySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Apply model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $dataProvider = new ActiveDataProvider([
            'query' => $model->getDevices(),
        ]);

        return $this->render('view', [
            'model' => $this->findModel($id),
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param integer $id
     * @return Response
     * @throws ConflictHttpException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function actionActive($id)
    {
        /** @var Mutex $mutex */
        $mutex = Yii::$app->get('mutex');
        if (!$mutex->acquire('DEVICE_REG')) {
            throw new ConflictHttpException('发生冲突，当前正在量产。');
        }
        Device::getDb()->transaction(function () use ($id) {
            Device::updateAll([
                'state' => Device::STATE_NEW,
                'updated_at' => time() - 1, // fix order
            ], ['and', ['state' => Device::STATE_READY], ['not', ['apply_id' => $id]]]);
            Device::updateAll([
                'state' => Device::STATE_READY,
                'updated_at' => time(),
            ], ['state' => Device::STATE_NEW, 'apply_id' => $id]);
        });
        return $this->redirect(['index']);
    }

    /**
     * Creates a new Apply model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws Throwable
     */
    public function actionCreate()
    {
        $model = new Apply();

        if ($model->load(Yii::$app->request->post()) && $model->insert()) {
            return $this->render('creating', [
                'model' => $model,
            ]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * @param integer $id
     * @param integer $page
     * @return integer
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function actionCreating($id, $page)
    {
        if (defined('TEST_AJAX')) {
            return static::percent('TEST_AJAX_CREATING');
        }
        $model = $this->findModel($id);
        /** @var Iot $iot */
        $iot = Yii::$app->get('iot');
        /** @var Mutex $mutex */
        $mutex = Yii::$app->get('mutex');
        do {
            $acquire = $mutex->acquire(static::class . $id, 1);
        } while (!$acquire);
        try {
            $results = $iot->queryPageByApplyId($model->id, $page);
            $rows = [];
            foreach ($results['ApplyDeviceList']['ApplyDeviceInfo'] as $result) {
                $serialNo = $model->getSerialNo();
                if ($serialNo === false) {
                    break;
                }
                $device = new Device([
                    'serial_no' => $serialNo,
                    'apply_id' => $model->id,
                    'device_name' => $result['DeviceName'],
                    'device_secret' => $result['DeviceSecret'],
                    'state' => Device::STATE_NEW,
                    'created_at' => time(),
                    'updated_at' => time(),
                ]);
                $rows[] = $device->attributes;
            }
            try {
                if (!empty($device) && !empty($rows)) {
                    Yii::$app->db->createCommand()->batchInsert(Device::tableName(), $device->attributes(), $rows)->execute();
                }
                if ($results['Page'] >= $results['PageCount']) {
                    Product::clear();
                }
                return intval($results['Page'] * 100 / $results['PageCount']);
            } catch (DbException $e) {
                throw new BadRequestHttpException($e->getMessage(), $e->getCode(), $e);
            }
        } catch (InvalidValueException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e->getCode(), $e);
        } catch (AliException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Deletes an existing Apply model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->getDevices()->unused()->exists()) {
            // 存在未量产的设备转入 ajax 逐一删除
            return $this->render('deleting', [
                'model' => $model,
            ]);
        }
        if ($model->getDevices()->exists()) {
            Yii::$app->session->setFlash('error', '当前量产批次还存在设备，不能删除。');
            return $this->redirect(['view', 'id' => $id]);
        }
        $model->delete();
        return $this->redirect(['index']);
    }

    /**
     * @param integer $id
     * @return bool
     * @throws ConflictHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function actionDeleting($id)
    {
        if (defined('TEST_AJAX')) {
            return static::percent('TEST_AJAX_DELETING');
        }
        $model = $this->findModel($id);
        if (empty($model->firstUnusedDevice)) {
            throw new NotFoundHttpException('此量产批次不存在未量产的设备。请结束操作。');
        }
        /** @var Mutex $mutex */
        $mutex = Yii::$app->get('mutex');
        if (!$mutex->acquire('DEVICE_REG')) {
            throw new ConflictHttpException('发生冲突，当前正在量产。请停止设备量产后再重新操作。');
        }
        $key = static::class . 'deleting' . $id;
        $total = Yii::$app->cache->getOrSet($key, function () use ($model) {
            return $model->getDevices()->unused()->count();
        }, 600);
        $model->firstUnusedDevice->delete();
        $exists = $model->getDevices()->unused()->count();
        if ($exists > 0) {
            return intval(($total - $exists) * 100 / $total);
        }
        Yii::$app->cache->delete($key);
        return 100;
    }

    /**
     * @param integer $id
     * @return bool
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function actionWaiting($id)
    {
        if (defined('TEST_AJAX')) {
            return true;
        }
        $model = $this->findModel($id);
        /** @var Iot $iot */
        $iot = Yii::$app->get('iot');
        try {
            $status = $iot->queryBatchRegisterDeviceStatus($model->product_key, $model->id);
            if ($status['Status'] == 'CREATE_SUCCESS') {
                return true; // 开始创建
            }
        } catch (InvalidValueException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e->getCode(), $e);
        } catch (AliException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e->getCode(), $e);
        }
        return false; // 继续等待
    }

    /**
     * Finds the Apply model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Apply the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Apply::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('没有找到对应的量产批次。');
    }

    /**
     * @param string $key
     * @return int
     */
    protected static function percent($key)
    {
        $n = Yii::$app->cache->get($key);
        if ($n === false) {
            $n = 1;
        }
        if ($n < 10) {
            Yii::$app->cache->set($key, $n + 1, 60);
        } else {
            Yii::$app->cache->delete($key);
        }
        return intval($n * 100 / 10);
    }
}
