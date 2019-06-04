<?php

namespace app;

use yii\bootstrap\Html as BaseHtml;

/**
 * @inheritDoc
 */
class Html extends BaseHtml
{
    /**
     * @inheritDoc
     */
    public static function a($text, $url = null, $options = [])
    {
        if (isset($options['icon'])) {
            $text = self::icon($options['icon']) . ' ' . $text;
            unset($options['icon']);
        }
        return parent::a($text, $url, $options);
    }

    /**
     * @inheritDoc
     */
    public static function submitButton($content = 'Submit', $options = [])
    {
        if (isset($options['icon'])) {
            $content = self::icon($options['icon']) . ' ' . $content;
            unset($options['icon']);
        }
        return parent::submitButton($content, $options);
    }
}
