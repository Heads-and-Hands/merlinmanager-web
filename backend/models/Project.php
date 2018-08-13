<?php

namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "project".
 *
 * @property int $id
 * @property string $name
 * @property int $user_id
 * @property string $date
 * @property string $file
 *
 * @property Project $project
 */
class Project extends ActiveRecord
{
    public $zipFile;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project';
    }

    public static function searchFile($folderName)
    {
        $files = FileHelper::findFiles($folderName, ['only' => ['index.html'], 'recursive' => FALSE]);
        return (bool)$files;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'user_id'], 'required'],
            [['user_id'], 'integer'],
            [['date'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['name'], 'unique'],
        ];
    }

    public function behaviors()
    {
        return [
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
            'parent_id' => 'Parent ID',
            'date' => 'Date',
            'file' => 'File',
        ];
    }

    public function afterDelete()
    {
        parent::afterDelete();
        FileHelper::removeDirectory(Yii::getAlias('@filePath') . '/' . $this->name);
        FileHelper::removeDirectory(Yii::getAlias('@filePath') . '/' . $this->getTree());
    }

    public function getParent()
    {
        return $this->hasOne(Project::class, ['id' => 'parent_id']);
    }

    public function getTree()
    {
        $model = $this;
        $str = '';
        while ($model) {
            $str = $model->name . "/" . $str;
            $model = $model->parent;
        }
        return $str;
    }

    public function getPath($separator)
    {
        $model = $this;
        $str = '';
        while ($model) {
            $str = $model->name . $separator . $str;
            $model = $model->parent;
        }
        return $str;
    }

    public function getFullPath()
    {
        $path = Yii::getAlias('@filePath') . DIRECTORY_SEPARATOR . $this->getPath(DIRECTORY_SEPARATOR);
        return $path;
    }

    public function getLink()
    {
        $domainModel = ProjectDomain::find()->one();
        $str = $this->getPath('/');
        $domain = $domainModel->domain;
        if ($domain) {
            if (substr($domain, strlen($domain) - 1) == '/') {
                $str = $domain . $str;
            } else {
                $str = $domain . '/' . $str;
            }
        } else {
            $str = '/' . $str;
        }
        return Html::a($str, $str);
    }
}

