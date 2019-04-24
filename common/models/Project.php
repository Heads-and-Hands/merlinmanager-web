<?php

namespace common\models;

use common\models\ProjectDomain;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Html;

/**
 * This is the model class for table "project".
 *
 * @property string $id
 * @property string $name
 * @property string $user_id
 * @property string $date
 * @property string $file
 * @property string $parent_id
 *
 * @property Project $parent
 * @property Project[] $projects
 * @property User $user
 */
class Project extends \yii\db\ActiveRecord
{

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    static $status = [
        self::STATUS_ACTIVE   => 'Активен',
        self::STATUS_INACTIVE => 'Не активен',
    ];

    public static function searchFile($folderName)
    {
        $files = FileHelper::findFiles($folderName, ['only' => ['index.html'], 'recursive' => FALSE]);
        return (bool)$files;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'user_id', 'file'], 'required'],
            [['user_id', 'parent_id', 'status'], 'integer'],
            [['date'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['file'], 'string', 'max' => 255],
            [['secret'], 'string', 'max' => 255],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::class, 'targetAttribute' => ['parent_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'        => 'ID',
            'name'      => 'Name',
            'user_id'   => 'User ID',
            'date'      => 'Date',
            'file'      => 'File',
            'parent_id' => 'Parent ID',
            'secret'    => 'Secret',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'date',
                'updatedAtAttribute' => null,
                'value'              => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Project::class, ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjects()
    {
        return $this->hasMany(Project::class, ['parent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return mixed
     */
    public static function getProjectList()
    {
        if (Yii::$app->user->identity->isAdmin) {
            $projectList = self::find()->all();
        } else {
            $projectList = self::find()->where(['user_id' => Yii::$app->user->identity->getId()])->all();
        }
        return ArrayHelper::map($projectList, 'id', 'name');
    }

    public function getTree()
    {
        $model = $this;
        $str = '';
        while ($model) {
            $str = $model->name . '/' . $str;
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

    public function getLink()
    {
        $domain = ProjectDomain::find()->one()->domain;
        $str = $this->getPath('/');
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

    public function getFullPath()
    {
        return Yii::getAlias('@filePath') . DIRECTORY_SEPARATOR . $this->getPath(DIRECTORY_SEPARATOR) ?? '';
    }
}
