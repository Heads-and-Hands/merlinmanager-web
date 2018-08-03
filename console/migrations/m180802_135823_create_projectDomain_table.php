<?php

use yii\db\Migration;

/**
 * Handles the creation of table `projectDomain`.
 */
class m180802_135823_create_projectDomain_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('projectDomain', [
            'id' => $this->primaryKey(),
            'domain' => $this->string(),
        ]);
        $this->insert('projectDomain',[
           'domain' => '',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('projectDomain');
    }
}
