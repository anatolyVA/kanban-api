<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%project_user}}`.
 */
class m240330_101217_create_project_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%project_user}}', [
            'project_id' => $this->integer(),
            'user_id' => $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid'),
        ]);

        $this->addPrimaryKey('project_id_user_id', '{{%project_user}}', ['project_id', 'user_id']);
        $this->addForeignKey('project_id', '{{%project_user}}', 'project_id', '{{%project}}', 'id');
        $this->addForeignKey('user_id', '{{%project_user}}', 'user_id', '{{%user}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%project_user}}');
    }
}
