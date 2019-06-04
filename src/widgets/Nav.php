<?php

namespace app\widgets;

use app\Html;
use yii\bootstrap\Nav as BaseNav;

/**
 * @inheritdoc
 */
class Nav extends BaseNav
{
    /**
     * @inheritdoc
     */
    public function renderItem($item)
    {
        if (isset($item['icon'])) {
            $item['label'] = Html::icon($item['icon']) . (isset($item['label']) ? ' ' . Html::encode($item['label']) : '');
            $item['encode'] = false;
            unset($item['icon']);
        }
        return parent::renderItem($item);
    }
}