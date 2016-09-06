<?php
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title="Пользователи";
$this->params['breadcrumbs'][] = $this->title;
//foreach ($users as $user){
//    $user=array_merge($user,['url'=>Url::to('users/places',['id'=>$user->id])]);
//}
    
$provider = new ActiveDataProvider([
    'query' => $users,
    'pagination' => [
        'pageSize' => 10,
    ],
]);


echo "<span class='label label-info'>Нажмите на пользователя, чтобы увидеть разрешенные для него точки</span></br></br>";
echo GridView::widget([
    'dataProvider' => $provider,
    'columns' => [
        ['attribute' => 'id',
        'format' => 'text',
        'label' => 'Код'],
        [
            'label'=>'Логин',
            'format'=>'raw',
            'value'=>function($data){
            return Html::a(
                $data->name,
                Url::to(['/users/places','id'=>$data->id])
            );
    }
            
            ],
        ['attribute' => 'createTime',
        'format' => 'datetime',
        'label' => 'Дата регистрации'],
        // ...
    ],
    'layout'=>'{errors}{items}{pager}'               
]);
