<?php

use common\models\Project;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ProjectSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Projects';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Project', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?= Html::beginForm(['project-selected'],'post');?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'columns'      => [
            [
                'class' => 'yii\grid\CheckboxColumn', 'checkboxOptions' => function ($model) {
                return ['value' => $model->id];
            },
            ],
            //'id',
            [
                'attribute' => 'user.login',
                'filter'    => ArrayHelper::map(Project::find()->all(), 'user.login', 'user.login'),
            ],
            'name',
            'date',
            'tree',
            [
                'attribute' => 'link',
                'format'    => 'html',
                'value'     => function ($model) {
                    $private = Yii::$app->user->identity->getId() == $model->user_id ?? false;
                    if ($model->secret) {
                        return $private ? $model->link : 'protected';
                    } else {
                        return $model->link;
                    }
                }
            ],
            [
                'attribute' => 'status',
                'value'     => function ($model) {
                    return Project::$status[$model->status];
                },
                'filter'    => Project::$status,
            ],

            //'parent_id',

            ['class' => 'yii\grid\ActionColumn', 'template' => '{view}'],
            ['class' => 'yii\grid\ActionColumn', 'template' => '{update}'],
            ['class' => 'yii\grid\ActionColumn', 'template' => '{delete}'],
        ],
    ]); ?>

    <?=Html::submitButton('Send', ['class' => 'btn btn-primary']);?>

    <?= Html::endForm();?>

</div>
