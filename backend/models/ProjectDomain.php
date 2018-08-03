<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "projectDomain".
 *
 * @property int $id
 * @property string $domain
 */
class ProjectDomain extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'projectDomain';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['domain'], 'url'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'domain' => 'Domain',
        ];
    }

}
