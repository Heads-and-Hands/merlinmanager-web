<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Project */

$this->title = Yii::t('content', 'Update Project: {model_name}',[
        'model_name' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' =>  Yii::t('content', 'Projects'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('content', 'Update');
?>
<div class="project-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
