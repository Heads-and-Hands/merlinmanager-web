<?php

namespace backend\models;

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
            ['name', 'validateName'],
            [['name'], 'string', 'max' => 100],
            [['file'], 'file',  'skipOnEmpty' => false, 'checkExtensionByMimeType' => false, 'extensions' => 'zip'],
            [['name'], 'unique' , 'targetClass' => '\backend\models\Project', 'targetAttribute' => ['name']],
        ];
    }

    public function validateName($attribute)
    {
        if (strpos($this->name, '/') !== false) {
            $this->addError($attribute, 'Name is incorrect');
        }
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
