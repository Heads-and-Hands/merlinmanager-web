<?php

use yii\db\Migration;

/**
 * Handles adding protected to table `{{%project}}`.
 */
class m190424_063931_add_protected_column_to_project_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('project', 'secret', $this->string()->null());
        $this->addColumn('project', 'status', $this->smallInteger()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('project', 'secret');
        $this->dropColumn('project', 'status');
    }
}
