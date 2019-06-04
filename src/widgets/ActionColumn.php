<?php

namespace app\widgets;

use app\Html;
use yii\grid\ActionColumn as BaseActionColumn;

/**
 * @inheritdoc
 */
class ActionColumn extends BaseActionColumn
{
    /**
     * @inheritdoc
     */
    protected function initDefaultButtons()
    {
        if (!isset($this->buttons['view'])) {
            $this->buttons['view'] = function ($url, $model, $key) {
                $options = array_merge([
                    'title' => '查看',
                    'aria-label' => '查看',
                    'data-pjax' => '0',
                    'class' => 'btn btn-info btn-xs',
                    'icon' => 'eye-open',
                ], $this->buttonOptions);
                return Html::a('查看', $url, $options);
            };
        }
        if (!isset($this->buttons['update'])) {
            $this->buttons['update'] = function ($url, $model, $key) {
                $options = array_merge([
                    'title' => '编辑',
                    'aria-label' => '编辑',
                    'data-pjax' => '0',
                    'class' => 'btn btn-primary btn-xs',
                    'icon' => 'pencil',
                ], $this->buttonOptions);
                return Html::a('编辑', $url, $options);
            };
        }
        if (!isset($this->buttons['delete'])) {
            $this->buttons['delete'] = function ($url, $model, $key) {
                $options = array_merge([
                    'title' => '删除',
                    'aria-label' => '删除',
                    'data-confirm' => '您确定要删除此项吗？',
                    'data-method' => 'post',
                    'data-pjax' => '0',
                    'class' => 'btn btn-danger btn-xs',
                    'icon' => 'trash',
                ], $this->buttonOptions);
                return Html::a('删除', $url, $options);
            };
        }
    }
}
