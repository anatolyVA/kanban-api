<?php

use yii\base\NotSupportedException;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%view}}`.
 */
class m240407_173550_create_view_table extends Migration
{
    /**
     * {@inheritdoc}
     * @throws NotSupportedException
     */
    public function safeUp(): void
    {
        $this->createTable('{{%view}}', [
            'id' => $this->getDb()
                ->getSchema()
                ->createColumnSchemaBuilder('uuid')
                ->defaultExpression(new yii\db\Expression('gen_random_uuid()')),
            'title' => $this->string(32)->notNull(),
            'project_id' => $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid')->notNull()
        ]);

        $this->addPrimaryKey('view_id', '{{%view}}', 'id');
        $this->addForeignKey('project_id', '{{%view}}', 'project_id', '{{%project}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%view}}');
    }
}
