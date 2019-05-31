<?php

namespace app\widgets;

use yii\bootstrap\ActiveForm;

class SetupActiveForm extends ActiveForm
{
    /**
     * @internal
     */
    public $layout = 'horizontal';
    /**
     * @internal
     */
    public $fieldConfig = [
        'template' => "{label}\n<div class=\"col-lg-4\">{input}</div>\n<div class=\"col-lg-6\">{error}</div>",
        'labelOptions' => ['class' => 'col-lg-2 control-label'],
    ];
}
