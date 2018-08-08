<?php

namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\SluggableBehavior;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

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
            [['name', 'user_id', 'file'], 'required'],
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

    public function getFullPath()
    {
        $model = $this;
        $str = '';
        while ($model) {
            $str = $model->name . DIRECTORY_SEPARATOR . $str;
            $model = $model->parent;
        }
        $path = Yii::getAlias('@filePath') . DIRECTORY_SEPARATOR . $str;
        return $path;
    }

    public function getLink()
    {
        $str = '';
        $domainModel = ProjectDomain::find()->one();
        $model = $this;
        while ($model) {
            $str = $model->name . '/' . $str;
            $model = $model->parent;
        }
        $domain = $domainModel->domain;
        if ($domain) {
            if (substr($domain, strlen($domain) - 1) == "/") {
                $str = Html::a($domain . $str, $domain . $model->name);
            } else {
                $str = Html::a($str, $domain . '/' . $model->name);
            }
        } else {
            $str = Html::a('/' . $str, '/' . $str);
        }
        return $str;
    }

}

