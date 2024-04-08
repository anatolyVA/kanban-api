<?php

use yii\base\NotSupportedException;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%view_user}}`.
 */
class m240407_180620_create_view_user_table extends Migration
{
    /**
     * {@inheritdoc}
     * @throws NotSupportedException
     */
    public function safeUp(): void
    {
        $this->createTable('{{%view_user}}', [
            'view_id' => $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid'),
            'user_id' => $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid')
        ]);

        $this->addPrimaryKey('view_id_user_id', '{{%view_user}}', ['view_id', 'user_id']);
        $this->addForeignKey('view_id', '{{%view_user}}', 'view_id', '{{%view}}', 'id');
        $this->addForeignKey('user_id', '{{%view_user}}', 'user_id', '{{%user}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%view_user}}');
    }
}
