<?php

use yii\base\NotSupportedException;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_token}}`.
 */
class m240319_184251_create_user_token_table extends Migration
{
    /**
     * {@inheritdoc}
     * @throws NotSupportedException
     */
    public function safeUp(): void
    {
        $this->createTable('{{%user_token}}', [
            'user_id' => $this->getDb()
                ->getSchema()
                ->createColumnSchemaBuilder('uuid')
                ->notNull(),
            'refresh_token' => $this->string(510)->notNull()->unique(),
            'user_ip' => $this->string(50)->notNull(),
            'expiration_date' => $this->dateTime()->notNull()
        ]);
        $this->addPrimaryKey('refresh_token', "{{%user_token}}", 'refresh_token');
        $this->addForeignKey('user_id', '{{%user_token}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_token}}');
    }
}
