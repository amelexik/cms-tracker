<?php
return [
    'other' => [
        'items' => [
            [

                'label' => \Yii::t('skeeks/tracker', 'Cms tracker'),
                'items' => [
                    [
                        "label"          => \Yii::t('skeeks/tracker', "Setting"),
                        "url"            => ["cms/admin-settings", "component" => 'skeeks\modules\cms\tracker\components\TrackerComponent']
                    ]
                ]
            ],
        ],

    ]
];