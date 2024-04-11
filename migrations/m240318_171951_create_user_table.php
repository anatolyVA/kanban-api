<?php

use yii\base\NotSupportedException;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%user}}`.
 */
class m240318_171951_create_user_table extends Migration
{
    /**
     * {@inheritdoc}
     * @throws NotSupportedException
     */
    public function safeUp(): void
    {
        $this->createTable('{{%user}}', [
            'id' => $this->getDb()
                ->getSchema()
                ->createColumnSchemaBuilder('uuid')
                ->defaultExpression(new yii\db\Expression('gen_random_uuid()')),
            'first_name' => $this->string(52)->notNull(),
            'last_name' => $this->string(52)->notNull(),
            'username' => $this->string(52)->unique()->notNull(),
            'email' => $this->string(64)->unique()->notNull(),
            'password' => $this->string()->notNull(),
        ]);

        $this->addPrimaryKey('user_id', '{{%user}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%user}}');
    }
}


