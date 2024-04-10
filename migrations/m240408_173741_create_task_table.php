<?php

use yii\base\NotSupportedException;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%task}}`.
 */
class m240408_173741_create_task_table extends Migration
{
    /**
     * {@inheritdoc}
     * @throws NotSupportedException
     */
    public function safeUp(): void
    {
        $this->createTable('{{%task}}', [
            'id' => $this->getDb()
                ->getSchema()
                ->createColumnSchemaBuilder('uuid')
                ->defaultExpression(new yii\db\Expression('gen_random_uuid()')),
            'title' => $this->string(32)->notNull(),
            'description' => $this->string(510),
            'deadline' => $this->dateTime(),
            'priority' => $this->smallInteger(2),
            'is_completed' => $this->boolean()->defaultValue(false),
            'collection_id' => $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid')->notNull(),
            'creator_id' => $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid')->notNull(),
            'parent_id' => $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid'),
        ]);

        $this->addPrimaryKey('task_id', '{{%task}}', 'id');
        $this->addForeignKey('creator_id', '{{%task}}', 'creator_id', '{{%user}}', 'id');
        $this->addForeignKey('parent_id', '{{%task}}', 'parent_id', '{{%task}}', 'id');
        $this->addForeignKey('collection_id', '{{%task}}', 'collection_id', '{{%collection}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%task}}');
    }
}
