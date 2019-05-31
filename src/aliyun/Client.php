<?php

namespace app\aliyun;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use Yii;
use yii\base\Component;
use yii\base\Exception as BaseException;
use yii\base\InvalidConfigException;

abstract class Client extends Component
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $regionId = 'cn-hangzhou';

    abstract protected function initClient();

    /**
     * @throws BaseException
     * @throws ClientException
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty($this->name)) {
            $this->defaultName();
        }
        if (!AlibabaCloud::has($this->name)) {
            if (empty($this->regionId)) {
                throw new InvalidConfigException ('The "regionId" property must be set.');
            }
            $this->initClient();
        }
    }

    /**
     * @throws BaseException
     */
    protected function defaultName()
    {
        $this->name = Yii::$app->security->generateRandomString();
    }
}
