<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Project Domains';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-domain-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            'domain',
            ['class' => 'yii\grid\ActionColumn',
                'template'=>'{view} {update}',
            ],
        ],
    ]); ?>
</div>
