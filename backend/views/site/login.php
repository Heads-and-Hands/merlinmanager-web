<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = Yii::t('content', 'Login:');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>
    <?php echo Yii::$app->user->id;  ?>
    <p> <?= Yii::t('content', 'Please fill out the following fields to login:'); ?></p>
    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
                <?= $form->field($model, 'login')->textInput() ?>
                <?= $form->field($model, 'password_hash')->passwordInput()->label('Password') ?>
                <div class="form-group">
                    <?= Html::submitButton(Yii::t('content', 'login'), ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>

    </div>
</div>
