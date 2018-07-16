<?php

namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\SluggableBehavior;
use yii\db\Expression;
use yii\helpers\FileHelper;

/**
 * This is the model class for table "project".
 *
 * @property int $id
 * @property string $name
 * @property int $user_id
 * @property string $date
 * @property string $link
 * @property string $file
 */
class Project extends \yii\db\ActiveRecord
{
    public $zipFile;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project';
    }

    public function search_file($folderName)
    {
        $files = FileHelper::findFiles($folderName,['only'=>['index.html']]);
        return (bool)$files;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'user_id', 'file'], 'required'],
            [['user_id'], 'integer'],
            [['date'], 'safe'],
            [['name', 'link'], 'string', 'max' => 100],
            [['file'], 'string'],
            [['name'], 'unique'],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => SluggableBehavior::class,
                'attribute' => 'name',
                'slugAttribute' => 'link',
            ],
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'date',
                'updatedAtAttribute' => null,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'user_id' => 'User ID',
            'date' => 'Date',
            'link' => 'Link',
            'file' => 'File',
        ];
    }

    public function afterDelete()
    {
        parent::afterDelete();
        FileHelper::unlink(Yii::getAlias('@filePath'). '/' .$this->file);
        FileHelper::removeDirectory(Yii::getAlias('@filePath'). '/' .$this->name);

    }
}
