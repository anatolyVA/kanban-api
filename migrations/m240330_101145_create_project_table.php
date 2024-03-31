<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%project}}`.
 */
class m240330_101145_create_project_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%project}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(64)->notNull(),
            'creator_id' => $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid')->notNull(),
        ]);

        $this->addForeignKey('creator_id', '{{%project}}', 'creator_id', '{{%user}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%project}}');
    }
}
