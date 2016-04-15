<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PlaceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Точки';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="place-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Создать точку', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [


            ['attribute' => 'id',
            'format' => 'text',
            'label' => 'Код'],
            ['attribute' => 'name',
            'format' => 'text',
            'label' => 'Наименование'],
            ['attribute' => 'address',
            'format' => 'text',
            'label' => 'Адрес'],

            ['class' => 'yii\grid\ActionColumn'],
        ],
        'layout'=>'{errors}{items}{pager}'
    ]); ?>

</div>
