<?php

namespace skeeks\modules\cms\tracker\models;

use Yii;

/**
 * This is the model class for table "{{%cms_tracker_hits}}".
 *
 * @property int $id
 * @property int $type
 * @property int $pk
 * @property string $model_class
 * @property int $hits
 * @property int $sync_at
 */
class CmsTrackerHits extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%cms_tracker_hits}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'pk', 'hits','sync_at'], 'integer'],
            [['model_class'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'          => Yii::t('skeeks/tracker', 'ID'),
            'type'        => Yii::t('skeeks/tracker', 'Type'),
            'pk'          => Yii::t('skeeks/tracker', 'Pk'),
            'model_class' => Yii::t('skeeks/tracker', 'Model Class'),
            'hits'        => Yii::t('skeeks/tracker', 'Hits'),
            'sync_at'     => Yii::t('skeeks/tracker', 'Sync At'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return CmsTrackerHitsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CmsTrackerHitsQuery(get_called_class());
    }
}
