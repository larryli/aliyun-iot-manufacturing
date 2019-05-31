<?php

namespace app\aliyun;

use AlibabaCloud\Client\Request\RpcRequest;
use AlibabaCloud\Iot\Iot as AliIot;
use AlibabaCloud\Iot\V20180120\IotApiResolver;
use yii\base\InvalidValueException;

class Iot extends Resolver
{
    /**
     * @return IotApiResolver
     */
    public function resolver()
    {
        return AliIot::v20180120();
    }

    /**
     * @param RpcRequest $rpc
     * @return array
     * @throws Exception
     */
    public function rpc($rpc)
    {
        $result = $this->request($rpc);
        if ($this->value($result, 'Success') === false) {
            $code = $this->value($result, 'Code');
            $message = $this->value($result, 'ErrorMessage');
            throw new Exception($message, $code, "{$code}: {$message}");
        }
        return $result;
    }

    /**
     * @param string $productKey
     * @param int $count
     * @return int
     * @throws Exception
     */
    public function batchRegisterDevice($productKey, $count)
    {
        return $this->result($this->resolver()->batchRegisterDevice()
            ->withProductKey($productKey)
            ->withCount($count), 'Data.ApplyId');
    }

    /**
     * @param string $productKey
     * @param string $deviceName
     * @return mixed
     * @throws Exception
     */
    public function deleteDevice($productKey, $deviceName)
    {
        return $this->result($this->resolver()->deleteDevice()
            ->withProductKey($productKey)
            ->withDeviceName($deviceName));
    }

    /**
     * Return:
     * ```json
     *  {
     *    "PageCount": 1,
     *    "PageSize": 200,
     *    "CurrentPage": 1,
     *    "List": {
     *      "ProductInfo": [{
     *        "DataFormat": 1,
     *        "ProductKey": "a1512345678",
     *        "NodeType": 0,
     *        "ProductName": "Name",
     *        "DeviceCount": 1,
     *        "GmtCreate": 1556447758000
     *      }],
     *    },
     *    "Total": 1
     *  }
     * ```
     *
     * @param int $page
     * @param int $pageSize
     * @param string $type 产品类型，默认为空，返回所有产品
     *  'iothub_senior'：物联网平台高级版
     *  'iothub'：物联网平台基础版
     * @return array
     * @throws Exception
     */
    public function queryProductList($page = 1, $pageSize = 200, $type = '')
    {
        $request = $this->resolver()->queryProductList()
            ->withCurrentPage($page)
            ->withPageSize($pageSize);
        if (!empty($type)) {
            if (!in_array($type, ['iothub_senior', 'iothub'])) {
                throw new InvalidValueException('The type value must be "iothub_senior" or "iothub".');
            }
            $request->withAliyunCommodityCode($type);
        }
        return $this->result($request, 'Data');
    }

    /**
     * Return:
     * ```json
     * {
     *   "Status": "CREATE_SUCCESS"
     *   "ValidList": {
     *     "Name": [
     *       "test1"
     *     ]
     *   },
     * }
     * ```
     *
     * @param string $productKey
     * @param int $applyId
     * @return array
     * @throws Exception
     */
    public function queryBatchRegisterDeviceStatus($productKey, $applyId)
    {
        return $this->result($this->resolver()
            ->queryBatchRegisterDeviceStatus()
            ->withProductKey($productKey)
            ->withApplyId($applyId), 'Data');
    }

    /**
     * Return:
     * ```json
     * {
     *   "PageCount": 1,
     *   "ApplyDeviceList": {
     *     "ApplyDeviceInfo": [{
     *       "DeviceId": "gQG2GJ2y10m6hIk87jFm",
     *       "DeviceName": "test1",
     *       "DeviceSecret": "SkfeXXKrTgp1DbDxYr74mfJ5cnui****",
     *       "IotId": "nadRfljdEndlfadgadfaj****"
     *     }],
     *   },
     *   "Page": 1,
     *   "PageSize": 10,
     *   "Total": 1
     * }
     * ```
     * @param int $applyId
     * @param int $page
     * @param int $pageSize
     * @return mixed
     * @throws Exception
     */
    public function queryPageByApplyId($applyId, $page = 1, $pageSize = 50)
    {
        return $this->result($this->resolver()
            ->queryPageByApplyId()
            ->withApplyId($applyId)
            ->withCurrentPage($page)
            ->withPageSize($pageSize));
    }
}
