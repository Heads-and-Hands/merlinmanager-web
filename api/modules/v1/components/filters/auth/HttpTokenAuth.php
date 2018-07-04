<?php

namespace api\modules\v1\components\filters\auth;
use yii\filters\auth\AuthMethod;

/**
 * Авторизация по бессрочному токену Auth-Token
*/
class HttpTokenAuth extends AuthMethod
{
    public $tokenHeader = 'Auth-Token';

    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $token = $request->getHeaders()->get($this->tokenHeader);
        if (empty($token)) {
            return null;//not authorized
        }

        if (is_string($token)) {
            return $user->loginByAccessToken($token, get_class($this));
        }

        return null;
    }
}
