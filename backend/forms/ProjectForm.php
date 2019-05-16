<?php

namespace backend\forms;

use common\models\Project;
use yii\base\Model;
use yii\web\UploadedFile;

class ProjectForm extends Model
{
    public $id;
    public $name;
    public $secret;
    public $file;
    public $parent_id;
    public $fileIndex;
    public $status;

    public $isNew;

    public function rules()
    {
        return [
            [['name', 'secret'], 'required'],
            ['name', 'validateName'],
            ['status', 'boolean'],
            [['secret'], 'string', 'max' => 250],
            [['name'], 'string', 'max' => 100],
            [['file'], 'file', 'checkExtensionByMimeType' => false, 'extensions' => 'zip'],
            [['fileIndex'], 'file', 'checkExtensionByMimeType' => false, 'extensions' => 'html'],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::class, 'targetAttribute' => ['parent_id' => 'id']],
            ['file', 'required', 'when'       =>
                                     function ($model) {
                                         return $model->isNew && !$model->fileIndex;
                                     },
                                 'whenClient' => "function (attribute, value) { return false}"
            ],
            ['fileIndex', 'required', 'when'       =>
                                          function ($model) {
                                              return $model->isNew && !$model->file;
                                          },
                                      'whenClient' => "function (attribute, value) { return false}"
            ],
            ['isNew', 'safe'],
        ];
    }

    public function load($data, $formName = null)
    {
        if (parent::load($data, $formName)) {
            $this->file = UploadedFile::getInstance($this, 'file');
            $this->fileIndex = UploadedFile::getInstance($this, 'fileIndex');
            return true;
        }

        return false;
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
            'id'        => 'ID',
            'name'      => 'Name',
            'file'      => 'File',
            'fileIndex' => 'FileIndex',
            'link'      => 'Link',
        ];
    }
}