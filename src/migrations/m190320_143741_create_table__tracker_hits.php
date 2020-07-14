<?php

use yii\db\Migration;

/**
 * Class m190320_143741_create_table__tracker_hits
 */
class m190320_143741_create_table__tracker_hits extends Migration
{
    const TABLE = '{{%cms_tracker_hits}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable(self::TABLE, [
            'id'          => $this->primaryKey(),
            'type'        => $this->integer()->notNull()->defaultValue(1),
            'pk'          => $this->integer(),
            'model_class' => $this->string(),
            'hits'        => $this->integer()->notNull()->defaultValue(0),
            'sync_at'     => $this->integer(10)->unsigned(),
        ], $tableOptions);

        $this->createIndex('cms_tracker_hits_model_class_pk_type', self::TABLE, ['pk', 'model_class', 'type']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::TABLE);
    }
}
