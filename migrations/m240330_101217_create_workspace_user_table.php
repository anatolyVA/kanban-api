<?php

use yii\base\NotSupportedException;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%workspace_user}}`.
 */
class m240330_101217_create_workspace_user_table extends Migration
{
    /**
     * {@inheritdoc}
     * @throws NotSupportedException
     */
    public function safeUp(): void
    {
        $this->createTable('{{%workspace_user}}', [
            'workspace_id' => $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid'),
            'user_id' => $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid'),
        ]);

        $this->addPrimaryKey('workspace_id_user_id', '{{%workspace_user}}', ['workspace_id', 'user_id']);
        $this->addForeignKey('workspace_id', '{{%workspace_user}}', 'workspace_id', '{{%workspace}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('user_id', '{{%workspace_user}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%workspace_user}}');
    }
}
