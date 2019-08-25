<?php

namespace backend\controllers;

use backend\components\AuthHandler;
use common\components\MyAuthClient;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'redmine-auth'],
                        'allow'   => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->redirect('project/index');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        return $this->render('login');
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * @param null $token
     * @return \yii\console\Response|\yii\web\Response
     */
    public function actionRedmineAuth($token = null)
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        if (!$token) {
            $oauthClient = new MyAuthClient();
            $url = $oauthClient->buildAuthUrl();
            return Yii::$app->getResponse()->redirect($url);
        }
        (new AuthHandler($token))->handle();
        return $this->goHome();
    }
}
