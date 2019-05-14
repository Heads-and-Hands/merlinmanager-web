<?php

namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $verification_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property string $password write-only password
 */
class User extends ActiveRecord implements IdentityInterface
{
    public $password_repeat;
    public $password;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['login'], 'required'],
            [
                ['password', 'password_repeat'], 'required',
                'when' => function ($model) {
                    return $model->password || $model->password_repeat;
                },
                'whenClient' => 'function() { return $("#user-password").val() || $("#user-password_repeat").val()}'
            ],
            [['password'], 'compare', 'compareAttribute' => 'password_repeat'],
            ['name', 'string'],
            ['name', 'default', 'value' => ''],
            ['isAdmin', 'boolean'],
            [['login'], 'string', 'max' => 100],
            [['password_hash'], 'string', 'max' => 64],
            [['password'], 'string', 'max' => 64],
            [['password_repeat'], 'string', 'max' => 56],
            [['login'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function getQuantity()
    {
        return Html::a(Project::find()->where(['user_id' => $this->id])->count(),
            ['/project/index', 'ProjectSearch[user.login]' => $this->login]);
    }

    public function beforeSave($insert)
    {
        if ($this->password) {
            $this->setPassword($this->password);
            $this->generateAuthKey();
        }
        return parent::beforeSave($insert);
    }
}
