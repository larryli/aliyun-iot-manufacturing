<?php
/**
 * 入口
 */

/**
 * @param $path
 * @return bool|mixed
 */
function requireVendor($path)
{
    $path = realpath($path);
    $file1 = $path . '/autoload.php';
    if (file_exists($file1)) {
        /** @noinspection PhpIncludeInspection */
        require($file1);
        defined('VENDOR_PATH') || define('VENDOR_PATH', $path);
        return true;
    }
    return false;
}

if (!$loader = requireVendor(__DIR__ . '/vendor')) {
    if (PHP_SAPI !== 'cli') {
        echo '<pre>';
    }
    echo 'You must set up the project dependencies, run the following commands:' . PHP_EOL .
        'curl -sS https://getcomposer.org/installer | php' . PHP_EOL .
        'php composer.phar install' . PHP_EOL;
    exit(1);
}
return $loader;
