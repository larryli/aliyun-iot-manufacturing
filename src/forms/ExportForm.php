<?php

namespace app\forms;

use app\models\Device;
use app\models\Product;
use Yii;
use yii\base\Model;
use yii\db\Expression;

/**
 * ExportForm
 *
 * @property string $filename
 * @property string[] $products
 */
class ExportForm extends Model
{
    /**
     * @var string
     */
    public $productKey;
    /**
     * @var string
     */
    public $serialNoHeader;
    /**
     * @var string
     */
    public $productKeyHeader;
    /**
     * @var string
     */
    public $deviceNameHeader;
    /**
     * @var string
     */
    public $deviceSecretHeader;
    /**
     * @var string
     */
    private $_filename;
    /**
     * @var string[]
     */
    private $_products;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->serialNoHeader === null) {
            $this->serialNoHeader = 'SerialNo';
        }
        if ($this->productKeyHeader === null) {
            $this->productKeyHeader = '';
        }
        if ($this->deviceNameHeader === null) {
            $this->deviceNameHeader = 'DeviceName';
        }
        if ($this->deviceSecretHeader === null) {
            $this->deviceSecretHeader = '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['productKey', 'serialNoHeader', 'deviceNameHeader'], 'required'],
            ['productKey', 'in', 'range' => array_keys($this->getProducts())],
            [['serialNoHeader', 'productKeyHeader', 'deviceNameHeader', 'deviceSecretHeader'], 'trim'],
            [['serialNoHeader', 'productKeyHeader', 'deviceNameHeader', 'deviceSecretHeader'], 'match',
                'pattern' => '/^[_a-z]+[_a-z0-9]*$/i',
                'message' => '必须是字母与数字以及下划线，并且不能以字母开头。'],
            [['productKeyHeader', 'deviceNameHeader', 'deviceSecretHeader'], 'compare', 'compareAttribute' => 'serialNoHeader', 'operator' => '!='],
            [['deviceNameHeader', 'deviceSecretHeader'], 'compare', 'compareAttribute' => 'productKeyHeader', 'operator' => '!='],
            [['deviceSecretHeader'], 'compare', 'compareAttribute' => 'deviceNameHeader', 'operator' => '!='],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'productKey' => '产品',
            'serialNoHeader' => 'SerialNo 字段名',
            'productKeyHeader' => 'ProductKey 字段名',
            'deviceNameHeader' => 'DeviceName 字段名',
            'deviceSecretHeader' => 'DeviceSecret 字段名',
        ];
    }

    /**
     * @return bool
     */
    public function download()
    {
        if ($this->validate()) {
            $headers = [$this->serialNoHeader];
            if (!empty($this->productKeyHeader)) {
                $headers[] = $this->productKeyHeader;
            }
            $headers[] = $this->deviceNameHeader;
            if (!empty($this->deviceSecretHeader)) {
                $headers[] = $this->deviceSecretHeader;
            }
            $rows = [implode(',', $headers)];
            $applies = [];
            foreach (Device::find()->success()->productKey($this->productKey)->each() as $model) {
                /** @var Device $model */
                $cols = [$model->serialNo];
                if (!empty($this->productKeyHeader)) {
                    $cols[] = $this->productKey;
                }
                $cols[] = $model->deviceName;
                if (!empty($this->deviceSecretHeader)) {
                    $cols[] = $model->deviceSecret;
                }
                $rows[] = implode(',', $cols);
                $applies[] = $model->apply_id;
            }
            $applies = array_unique($applies);
            Device::updateAll(['state' => Device::STATE_DONE], ['apply_id' => $applies, 'state' => Device::STATE_SUCCESS]);
            $this->_filename = $this->productKey . date('-Ymd-His') . '.csv';
            Yii::$app->cache->set(static::class . $this->_filename, implode("\n", $rows), 3600);
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->_filename;
    }

    /**
     * @return string[]
     */
    public function getProducts()
    {
        if ($this->_products === null) {
            $products = Product::texts();
            $this->_products = [];
            foreach (Device::find()->success()->joinWith('apply')
                         ->select(['productKey' => 'apply.product_key'])->distinct()
                         ->addSelect(['deviceCount' => new Expression('COUNT(*)')])->all() as $model) {
                $this->_products[$model->productKey] = (isset($products[$model->productKey]) ?
                        $products[$model->productKey] : "未知产品 {$model->productKey}")
                    . "，可同步量产数量：{$model->deviceCount}";
            }
        }
        return $this->_products;
    }

    /**
     * @param string $filename
     * @return bool
     */
    public static function existsFile($filename)
    {
        return Yii::$app->cache->exists(static::class . $filename);
    }

    /**
     * @param string $filename
     * @return bool
     */
    public static function getFile($filename)
    {
        return Yii::$app->cache->get(static::class . $filename);
    }
}
