<?php

use yii\db\Migration;

/**
 * Handles the creation of table `project`.
 */
class m180705_072327_create_project_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('project', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull()->unique(),
            'user_id' => $this->integer()->notNull(),
            'date' => $this->date()->notNull(),
            'link' => $this->string(100)->notNull(),
            'file' => $this->string()->notNull(),
        ]);

        // creates index for column `login_id`
        $this->createIndex(
            'idx-project-login_id',
            'project',
            'user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-project-login_id',
            'project',
            'login_id',
            'user',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `user`
        $this->dropForeignKey(
            'fk-project-login_id',
            'project'
        );

        // drops index for column `login_id`
        $this->dropIndex(
            'idx-project-login_id',
            'project'
        );

        $this->dropTable('project');
    }
}
