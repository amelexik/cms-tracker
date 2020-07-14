<?php
return [
    'bootstrap'  => ['skeeks\modules\cms\tracker\Bootstrap'],
    'modules'    => [
        'tracker' => [
            'class' => 'skeeks\modules\cms\tracker\Module',
        ],
    ],
    'components' =>
        [
            'tracker' => [
                'class' => 'skeeks\modules\cms\tracker\components\TrackerComponent',
            ],
            'i18n'    => [
                'translations' => [
                    'skeeks/tracker' => [
                        'class'    => 'yii\i18n\PhpMessageSource',
                        'basePath' => '@skeeks/modules/cms/tracker/messages'
                    ],
                ],
            ],
        ],
];