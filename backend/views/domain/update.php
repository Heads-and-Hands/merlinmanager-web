<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\ProjectDomain */

$this->title = Yii::t('content','Settings') .': '. $model->domain;
$this->params['breadcrumbs'][] = ['label' => Yii::t('content','Settings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->domain, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] =  Yii::t('content','Update Settings');
?>
<div class="project-domain-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>