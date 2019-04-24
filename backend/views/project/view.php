<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Project */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="project-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'user.login',
            [
                    'attribute' => 'name',
                    'label' => 'Project Name',
            ],
            'date',
            'file',
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
                'attribute' => 'secret',
                'format'    => 'html',
                'value'     => function ($model) {
                    $private = Yii::$app->user->identity->getId() == $model->user_id ?? false;
                    $url = Html::a( Url::base('http') .
                        Yii::$app->urlManager->createUrl(['/timing','id'=>$model->secret]), ['timing', 'id' => $model->secret]);
                    if ($model->secret) {
                        return $private ? $url : 'protected';
                    }
                }
            ],
            'fullPath',
        ],
    ]) ?>
</div>
