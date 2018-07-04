<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'api\controllers',
    'modules' => [
        'v1' => [
            'class' => 'api\modules\v1\Module',
        ],
    ],
    'components' => [
        'user' => [
            'identityClass' => 'common\models\User',
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'response' => [
            'format' => yii\web\Response::FORMAT_JSON,
            'on beforeSend' => function ($event) {
                /* @var $response \yii\web\Response */
                $response = $event->sender;
                $error = $response->statusCode != 200;
                $response->data =  [
                    'data' => !$error ? $response->data : null,
                    'code' => empty($code) ? ($error ? 0 : 1) : $code,
                    'message' => $error
                        ? (isset($response->data['message']) ? $response->data['message'] : 'Unknown Error')
                        : '',
                ];
            },
        ],
        'request' => [
            'class' => '\yii\web\Request',
            'baseUrl' => '/api',
            'enableCookieValidation' => false,
            'enableCsrfValidation' => false,
            'parsers' => [
                'multipart/form-data' => 'yii\web\MultipartFormDataParser',
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
    ],
    'params' => $params,
];
