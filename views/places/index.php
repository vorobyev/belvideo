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
    <span class='label label-danger'>ВНИМАНИЕ! При удалении точки удалятся все записи о ней в базе данных, а также связанные с ней файлы и плейлист!</span></br></br>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Создать точку', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'emptyText'=>"Точки не найдены...",
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

            [
                'class' => 'yii\grid\ActionColumn',
                //для изменения подсказок и сообщения об удалении на русский язык переобъявляем buttons
                'buttons' => [
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                            'title' => Yii::t('yii', 'Удалить'),
                            'data-confirm' => 'Внимание! Процесс удаления необратим. Вы действительно хотите удалить точку?',
                            'data-method' => 'post',
                            'data-pjax' => '0',
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, [
                            'title' => Yii::t('yii', 'Изменить'),
                            'data-method' => 'post',
                            'data-pjax' => '0',
                        ]);
                    },
                    'view' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, [
                            'title' => Yii::t('yii', 'Просмотр'),
                            'data-method' => 'post',
                            'data-pjax' => '0',
                        ]);
                    },
                ],
            ],
        ],
        'layout'=>'{errors}{items}{pager}'
    ]); ?>

</div>
