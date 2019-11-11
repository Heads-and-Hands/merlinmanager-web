<?php

use common\models\Project;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Project */
/* @var $form yii\widgets\ActiveForm */

$this->registerJsFile('@web/js/main.js',  ['position' => yii\web\View::POS_END]);

?>

<div class="project-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'parent_id')->dropDownList(Project::getProjectList(),[
        'prompt' => '',
    ])->label('Parent') ?>

<!--     $form->field($model, 'secret',-->
<!--        [-->
<!--            'template' => "{label}\n-->
<!--                    <div class=\"input-group\" >-->
<!--                      {input}\n-->
<!--                      <span class=\"input-group-btn\">-->
<!--                            <button class=\"btn btn-default\" type=\"button\" onclick=\"random()\">-->
<!--                                 <span class=\"glyphicon glyphicon-refresh\" aria-hidden=\"true\"></span>-->
<!--                            </button>-->
<!--                      </span>-->
<!--                    </div>-->
<!--                    \n{hint}-->
<!--                    \n{error}"-->
<!--        ])->textInput(['id' => 'secret-input']) -->

    <?= $form->field($model, 'file')->fileInput()->label('Archive') ?>

    <?= Html::tag('p','or') ?>

    <?= $form->field($model, 'fileIndex')->fileInput()->label('File') ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
