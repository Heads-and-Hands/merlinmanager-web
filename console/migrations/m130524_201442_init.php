<?php

use yii\db\Migration;

class m130524_201442_init extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' =>$this->string(100)->notNull(),
            'login' => $this->string(100)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'isAdmin' => $this->boolean()->defaultValue(false),
        ], $tableOptions);

        $this->createTable('projectDomain', [
            'id' => $this->primaryKey()->unsigned(),
            'domain' => $this->string(),
        ]);
        $this->insert('projectDomain',[
            'domain' => '',
        ]);

        $this->createTable('{{%project}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(100)->notNull()->unique(),
            'user_id' => $this->integer()->notNull()->unsigned(),
            'date' => $this->date()->notNull(),
            'file' => $this->string()->notNull(),
            'parent_id' => $this->integer()->unsigned(),
        ],$tableOptions);

        $this->createIndex(
            'idx-category-parent_id',
            '{{%project}}',
            'parent_id'
        );
        $this->addForeignKey(
            'fk-category-parent',
            '{{%project}}',
            'parent_id',
            '{{%project}}',
            'id',
            'SET NULL',
            'RESTRICT'
        );

        // creates index for column `user_id`
        $this->createIndex(
            'idx-project-user_id',
            'project',
            'user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-project-user_id',
            'project',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );

        $this->update('{{%user}}', [
            'id' => $this->primaryKey()->unsigned(),
        ]);

        $this->update('project', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(100)->notNull(),
            'user_id' => $this->integer()->notNull()->unsigned(),
            'parent_id' => $this->integer()->unsigned(),
        ]);

       $this->update('projectDomain', [
            'id' => $this->primaryKey()->unsigned(),
        ]);

        $this->dropIndex('name', 'project');
    }

    public function down()
    {
        $this->dropTable('{{%project}}');
        $this->dropTable('{{%user}}');
        $this->dropTable('{{%projectDomain}}');
    }
}
