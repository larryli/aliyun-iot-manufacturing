<?php

namespace app\models;

use app\aliyun\Exception;
use app\aliyun\Iot;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\bootstrap\Html;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * This is the model class for table "{{%device}}".
 *
 * @property int $id
 * @property string $serial_no
 * @property int $apply_id
 * @property string $applyTitle
 * @property string $device_name
 * @property string $device_secret
 * @property string $serialNo
 * @property string $productName
 * @property string $productKey
 * @property string $deviceName
 * @property string $deviceSecret
 * @property int $state
 * @property int $created_at
 * @property int $updated_at
 * @property string $stateName
 */
class Device extends ActiveRecord
{
    const STATE_NEW = 0;
    const STATE_READY = 1;
    const STATE_SUCCESS = 2;
    const STATE_DONE = 3;
    public static $states = [
        self::STATE_NEW => '新设备',
        self::STATE_READY => '等待量产',
        self::STATE_SUCCESS => '量产完成',
        self::STATE_DONE => '已同步数据',
    ];
    /**
     * @var integer 用于接收 count(*) 结果
     */
    public $deviceCount;
    /**
     * @var string
     */
    protected $_productKey = false;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%device}}';
    }

    /**
     * {@inheritdoc}
     */
    public static function find()
    {
        return new DeviceQuery(get_called_class());
    }

    /**
     * @return bool Has device
     */
    public static function exists()
    {
        return static::find()->exists();
    }

    /**
     * @return bool Has success device
     */
    public static function existsSuccess()
    {
        return static::find()->success()->exists();
    }

    /**
     * @return string
     */
    public static function statusHtml()
    {
        $ready = static::find()->ready()->count();
        $success = static::find()->success()->count();
        $done = static::find()->done()->count();
        $new = static::find()->new()->count();
        if ($ready > 0) {
            $icon = Html::icon('play');
            $class = 'primary';
            $text = "<b>{$ready}</b> 个设备正在等待量产。";
            $strings = [];
            if ($success > 0) {
                $strings[] = "已量产完成 <b>{$success}</b> 个设备";
            }
            if ($done > 0) {
                $strings[] = "已同步完成 <b>{$done}</b> 个设备数据";
            }
            if ($new > 0) {
                $strings[] = "还有 <b>{$new}</b> 个设备可激活继续量产";
            }
            $text .= implode('，', $strings) . '。';
        } elseif ($new > 0) {
            $icon = Html::icon('pause');
            if ($success > 0) {
                $class = 'success';
                $text = "已量产完成 <b>{$success}</b> 个设备。" . (empty($done) ? '' : "已同步完成 <b>{$done}</b> 个设备数据，") . "还有 <b>{$new}</b> 个设备可激活继续量产。";
            } elseif ($done > 0) {
                $text = "已同步完成 <b>{$done}</b> 个设备数据，还有 <b>{$new}</b> 个设备可激活继续量产。";
            } else {
                $class = 'info';
                $text = "<b>{$new}</b> 个设备可激活开始量产。";
            }
        } elseif ($success > 0 || $done > 0) {
            $icon = Html::icon('stop');
            if ($success == 0) {
                $text = "已同步完成全部 <b>{$done}</b> 个设备数据。";
            } else {
                $class = 'success';
                $text = "已量产完成 <b>{$success}</b> 个设备。" . (empty($done) ? '' : "已同步完成 <b>{$done}</b> 个设备数据。");
            }
        } else {
            $icon = Html::icon('eject');
            $class = 'warning';
            $text = '<b>设备不存在。</b>';
        }
        return Html::tag('span', $icon . ' ' . $text, empty($class) ? [] : ['class' => 'text-' . $class]);
    }

    /**
     * @return Device
     * @throws NotFoundHttpException
     */
    public static function reg()
    {
        /** @var Device $model */
        $model = static::find()->ready()->orderBy(['id' => SORT_ASC])->limit(1)->one();
        if (empty($model)) {
            throw new NotFoundHttpException('Not found.');
        }
        $model->state = static::STATE_SUCCESS;
        $model->save(false);
        return $model;
    }

    /**
     * @param DeviceQuery $query
     * @return string
     */
    public static function summary($query)
    {
        $summary = [];
        foreach (static::$states as $state => $name) {
            $q = clone $query;
            if (($count = $q->andOnCondition(['state' => $state])->count()) > 0) {
                $summary[] = "<b>{$count}</b> 个{$name}";
            }
        }
        return implode('，', $summary);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        return ['serialNo', 'productKey', 'deviceName', 'deviceSecret'];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'serial_no' => 'SerialNo',
            'apply_id' => '批次',
            'applyTitle' => '批次名',
            'productName' => '产品',
            'productKey' => 'ProductKey',
            'device_name' => 'DeviceName',
            'device_secret' => 'DeviceSecret',
            'state' => '状态',
            'stateName' => '状态',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * @return bool
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function beforeDelete()
    {
        /** @var Iot $iot */
        $iot = Yii::$app->get('iot');
        $iot->deleteDevice($this->productKey, $this->deviceName);
        return parent::beforeDelete();
    }

    /**
     * @return ActiveQuery
     */
    public function getApply()
    {
        return $this->hasOne(Apply::class, ['id' => 'apply_id']);
    }

    /**
     * @return string
     */
    public function getApplyTitle()
    {
        return ArrayHelper::getValue($this, 'apply.title');
    }

    /**
     * @return string
     */
    public function getDeviceName()
    {
        return $this->device_name;
    }

    /**
     * @return string
     */
    public function getDeviceSecret()
    {
        return $this->device_secret;
    }

    /**
     * @return string
     */
    public function getProductName()
    {
        return ArrayHelper::getValue($this, 'apply.product_name');
    }

    /**
     * @return string
     */
    public function getProductKey()
    {
        if ($this->_productKey === false) {
            $this->_productKey = ArrayHelper::getValue($this, 'apply.product_key');
        }
        return $this->_productKey;
    }

    /**
     * @return string
     */
    public function getSerialNo()
    {
        return $this->serial_no;
    }

    /**
     * @return string
     */
    public function getStateName()
    {
        return ArrayHelper::getValue(static::$states, $this->state);
    }

    /**
     * @param string $key
     */
    public function setProductKey($key)
    {
        $this->_productKey = $key;
    }
}
