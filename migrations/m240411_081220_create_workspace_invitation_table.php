<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%workspace_invitation}}`.
 */
class m240411_081220_create_workspace_invitation_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%workspace_invitation}}', [
            'workspace_id' => $this->getDb()
                         ->getSchema()
                         ->createColumnSchemaBuilder('uuid'),
            'user_id' => $this->getDb()
                         ->getSchema()
                         ->createColumnSchemaBuilder('uuid'),
        ]);

        $this->addPrimaryKey('workspace_id_invitee_id', '{{%workspace_invitation}}', ['workspace_id', 'user_id']);
        $this->addForeignKey('workspace_id', '{{%workspace_invitation}}', 'workspace_id', '{{%workspace}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('user_id', '{{%workspace_invitation}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%workspace_invitation}}');
    }
}
