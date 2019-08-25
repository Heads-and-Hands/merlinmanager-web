<?php

namespace backend\components;

use Yii;
use yii\authclient\OAuth2;
use yii\web\HttpException;

class Redmine extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    public $authUrl = 'http://oauth.handh.ru:9888/oauth';
    /**
     * {@inheritdoc}
     */
    public $tokenUrl = 'http://oauth.handh.';

    public $returnUrl = 'http://localhost:10081/site/redmine-auth';

    /**
     * {@inheritdoc}
     */
    public function buildAuthUrl(array $params = [])
    {
        $authState = $this->generateAuthState();
        $this->setState('authState', $authState);
        $defaultParams = [
            'redirect_uri' => $this->returnUrl,
        ];
        return $this->composeUrl($this->authUrl, array_merge($defaultParams, $params));
    }

    protected function initUserAttributes()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultName()
    {
        return 'redmine';
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultTitle()
    {
        return 'Redmine';
    }
}
