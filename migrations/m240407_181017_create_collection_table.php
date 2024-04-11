<?php

use yii\base\NotSupportedException;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%collection}}`.
 */
class m240407_181017_create_collection_table extends Migration
{
    /**
     * {@inheritdoc}
     * @throws NotSupportedException
     */
    public function safeUp(): void
    {
        $this->createTable('{{%collection}}', [
            'id' => $this->getDb()
                ->getSchema()
                ->createColumnSchemaBuilder('uuid')
                ->defaultExpression(new yii\db\Expression('gen_random_uuid()')),
            'title' => $this->string(32)->notNull(),
            'view_id' => $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid')->notNull(),
            'status' => $this->smallInteger(1)
        ]);

        $this->addPrimaryKey('collection_id', '{{%collection}}', 'id');
        $this->addForeignKey('view_id', '{{%collection}}', 'view_id', '{{%view}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('{{%collection}}');
    }
}
