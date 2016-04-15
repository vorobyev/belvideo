<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Place */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Точки', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="place-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы действительно хотите удалить эту запись?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [

            ['attribute' => 'id',
            'format' => 'text',
            'label' => 'Код'],
            ['attribute' => 'name',
            'format' => 'text',
            'label' => 'Наименование'],
            ['attribute' => 'address',
            'format' => 'text',
            'label' => 'Адрес'],
        ],
    ]) ?>

</div>
