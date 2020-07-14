<?php

namespace skeeks\modules\cms\tracker\models;

/**
 * This is the ActiveQuery class for [[CmsTrackerHits]].
 *
 * @see CmsTrackerHits
 */
class CmsTrackerHitsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return CmsTrackerHits[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return CmsTrackerHits|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
