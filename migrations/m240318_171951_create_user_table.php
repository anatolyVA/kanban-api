<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user}}`.
 */
class m240318_171951_create_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%user}}', [
            'id' => $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid'),
            'first_name' => $this->string(52)->notNull(),
            'last_name' => $this->string(52)->notNull(),
            'email' => $this->string(64)->unique()->notNull(),
            'password' => $this->string()->notNull(),
        ]);

        $this->addPrimaryKey('id', '{{%user}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%user}}');
    }
}


