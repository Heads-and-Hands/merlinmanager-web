<?php


namespace common\components;

use Codeception\Module\Cli;
use GuzzleHttp\Client;
use Yii;
use yii\authclient\OAuth2;
use yii\helpers\Json;

class MyAuthClient extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    public $authUrl = 'http://oauth.handh.ru:9888/oauth';

    /**
     * {@inheritdoc}
     */
    public function buildAuthUrl(array $params = [])
    {
        $authState = $this->generateAuthState();
        $this->setState('authState', $authState);
        $defaultParams = [
            'redirect_uri' => $this->getReturnUrl(),
        ];
        return $this->composeUrl($this->authUrl, array_merge($defaultParams, $params));
    }

    public function getProfile($token)
    {
        $client = new Client();
        $response = $client->request('GET', $this->authUrl . '/token?token=' . $token, [
            'headers' => ['Content-Type' => 'application/json'],
        ]);

        return Json::decode($response->getBody()->getContents(), true);
    }

    public function initUserAttributes()
    {
        // TODO: Implement initUserAttributes() method.
    }
}