<?php

namespace app\models;

use app\aliyun\Iot;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Product
 * @property string $text
 */
class Product extends Model
{
    /**
     * @var string
     */
    public $key;
    /**
     * @var string
     */
    public $name;
    /**
     * @var integer
     */
    public $count;
    /**
     * @var string[]
     */
    private static $_names;
    /**
     * @var static[]
     */
    private static $_products;
    /**
     * @var string[]
     */
    private static $_texts;

    /**
     *
     */
    public static function clear()
    {
        Yii::$app->cache->delete(static::class);
    }

    /**
     * @return string[]
     */
    public static function names()
    {
        if (static::$_names === null) {
            static::$_names = ArrayHelper::map(static::products(), 'key', 'name');
        }
        return static::$_names;
    }

    /**
     * @return static[]
     */
    public static function products()
    {
        if (static::$_products === null) {
            static::$_products = Yii::$app->cache->getOrSet(static::class, function () {
                /** @var Iot $iot */
                $iot = Yii::$app->get('iot');
                $products = [];
                $productKeys = ArrayHelper::getValue(Yii::$app->params, 'productKeys');
                foreach ($iot->queryProductList()['List']['ProductInfo'] as $v) {
                    if (empty($productKeys) || in_array($v['ProductKey'], $productKeys)) {
                        $products[] = new static([
                            'key' => $v['ProductKey'],
                            'name' => $v['ProductName'],
                            'count' => $v['DeviceCount'],
                        ]);
                    }
                }
                return $products;
            }, 300);
        }
        return static::$_products;
    }

    /**
     * @return string[]
     */
    public static function texts()
    {
        if (static::$_texts === null) {
            static::$_texts = ArrayHelper::map(static::products(), 'key', 'text');
        }
        return static::$_texts;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return "{$this->name} [{$this->key}] (设备数量：{$this->count})";
    }
}
