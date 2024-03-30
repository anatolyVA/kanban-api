<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task}}`.
 */
class m240330_101202_create_task_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%task}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(64)->notNull(),
            'status' => $this->string(64)->notNull(),
            'project_id' => $this->integer()->notNull(),
            'performer_id' => $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid')->notNull(),
        ]);

        $this->addForeignKey('performer_id', '{{%task}}', 'performer_id', '{{%user}}', 'id');
        $this->addForeignKey('status', '{{%task}}', 'status', '{{%status}}', 'title');
        $this->addForeignKey('project_id', '{{%task}}', 'project_id', '{{%project}}', 'id');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%task}}');
    }
}
