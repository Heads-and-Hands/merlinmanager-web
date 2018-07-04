<?php

namespace common\models;

use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii;

/**
 * This is the model class for table "admin".
 *
 * @property integer $id
 * @property string $email
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $created_at
 * @property string $updated_at
 */
class Admin extends ActiveRecord implements IdentityInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'auth_key', 'password_hash',], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['auth_key'], 'string', 'max' => 32],
            [['password_hash'], 'string', 'max' => 255],
            [['password_reset_token'], 'string', 'max' => 64],
            [['password_reset_token'], 'unique'],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => date('Y-m-d H:i:s')
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Admin ID',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Method for admin creating
     *
     * @param $email string
     * @param $password string
     *
     * @return Admin
    */
    public static function create(string $email, string $password) : Admin
    {
        $admin = new static();
        $security = Yii::$app->getSecurity();
        $admin->auth_key = $security->generateRandomString();
        $admin->password_hash = $security->generatePasswordHash($password);
        return $admin;
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }


    public function validateAuthKey($authKey)
    {
        return $this->auth_key == $authKey;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }
}
