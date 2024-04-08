<?php

use yii\base\NotSupportedException;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%project}}`.
 */
class m240407_162907_create_project_table extends Migration
{
    /**
     * {@inheritdoc}
     * @throws NotSupportedException
     */
    public function safeUp(): void
    {
        $this->createTable('{{%project}}', [
            'id' => $this->getDb()
                ->getSchema()
                ->createColumnSchemaBuilder('uuid')
                ->defaultExpression(new yii\db\Expression('gen_random_uuid()')),
            'title' => $this->string(32)->notNull(),
            'workspace_id' => $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid')->notNull(),
        ]);

        $this->addPrimaryKey('project_id', '{{%project}}', 'id');
        $this->addForeignKey('workspace_id', '{{%project}}', 'workspace_id', '{{%workspace}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%project}}');
    }
}
