<?php

namespace backend\components;

use common\components\MyAuthClient;
use common\models\Auth;
use common\models\User;
use Yii;
use yii\helpers\ArrayHelper;

class AuthHandler
{
    private $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function handle()
    {
        $oauthClient = new MyAuthClient();
        $attributes = $oauthClient->getProfile($this->token);

        $auth = $this->findAuth($attributes);
        if ($auth) {
            $user = $auth->user;
            return Yii::$app->user->login($user);
        }
        /** @var array $attributes */
        if ($user = $this->createAccount($attributes)) {
            return Yii::$app->user->login($user);
        }
    }

    private function findAuth(array $attributes): ?Auth
    {
        $redmineId = ArrayHelper::getValue($attributes, 'UserId');
        $params = [
            'redmine_id' => $redmineId,
            'token'      => $this->token,
        ];
        return Auth::findOne($params);
    }

    /**
     * @param array $attributes
     * @return bool|User|null
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    private function createAccount(array $attributes)
    {
        $userName = ArrayHelper::getValue($attributes, 'UserName');
        $login = ArrayHelper::getValue($attributes, 'Login');
        $roles = ArrayHelper::getValue($attributes, 'Roles');
        $id = ArrayHelper::getValue($attributes, 'UserId');

        if (User::findOne(['login' => $login])) {
            return false;
        }

        $user = $this->createUser($userName, $login, $roles);

        $transaction = User::getDb()->beginTransaction();
        if ($user->save()) {
            $auth = $this->createAuth($user->id, $id);
            if ($auth->save()) {
                $transaction->commit();
                return $user;
            }
        }
        $transaction->rollBack();
    }

    /**
     * @param $userName
     * @param $login
     * @param $roles
     * @return User|null
     * @throws \yii\base\Exception
     */
    private function createUser($userName, $login, $roles): ?User
    {
        $isAdmin = false;
        foreach ($roles as $role) {
            if ($role === 232) {
                $isAdmin = true;
            }
        }
        return new User([
            'name'    => $userName,
            'login'   => $login,
            'isAdmin' => $isAdmin,
            'auth_key' => Yii::$app->security->generateRandomString(),
        ]);
    }

    private function createAuth($userId, $redmineId): Auth
    {
        return new Auth([
            'user_id' => $userId,
            'redmine_id' => $redmineId,
            'token' => $this->token,
        ]);
    }
}