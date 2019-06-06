<?php

namespace app\forms;

use app\models\DeviceQuery;
use app\models\Product;
use Yii;
use yii\base\Model;
use yii\db\Expression;

/**
 * EspNvsForm
 *
 * @property string $filename
 * @property string[] $products
 */
abstract class DownloadForm extends Model
{
    /**
     * @var string
     */
    public $productKey;
    /**
     * @var string
     */
    public $serialNoKeyName;
    /**
     * @var string
     */
    public $productKeyKeyName;
    /**
     * @var string
     */
    public $deviceNameKeyName;
    /**
     * @var string
     */
    public $deviceSecretKeyName;
    /**
     * @var string
     */
    private $_filename;
    /**
     * @var string[]
     */
    private $_products;

    /**
     * @return DeviceQuery
     */
    abstract protected function findDevice();

    /**
     * @param string $filename
     * @return string
     */
    abstract protected function generateFile(&$filename);

    /**
     * @return bool
     */
    public function download()
    {
        if ($this->validate()) {
            $content = $this->generateFile($this->_filename);
            if (empty($content)) {
                return false;
            }
            Yii::$app->cache->set(self::class . $this->_filename, $content, 3600);
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
            foreach ($this->findDevice()->joinWith('apply')
                         ->select(['productKey' => 'apply.product_key'])->distinct()
                         ->addSelect(['deviceCount' => new Expression('COUNT(*)')])->all() as $model) {
                $this->_products[$model->productKey] = (isset($products[$model->productKey]) ?
                        $products[$model->productKey] : "未知产品 {$model->productKey}")
                    . "，当前可量产数量：{$model->deviceCount}";
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
        return Yii::$app->cache->exists(self::class . $filename);
    }

    /**
     * @param string $filename
     * @return bool
     */
    public static function getFile($filename)
    {
        return Yii::$app->cache->get(self::class . $filename);
    }
}
