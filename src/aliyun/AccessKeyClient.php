<?php

namespace app\aliyun;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use yii\base\InvalidConfigException;

class AccessKeyClient extends Client
{
    /**
     * @var string
     */
    public $accessKeyId;
    /**
     * @var string
     */
    public $accessKeySecret;

    /**
     * @throws InvalidConfigException
     * @throws ClientException
     */
    protected function initClient()
    {
        if (empty($this->accessKeyId)) {
            throw new InvalidConfigException ('The "accessKeyId" property must be set.');
        }
        if (empty($this->accessKeySecret)) {
            throw new InvalidConfigException ('The "accessKeySecret" property must be set.');
        }
        if (empty($this->regionId)) {
            throw new InvalidConfigException ('The "regionId" property must be set.');
        }
        AlibabaCloud::accessKeyClient($this->accessKeyId, $this->accessKeySecret)
            ->regionId($this->regionId)
            ->name($this->name);
    }
}
