<?php

use yii\base\NotSupportedException;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%comment}}`.
 */
class m240408_175605_create_comment_table extends Migration
{
    /**
     * {@inheritdoc}
     * @throws NotSupportedException
     */
    public function safeUp(): void
    {
        $this->createTable('{{%comment}}', [
            'id' => $this->getDb()
                ->getSchema()
                ->createColumnSchemaBuilder('uuid')
                ->defaultExpression(new yii\db\Expression('gen_random_uuid()')),
            'task_id' => $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid')->notNull(),
            'message' => $this->string(510)->notNull(),
            'creator_id' => $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid')->notNull(),
            'created_at' => $this->dateTime()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->dateTime(),
        ]);

        $this->addPrimaryKey('comment_id', '{{%comment}}', 'id');
        $this->addForeignKey('creator_id', '{{%comment}}', 'creator_id', '{{%user}}', 'id');
        $this->addForeignKey('task_id', '{{%comment}}', 'task_id', '{{%task}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%comment}}');
    }
}
