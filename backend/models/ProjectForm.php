<?php

namespace backend\models;

use yii\base\Model;

class ProjectForm extends Model
{
    public $id;
    public $name;
    public $file;
    public $parent_id;
    public $fileIndex;

    public function rules()
    {
        return [
            [['name'], 'required'],
            ['name', 'validateName'],
            [['name'], 'string', 'max' => 100],
            [['file'], 'file', 'checkExtensionByMimeType' => false, 'extensions' => 'zip'],
            [['fileIndex'], 'file', 'checkExtensionByMimeType' => false, 'extensions' => 'html'],
            [['name'], 'unique', 'targetClass' => '\backend\models\Project', 'targetAttribute' => ['name']],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::class, 'targetAttribute' => ['parent_id' => 'id']],
            ['file', 'required', 'when' => function ($model) { return !$model->fileIndex;},
                'whenClient' => "function (attribute, value) { return false}"],
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
            'fileIndex' => 'FileIndex',
            'link' => 'Link',
            'user_id' => 'User_id',
        ];
    }


}
