<?php

namespace app;

use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

/**
 * 使用 config.php 重新配置应用
 */
class Config extends Component implements BootstrapInterface
{
    /**
     * @var string
     */
    public $config;
    /**
     * @var string
     */
    public $header = <<< EOF
<?php
/**
 * This file is generated by the setup.
 * DO NOT MODIFY THIS FILE DIRECTLY.
 * @version {version}
 */
return
EOF;

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty($this->config)) {
            throw new InvalidConfigException('The Config::config must be set.');
        }
        $this->config = Yii::getAlias($this->config);
    }

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     * @throws InvalidConfigException
     */
    public function bootstrap($app)
    {
        foreach ($this->load() as $k => $v) {
            switch ($k) {
                case 'name':
                case 'language':
                case 'layout':
                    $app->$k = $v;
                    break;
                case 'timeZone':
                    $app->setTimeZone($v);
                    break;
                case 'params':
                    $app->params = ArrayHelper::merge($app->params, $v);
                    break;
                case 'components':
                    foreach ($v as $id => $definition) {
                        if (!empty($app->components[$id])) {
                            $app->set($id, ArrayHelper::merge($app->components[$id], $definition));
                        }
                    }
                    break;
            }
        }
    }

    /**
     * @return bool
     */
    public function empty()
    {
        return empty($this->load());
    }

    /**
     * @return array
     */
    public function load()
    {
        if (!file_exists($this->config)) {
            $this->save([]);
        }
        /** @noinspection PhpIncludeInspection */
        return require $this->config;
    }

    /**
     * @param array $config
     * @return bool
     */
    public function save($config)
    {
        $content = str_replace('{version}', date('Y-m-d H:i:s'), $this->header)
            . ' ' . VarDumper::export($config) . ";\n";
        return file_put_contents($this->config, $content) !== false;
    }
}
