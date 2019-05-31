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
    public $file;
    /**
     * @var string[]
     */
    private $_products;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['productKey', 'required'],
            ['productKey', 'in', 'range' => array_keys($this->getProducts())],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ['productKey' => '产品'];
    }

    /**
     * @return bool
     */
    public function download()
    {
        if ($this->validate()) {
            $content = "SerialNo,ProductKey,DeviceName,DeviceSecret\n";
            $applies = [];
            foreach (Device::find()->success()->productKey($this->productKey)->each() as $model) {
                /** @var Device $model */
                $content .= "{$model->serialNo},{$model->productKey},{$model->deviceName},{$model->deviceSecret}\n";
                $applies[] = $model->apply_id;
            }
            $applies = array_unique($applies);
            Device::updateAll(['state' => Device::STATE_DONE], ['apply_id' => $applies, 'state' => Device::STATE_SUCCESS]);
            $this->file = $this->productKey . date('-Ymd-His') . '.csv';
            Yii::$app->cache->set(static::class . $this->file, $content, 3600);
            return true;
        }
        return false;
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
     * @param string $file
     * @return bool
     */
    public static function existsFile($file)
    {
        return Yii::$app->cache->exists(static::class . $file);
    }

    /**
     * @param string $file
     * @return bool
     */
    public static function getFile($file)
    {
        return Yii::$app->cache->get(static::class . $file);
    }
}
