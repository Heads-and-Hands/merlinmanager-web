<?php

use yii\db\Migration;

/**
 * Handles the creation of table `test`.
 */
class m180702_124655_create_test_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('user', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull(),
            'login' => $this->string(100)->notNull()->unique(),
            'password_hash' => $this->string(100)->notNull(),
            'auth_key' => $this->string(100)->notNull(),
            'isAdmin' => $this->boolean()->defaultValue(false),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('user');
    }
}
