<?php

namespace app\forms;

use app\aliyun\Exception as AliException;
use app\aliyun\Iot;
use app\Config;
use app\Html;
use Yii;
use yii\base\Exception as BaseException;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\Connection;
use yii\db\Exception as DbException;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\mutex\MysqlMutex;
use yii\validators\EachValidator;

class SetupForm extends Model
{
    /**
     * @var array
     */
    public static $DB_PREFIXES = [
        'sqlite' => 'SQLite',
        'mysql' => 'MySQL',
    ];
    /**
     * @var string DSN prefix, `sqlite` or `mysql`, default `sqlite`
     */
    public $dbPrefix;
    /**
     * @var string Sqlite file, default `@runtime/db.sqlite`
     */
    public $dbFile;
    /**
     * @var string Mysql host, default `localhost`
     */
    public $dbHost;
    /**
     * @var integer Mysql port, default null
     */
    public $dbPort;
    /**
     * @var string Mysql database name
     */
    public $dbName;
    /**
     * @var string Mysql charset, default `utf8mb4`
     */
    public $dbCharset;
    /**
     * @var string Mysql username, default `root`
     */
    public $dbUsername;
    /**
     * @var string Mysql password, default empty
     */
    public $dbPassword;
    /**
     * @var string Aliyun Iot access key
     */
    public $iotAccessKeyId;
    /**
     * @var string Aliyun Iot access key secret
     */
    public $iotAccessKeySecret;
    /**
     * @var string Aliyun Iot region
     */
    public $iotRegionId;
    /**
     * @var string[]
     */
    public $productKeys;
    /**
     * @var array
     */
    public $products = [];
    /**
     * @var Config
     */
    protected $config;

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->config = Yii::$app->get('config');

        if ($this->dbPrefix === null) {
            $this->dbPrefix = 'sqlite';
        }
        if ($this->dbFile === null) {
            $this->dbFile = YII_ENV_DEV ? '@runtime/dev.sqlite' : '@runtime/db.sqlite';
        }
        if ($this->dbHost === null) {
            $this->dbHost = 'localhost';
        }
        if ($this->dbPort === null) {
            $this->dbPort = 3306;
        }
        if ($this->dbCharset === null) {
            $this->dbCharset = 'utf8';
        }
        if ($this->dbUsername === null) {
            $this->dbUsername = 'root';
        }

        if ($this->iotRegionId === null) {
            $this->iotRegionId = 'cn-shanghai';
        }

        if ($this->productKeys === null) {
            $this->productKeys = [];
        }
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'dbPrefix' => '类型',
            'dbFile' => '文件',
            'dbHost' => '主机',
            'dbPort' => '端口',
            'dbName' => '库名',
            'dbUsername' => '用户名',
            'dbPassword' => '密码',
            'dbCharset' => '字符集',

            'iotAccessKeyId' => ' AccessKeyId ',
            'iotAccessKeySecret' => ' AccessKeySecret ',
            'iotRegionId' => ' RegionId ',

