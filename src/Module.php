<?php

namespace skeeks\modules\cms\tracker;


class Module extends \yii\base\Module
{
    public $controllerNamespace = 'skeeks\modules\cms\tracker\controllers';

    /**
     * @return array
     */
    protected function _descriptor()
    {
        return array_merge(parent::_descriptor(), [
            "name"        => \Yii::t('skeeks/tracker', 'Cms tracker'),
            "description" => \Yii::t('skeeks/tracker', 'Cms tracker'),
        ]);
    }

}