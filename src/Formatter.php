<?php

namespace app;

use yii\helpers\Html;
use yii\i18n\Formatter as BaseFormatter;

class Formatter extends BaseFormatter
{
    /**
     * @param string $value
     * @return string
     */
    public function asCode($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        return Html::tag('code', Html::encode($value));
    }
}
