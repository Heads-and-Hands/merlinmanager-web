<?php

namespace console\controllers;

use common\models\User;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

/**
 * This is the command line tool for admins.
 *
 * You can use this command to create admin:
 *
 * ```
 * $ ./yii admin/create
 * ```
 */
class AdminController extends Controller
{
    /**
    Create admin user
     */
    public function actionCreate()
    {
        $name = $this->prompt('Name:');
        $login = $this->prompt('Login:');
        $password = $this->prompt('Password:');
        $model = new User;
        $security = \Yii::$app->getSecurity();
        $model->name = $name;
        $model->login = $login;
        $model->auth_key = $security->generateRandomString();
        $model->isAdmin = true;
        $model->password = $password;
        $model->password_hash = $security->generatePasswordHash($password);
        $model->password_repeat = $password;
        if(!$model->validate()) {
            print_r('Not Created');
            print_r($model->errors);
        } else {
            $model->save();
        }
    }
}