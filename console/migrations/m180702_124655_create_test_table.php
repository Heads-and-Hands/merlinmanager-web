<?php

use yii\db\Migration;

/**
 * Handles the creation of table `test`.
 */
class m180702_124655_create_test_table extends Migration
{

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('user', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull(),
            'login' => $this->string(100)->notNull()->unique(),
            'password_hash' => $this->string(100)->notNull(),
            'auth_key' => $this->string(100)->notNull(),
            'isAdmin' => $this->boolean()->defaultValue(false),
        ],$tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('user');
    }
}
