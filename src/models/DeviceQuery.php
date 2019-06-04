<?php

namespace app\models;

use yii\db\ActiveQuery;

/**
 * DeviceQuery
 */
class DeviceQuery extends ActiveQuery
{
    /**
     * @return DeviceQuery
     */
    public function new()
    {
        return $this->andOnCondition(['state' => Device::STATE_NEW]);
    }

    /**
     * @return DeviceQuery
     */
    public function ready()
    {
        return $this->andOnCondition(['state' => Device::STATE_READY]);
    }

    /**
     * @return DeviceQuery
     */
    public function unused()
    {
        return $this->andOnCondition(['state' => [Device::STATE_NEW, Device::STATE_READY]]);
    }

    /**
     * @return DeviceQuery
     */
    public function success()
    {
        return $this->andOnCondition(['state' => Device::STATE_SUCCESS]);
    }

    /**
     * @return DeviceQuery
     */
    public function done()
    {
        return $this->andOnCondition(['state' => Device::STATE_DONE]);
    }

    /**
     * @param string|string[] $key
     * @return DeviceQuery
     */
    public function productKey($key)
    {
        return $this->joinWith('apply')->andOnCondition(['apply.product_key' => $key]);
    }

    /**
     * @param string|string[] $no
     * @return DeviceQuery
     */
    public function serialNo($no)
    {
        return $this->andOnCondition(['serial_no' => $no]);
    }
}
