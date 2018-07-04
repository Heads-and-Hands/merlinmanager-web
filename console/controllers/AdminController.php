<?php

namespace console\controllers;


use common\models\Admin;
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
        $email = $this->prompt('Email:');
        $password = $this->prompt('Password:');
        $admin =  Admin::create($email, $password);
        if(!$admin->validate()) {
            print_r('Not Created');
            print_r($admin->errors);
        } else {
            $admin->save();
        }
    }
}