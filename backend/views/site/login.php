<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

use yii\helpers\Html;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
    .auth-icon {
        width: 500px;
        height: 70px;
    }
</style>
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please login through Redmine:</p>

    <?= Html::a('Redmine login', 'redmine-auth') ?>

</div>
