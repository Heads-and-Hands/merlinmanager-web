<?php

namespace app\models;

use Yii;
use yii\base\Model;

class ProjectForm extends Model
{
    public $name;
    public $file;

    public function rules()
    {
        return [
            [['name','file'], 'required'],
            [['name'], 'string', 'max' => 100],
            [['file'], 'file',  'skipOnEmpty' => false, 'checkExtensionByMimeType' => false, 'extensions' => 'zip'],
            [['name'], 'unique' , 'targetClass' => '\app\models\Project', 'targetAttribute' => ['name']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'file' => 'File',
            'link' => 'Link',
            'user_id' => 'User_id',
        ];
    }
}
