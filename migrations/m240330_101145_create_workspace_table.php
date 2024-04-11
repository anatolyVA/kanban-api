<?php

use yii\base\NotSupportedException;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%workspace}}`.
 */
class m240330_101145_create_workspace_table extends Migration
{
    /**
     * {@inheritdoc}
     * @throws NotSupportedException
     */
    public function safeUp(): void
    {
        $this->createTable('{{%workspace}}', [
            'id' => $this->getDb()
                ->getSchema()
                ->createColumnSchemaBuilder('uuid')
                ->defaultExpression(new yii\db\Expression('gen_random_uuid()')),
            'title' => $this->string(32)->notNull(),
            'creator_id' => $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid')->notNull(),
        ]);

        $this->addPrimaryKey('workspace_id', '{{%workspace}}', 'id');
        $this->addForeignKey('creator_id', '{{%workspace}}', 'creator_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%workspace}}');
    }
}
