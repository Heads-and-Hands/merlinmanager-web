<?php

use yii\helpers\Html;
use yii\grid\GridView;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title =  Yii::t('content', 'Users');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?= Html::a( Yii::t('content', 'Create'), ['user/create'], ['class' => 'btn btn-success']) ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            //'id',
            'name',
            'login',
            //'password_hash',
            //'auth_key',
            'isAdmin:boolean',
            'quantity',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
