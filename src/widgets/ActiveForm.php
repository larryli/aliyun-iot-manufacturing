<?php

namespace app\widgets;

use yii\bootstrap\ActiveForm as BaseActiveForm;

class ActiveForm extends BaseActiveForm
{
    /**
     * @internal
     */
    public $layout = 'horizontal';
    /**
     * @internal
     */
    public $fieldConfig = [
        'template' => "{label}\n<div class=\"col-lg-4\">{input}\n{hint}</div>\n<div class=\"col-lg-6\">{error}</div>",
        'labelOptions' => ['class' => 'col-lg-2 control-label'],
        'hintOptions' => ['class' => 'help-block'],
    ];
}
