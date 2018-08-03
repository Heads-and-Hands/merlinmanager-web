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
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('project', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull()->unique(),
            'user_id' => $this->integer()->notNull(),
            'date' => $this->date()->notNull(),
            'file' => $this->string()->notNull(),
            'parent_id' => $this->integer(),
        ],$tableOptions);




        $this->createIndex('idx-category-parent_id', '{{%project}}', 'parent_id');
        $this->addForeignKey('fk-category-parent', '{{%project}}', 'parent_id', '{{%project}}', 'id', 'SET NULL', 'RESTRICT');


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
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `user`
        $this->dropForeignKey(
            'fk-project-user_id',
            'project'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            'idx-project-user_id',
            'project'
        );

        $this->dropTable('project');
    }
}
