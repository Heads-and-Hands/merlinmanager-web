<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\ProjectDomain */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Project Domains', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-domain-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'domain',
        ],
    ]) ?>

</div>
