<?php

namespace console\controllers;

use common\models\User;
use yii\console\Controller;

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
        $login = $this->prompt('Login:');
        $password = $this->prompt('Password:');
        $repeatPassword = $this->prompt('Repeat Password:');
        $model = new User([
            'login' => $login,
            'password' => $password,
            'password_repeat' => $repeatPassword,
            'isAdmin' => true,
        ]);

        if(!$model->save()) {
            print_r('Not Created');
            print_r($model->errors);
        }
    }
}