<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Project */
/* @var $form yii\widgets\ActiveForm */

$this->registerJsFile('@web/js/main.js', ['position' => yii\web\View::POS_END]);

?>

<div class="project-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($projectForm, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'secret',
        [
            'template' => "{label}\n
                        <div class=\"input-group\" >
                         {input}\n
                         <span class=\"input-group-btn\">
                               <button class=\"btn btn-default\" type=\"button\" onclick=\"random()\">
                                    <span class=\"glyphicon glyphicon-refresh\" aria-hidden=\"true\"></span>
                               </button>
                         </span>
                       </div>
                     \n{hint}
                    \n{error}"
        ])->textInput(['id' => 'secret-input']) ?>

    <?= $form->field($projectForm, 'file')->fileInput()->label('Archive') ?>

    <?= Html::tag('p', 'or') ?>

    <?= $form->field($projectForm, 'fileIndex')->fileInput()->label('File') ?>

    <?php if (Yii::$app->user->identity->isAdmin) : ?>
        <?= $form->field($model, 'status')->checkbox() ?>
    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>


