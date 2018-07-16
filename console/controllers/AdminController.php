<?php

namespace console\controllers;


use yii\console\Controller;
use backend\models\User;

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

    public function actionCreate()
    {
        $user = new User();
        $login = $this->prompt(':login');
        $password = $this->prompt(':Password');
        $user->password = $password;
        $user->setPassword($password);
        $user->login = $login;
        $user->isAdmin = true;
        if(!$user->validate()) {
            print_r('Not Created');
            print_r($user->errors);
        } else {
            $user->generateAuthKey();
            $user->save();
        }
    }
}