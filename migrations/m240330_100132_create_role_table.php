<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%role}}`.
 */
class m240330_100132_create_role_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('{{%role}}', [
            'title' => $this->string(32),
        ]);

        $this->addPrimaryKey('role_title', '{{%role}}', 'title');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%role}}');
    }
}
