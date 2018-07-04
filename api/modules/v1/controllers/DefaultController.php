<?php

namespace api\modules\v1\controllers;

use api\modules\v1\controllers\base\Controller;

/**
 * Default controller for the `v1` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return 'api';
    }
}
