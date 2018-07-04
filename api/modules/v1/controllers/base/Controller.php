<?php

namespace api\modules\v1\controllers\base;

use api\modules\v1\components\filters\auth\HttpTokenAuth;
use yii\filters\auth\CompositeAuth;
use yii\filters\Cors;
use yii\helpers\ArrayHelper;

/**
 * Контроллер родитель для контроллеров апи требующих авторизацию
*/
class Controller extends \yii\rest\Controller
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'corsFilter' => [
                'class' => Cors::className(),
            ],
            'authenticator' => [
                'class' => CompositeAuth::className(),
                'authMethods' => [
                    [
                        'class' => HttpTokenAuth::className(),
                    ],
                ],
            ],
        ]);
    }
}