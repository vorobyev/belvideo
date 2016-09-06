<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use app\components\playlist;
use app\models\Place;
use yii\bootstrap\Modal;

$this->title = 'Плэйлисты';
$this->params['breadcrumbs'][] = $this->title;

Pjax::begin(); 
echo "<span class='label label-info'>Нажмите на точку, чтобы увидеть соответствующий ей плейлист</span></br></br>";
echo GridView::widget([
    'dataProvider' => $providerPlace,
    'layout'=>'{pager}{errors}{items}',
    'emptyText'=>"Точки не найдены...<br/>".Html::a('Перейти к списку точек',Url::toRoute(['places/index'])),
    'columns' => [
        [
            'format' => 'raw',
            'label' => 'Имя',
            'value' => function($data){
            return Html::a($data->name, ['files/playlists','playlist'=>$data->id],['id'=>'play'.$data->id]);
            }
        ],
        [        
            'attribute' => 'address',
            'format' => 'text',
            'label' => 'Адрес'
        ]

    ]
]);
     
        
 echo Html::a("", ['files/playlists'],['id'=>'play0','style'=>'display:none']);
 
if (Yii::$app->request->get('playlist')!=null) {
    $placeObj = Place::findOne(Yii::$app->request->get('playlist'));
    $placeName = $placeObj->name;
    $placeAddress = $placeObj->address;
} else {
    $placeName = "";
    $placeAddress = "";    
}


Modal::begin([
    'header' => '<h3 align=center>Плейлист точки '.$placeName.' <br/>('.$placeAddress.')</h3>',
    'options'=>['id'=>'modal-playlist'],
    'clientOptions'=>[
        'show'=>(Yii::$app->request->get('playlist')==null)?false:true
    ],
    'clientEvents'=>[
        'hidden.bs.modal'=>'function(){$("#play0").click();}'
    ],
    'size'=>'modal-lg'
]);

echo playlist::widget(['place'=>Yii::$app->request->get('playlist')]);

Modal::end();
Pjax::end(); 