<?php

namespace app\aliyun;

use AlibabaCloud\Client\Exception\AlibabaCloudException;
use AlibabaCloud\Client\Request\RpcRequest;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

abstract class Resolver extends Component
{
    /**
     * @var string|array|AccessKeyClient
     */
    public $client = 'aliyun';

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->client = Instance::ensure($this->client, Client::class);
    }

    abstract public function resolver();

    abstract public function rpc($rpc);

    /**
     * @param RpcRequest $rpc
     * @return mixed
     * @throws Exception
     */
    public function request($rpc)
    {
        try {
            return $rpc->scheme('https')->client($this->client->name)->request()->toArray();
        } catch (AlibabaCloudException $e) {
            throw new Exception($e->getErrorMessage(), $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param RpcRequest $rpc
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    public function result($rpc, $key = '')
    {
        $result = $this->rpc($rpc);
        return empty($key) ? $result : $this->value($result, $key);
    }

    /**
     * @param array $results
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    public function value($results, $key)
    {
        $value = ArrayHelper::getValue($results, $key);
        if ($value === null) {
            throw new Exception('ArrayHelper::getValue Error', "Missing '{$key}' in results: " . VarDumper::dumpAsString($results));
        }
        return $value;
    }
}
