<?php

use yii\helpers\html;
use yii\bootstrap\ActiveForm;

?>
<h1>New User</h1>

<?php $form = ActiveForm::begin(['method' => 'post']) ?>

    <?= $form->field($model, 'name')->textInput() ?>

    <?= $form->field($model, 'login')->textInput() ?>

    <?= $form->field($model, 'password_hash')->passwordInput() ?>

    <?= Html::submitButton('Signup', ['class' => 'btn btn-primary']) ?>

<?php ActiveForm::end() ?>
