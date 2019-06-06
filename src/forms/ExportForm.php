<?php

namespace app\forms;

use app\models\Device;
use app\models\DeviceQuery;

/**
 * ExportForm
 */
class ExportForm extends DownloadForm
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->serialNoKeyName === null) {
            $this->serialNoKeyName = 'SerialNo';
        }
        if ($this->productKeyKeyName === null) {
            $this->productKeyKeyName = '';
        }
        if ($this->deviceNameKeyName === null) {
            $this->deviceNameKeyName = 'DeviceName';
        }
        if ($this->deviceSecretKeyName === null) {
            $this->deviceSecretKeyName = '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['productKey', 'serialNoKeyName', 'deviceNameKeyName'], 'required'],
            [['productKey', 'serialNoKeyName', 'productKeyKeyName', 'deviceNameKeyName', 'deviceSecretKeyName'], 'trim'],
            ['productKey', 'in', 'range' => array_keys($this->products)],
            [['serialNoKeyName', 'productKeyKeyName', 'deviceNameKeyName', 'deviceSecretKeyName'], 'match',
                'pattern' => '/^[_a-z]+[_a-z0-9]*$/i',
                'message' => '必须是字母与数字以及下划线，并且不能以字母开头。'],
            [['productKeyKeyName', 'deviceNameKeyName', 'deviceSecretKeyName'], 'compare', 'compareAttribute' => 'serialNoKeyName', 'operator' => '!='],
            [['deviceNameKeyName', 'deviceSecretKeyName'], 'compare', 'compareAttribute' => 'productKeyKeyName', 'operator' => '!='],
            [['deviceSecretKeyName'], 'compare', 'compareAttribute' => 'deviceNameKeyName', 'operator' => '!='],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'productKey' => '产品',
            'serialNoKeyName' => 'SerialNo 字段名',
            'productKeyKeyName' => 'ProductKey 字段名',
            'deviceNameKeyName' => 'DeviceName 字段名',
            'deviceSecretKeyName' => 'DeviceSecret 字段名',
        ];
    }

    /**
     * @return DeviceQuery
     */
    protected function findDevice()
    {
        return Device::find()->success();
    }

    /**
     * @param string $filename
     * @return string
     */
    protected function generateFile(&$filename)
    {
        $KeyNames = [$this->serialNoKeyName];
        if (!empty($this->productKeyKeyName)) {
            $KeyNames[] = $this->productKeyKeyName;
        }
        $KeyNames[] = $this->deviceNameKeyName;
        if (!empty($this->deviceSecretKeyName)) {
            $KeyNames[] = $this->deviceSecretKeyName;
        }
        $rows = [implode(',', $KeyNames)];
        $applies = [];
        foreach (Device::find()->success()->productKey($this->productKey)->each() as $model) {
            /** @var Device $model */
            $cols = [$model->serialNo];
            if (!empty($this->productKeyKeyName)) {
                $cols[] = $this->productKey;
            }
            $cols[] = $model->deviceName;
            if (!empty($this->deviceSecretKeyName)) {
                $cols[] = $model->deviceSecret;
            }
            $rows[] = implode(',', $cols);
            $applies[] = $model->apply_id;
        }
        $applies = array_unique($applies);
        Device::updateAll(['state' => Device::STATE_DONE], ['apply_id' => $applies, 'state' => Device::STATE_SUCCESS]);
        $filename = $this->productKey . date('-Ymd-His') . '.csv';
        return implode("\n", $rows);
    }
}
