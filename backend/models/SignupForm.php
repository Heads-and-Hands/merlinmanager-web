<?php

namespace app\models;

use yii\base\Model;

/**
 * This is the model class for table "User".
 *
 * @property int $id
 * @property string $name
 * @property string $login
 * @property string $password_hash
 * @property string $auth_key
 * @property int $isAdmin
 */
class SignupForm extends Model
{

    public $name;
    public $login;
    public $password_hash;

    public function rules()
    {
        return [
            [['name', 'login', 'password_hash'], 'required'],
            [['name', 'login'], 'string', 'max' => 100],
            [['password_hash'], 'string', 'max' => 64],
            [['login'], 'unique', 'targetClass' => '\app\models\User', 'targetAttribute' => ['login']],
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
            'login' => 'Login',
            'password_hash' => 'Password Hash',
        ];
    }
}