            'productKeys' => '产品',
        ];
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        $whenSqlite = function ($model) {
            /** @var $model static */
            return $model->dbPrefix == 'sqlite';
        };
        $prefixId = Html::getInputId($this, 'dbPrefix');
        $whenClientSqlite = "function (attribute, value) { return $('#{$prefixId}').val() == 'sqlite' }";
        $whenMysql = function ($model) {
            /** @var $model static */
            return $model->dbPrefix == 'mysql';
        };
        $whenClientMysql = "function (attribute, value) { return $('#{$prefixId}').val() == 'mysql' }";

        return [
            ['dbPrefix', 'required'],
            ['dbPrefix', 'in', 'range' => array_keys(static::$DB_PREFIXES)],

            ['dbFile', 'required', 'when' => $whenSqlite, 'whenClient' => $whenClientSqlite],
            ['dbFile', 'trim', 'when' => $whenSqlite, 'whenClient' => $whenClientSqlite],
            ['dbFile', 'string', 'when' => $whenSqlite, 'whenClient' => $whenClientSqlite],
            ['dbFile', 'validateSqlite', 'when' => $whenSqlite, 'whenClient' => $whenClientSqlite],

            [['dbHost', 'dbPort', 'dbName', 'dbCharset', 'dbUsername'], 'required', 'when' => $whenMysql, 'whenClient' => $whenClientMysql],
            [['dbHost', 'dbName', 'dbCharset', 'dbUsername', 'dbPassword'], 'trim', 'when' => $whenMysql, 'whenClient' => $whenClientMysql],
            [['dbHost', 'dbName', 'dbCharset', 'dbUsername', 'dbPassword'], 'string', 'when' => $whenMysql, 'whenClient' => $whenClientMysql],
            ['dbPort', 'integer', 'min' => 1, 'max' => 65535, 'when' => $whenMysql, 'whenClient' => $whenClientMysql],
            ['dbName', 'validateMysql', 'when' => $whenMysql, 'whenClient' => $whenClientMysql],

            [['iotAccessKeyId', 'iotAccessKeySecret', 'iotRegionId'], 'required', 'on' => ['default', 'ajax']],
            [['iotAccessKeyId', 'iotAccessKeySecret', 'iotRegionId'], 'trim', 'on' => ['default', 'ajax']],
            [['iotAccessKeyId', 'iotAccessKeySecret', 'iotRegionId'], 'string', 'on' => ['default', 'ajax']],
            [['iotAccessKeyId', 'iotAccessKeySecret', 'iotRegionId'], 'validateIot', 'on' => ['default', 'ajax']],

            ['productKeys', 'safe'], // need ad hoc validate
        ];
    }

    /**
     * 验证 SQLite 是否配置正确
     * @param string $attribute
     */
    public function validateSqlite($attribute)
    {
        if (!$this->hasErrors()) {
            $db = ArrayHelper::merge(Yii::$app->components['db'], $this->configSqlite());
            try {
                /** @var Connection $db */
                $db = Instance::ensure($db, Connection::class);
                $db->open();
            } catch (InvalidConfigException $e) {
                $this->addError($attribute, $e->getMessage());
            } catch (DbException $e) {
                $this->addError($attribute, $e->getMessage());
            }
        }
    }

    /**
     * 验证 MySQL 是否配置正确
     * @param string $attribute
     */
    public function validateMysql($attribute)
    {
        if (!$this->hasErrors()) {
            $db = ArrayHelper::merge(Yii::$app->components['db'], $this->configMysql());
            try {
                /** @var Connection $db */
                $db = Instance::ensure($db, Connection::class);
                $db->open();
            } catch (InvalidConfigException $e) {
                $this->addError($attribute, $e->getMessage());
            } catch (DbException $e) {
                $this->addError($attribute, $e->getMessage());
            }
        }
    }

    /**
     * 验证 Iot 是否配置正确
     */
    public function validateIot()
    {
        if (!$this->hasErrors()) {
            $iot = ArrayHelper::merge(Yii::$app->components['iot'], $this->configIot());
            try {
                /** @var Iot $iot */
                $iot = Instance::ensure($iot, Iot::class);
                $this->products = [];
                foreach ($iot->queryProductList()['List']['ProductInfo'] as $v) {
                    $this->products[$v['ProductKey']] = "{$v['ProductName']} [{$v['ProductKey']}] (设备数量：{$v['DeviceCount']})";
                }
            } catch (InvalidConfigException $e) {
                $this->addError('iotAccessKeyId', '内部错误：' . $e->getMessage());
            } catch (AliException $e) {
                switch ($e->getErrorCode()) {
                    case 'InvalidAccessKeyId.NotFound':
                        $this->addError('iotAccessKeyId', 'AccessKeyId 不存在。');
                        break;
                    case 'SDK.HostNotFound':
                        $this->addError('iotRegionId', '地域不存在。');
                        break;
                    case 'SignatureDoesNotMatch':
                        $this->addError('iotAccessKeySecret', '签名不匹配。');
                        break;
                    default:
                        $this->addError('iotAccessKeySecret', '未知错误：' . $e->getErrorMessage());
                        break;
                }
            }
        }
    }

    /**
     * 保存配置
     * @return bool
     * @throws BaseException
     */
    public function save()
    {
        if ($this->validate()) {
            // ad hoc validate
            if (!empty($this->productKeys)) {
                (new EachValidator([
                    'rule' => ['in', 'range' => array_keys($this->products)],
                ]))->validateAttribute($this, 'productKeys');
                if ($this->hasErrors()) {
                    return false;
                }
            } else {
                $this->productKeys = [];
            }
            $config = [
                'components' => [],
                'params' => [],
            ];
            if ($this->dbPrefix == 'mysql') {
                $config['components']['db'] = $this->configMysql();
                $config['components']['mutex'] = [
                    'class' => MysqlMutex::class,
                ];
            } else {
                $config['components']['db'] = $this->configSqlite();
            }
            $config['components']['iot'] = $this->configIot();
            $config['components']['request'] = $this->configRequest();
            $config['params']['productKeys'] = $this->configProductKeys();
            return $this->config->save($config);
        }
        return false;
    }

    /**
     * SQLite 配置
     * @return array
     */
    protected function configSqlite()
    {
        return [
            'dsn' => "sqlite:{$this->dbFile}",
        ];
    }

    /**
     * MySQL 配置
     * @return array
     */
    protected function configMysql()
    {
        $dsn = "mysql:host={$this->dbHost};";
        if ($this->dbPort != 3306) {
            $dsn .= "port={$this->dbPort};";
        }
        $dsn .= "dbname={$this->dbName}";
        return [
            'dsn' => $dsn,
            'username' => $this->dbUsername,
            'password' => $this->dbPassword,
            'charset' => $this->dbCharset,
        ];
    }

    /**
     * Aliyun IoT 配置
     * @return array
     */
    protected function configIot()
    {
        return [
            'client' => [
                'accessKeyId' => $this->iotAccessKeyId,
                'accessKeySecret' => $this->iotAccessKeySecret,
                'regionId' => $this->iotRegionId,
            ],
        ];
    }

    /**
     * Cookie 配置
     *
     * @return array
     * @throws BaseException
     */
    protected function configRequest()
    {
        return [
            'cookieValidationKey' => Yii::$app->security->generateRandomString(),
        ];
    }

    /**
     * 量产产品配置
     * @return string[]
     */
    protected function configProductKeys()
    {
        return $this->productKeys;
    }
}
