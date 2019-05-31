<?php

namespace app\models;

use app\aliyun\Exception as AliException;
use app\aliyun\Iot;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%apply}}".
 *
 * @property int $id
 * @property string $product_key
 * @property string $product_name
 * @property string $title
 * @property string $description
 * @property string $start_serial_no
 * @property int $count
 * @property int $created_at
 * @property Device[] $devices
 * @property Device $unusedDevice
 * @property int $countNewDevices
 * @property int $countReadyDevices
 * @property int $countSuccessDevices
 * @property int $countDoneDevices
 */
class Apply extends ActiveRecord
{
    /**
     * @var string[]
     */
    protected $serialNos = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%apply}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'description', 'product_key', 'start_serial_no', 'count'], 'required'],
            [['description'], 'string'],
            [['product_key', 'start_serial_no', 'title'], 'string', 'max' => 255],
            ['product_key', 'in', 'range' => array_keys(Product::texts())],
            ['start_serial_no', 'match', 'pattern' => '/^[a-z0-9]+\d+$/i'],
            ['count', 'integer', 'min' => 1, 'max' => 1000],
            ['start_serial_no', 'validateSerialNo'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_key' => 'ProductKey',
            'product_name' => '产品名',
            'title' => '批次名',
            'description' => '描述',
            'start_serial_no' => '起始 SerialNo',
            'count' => '数量',
            'created_at' => '创建时间',
            'countNewDevices' => '新设备',
            'countReadyDevices' => '等待量产',
            'countSuccessDevices' => '量产成功',
            'countDoneDevices' => '同步完成',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     *
     */
    public function validateSerialNo()
    {
        if (!$this->hasErrors() && preg_match('/\d+$/', $this->start_serial_no, $arr)) {
            $start = reset($arr);
            $len = strlen($start);
            $prefix = substr($this->start_serial_no, 0, -$len);
            $start = intval($start);
            $end = str_pad($start + $this->count - 1, $len, '0', STR_PAD_LEFT);
            if (strlen($end) != $len) {
                $this->addError('start_serial_no', "起始 SerialNo 无法容纳 {$this->count} 个数据。");
            } else {
                $this->serialNos = [];
                for ($n = 0; $n < $this->count; $n++) {
                    $this->serialNos[] = $prefix . str_pad($start + $n, $len, '0', STR_PAD_LEFT);;
                }
                if (Device::find()->serialNo($this->serialNos)->exists()) {
                    $this->addError('start_serial_no', "生成的 SerialNo 列表已存在。");
                }
            }
        }
    }

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->product_name = Product::names()[$this->product_key];
            if (defined('TEST_AJAX')) {
                return parent::beforeSave($insert); // return
            }
            /** @var Iot $iot */
            $iot = Yii::$app->get('iot');
            try {
                $this->id = $iot->batchRegisterDevice($this->product_key, $this->count);
            } catch (AliException $e) {
                $this->addError('product_key', $e->getErrorMessage());
                return false;
            }
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritDoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            $this->cacheSerialNos();
        }
    }

    /**
     * @return DeviceQuery
     */
    public function getDevices()
    {
        /** @var $query DeviceQuery */
        $query = $this->hasMany(Device::class, ['apply_id' => 'id']);
        return $query;
    }

    /**
     * @return DeviceQuery|ActiveQuery
     */
    public function getUnusedDevice()
    {
        /** @var $query DeviceQuery */
        $query = $this->hasOne(Device::class, ['apply_id' => 'id']);
        return $query->unused();
    }

    /**
     * Must using with mutex
     * @return string
     */
    public function getSerialNo()
    {
        if ($this->serialNos === null) {
            $this->serialNos = Yii::$app->cache->get(static::class . $this->id);
        }
        if (empty($this->serialNos)) {
            return null;
        }
        $serialNo = reset($this->serialNos);
        if (empty($this->serialNos)) {
            Yii::$app->cache->delete(static::class . $this->id);
        } else {
            $this->cacheSerialNos();
        }
        return $serialNo;
    }

    /**
     * @return int
     */
    public function getCountNewDevices()
    {
        return $this->getDevices()->new()->count();
    }

    /**
     * @return int
     */
    public function getCountReadyDevices()
    {
        return $this->getDevices()->ready()->count();
    }

    /**
     * @return int
     */
    public function getCountSuccessDevices()
    {
        return $this->getDevices()->success()->count();
    }

    /**
     * @return int
     */
    public function getCountDoneDevices()
    {
        return $this->getDevices()->done()->count();
    }

    /**
     *
     */
    protected function cacheSerialNos()
    {
        Yii::$app->cache->set(static::class . $this->id, $this->serialNos, 3600);
    }
}
