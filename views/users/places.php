<?php
use kartik\sortable\Sortable;



$this->title="Точки пользователя ".$user->name;
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;


$places_obj=[];
         
foreach ($places as $place) {
    $places_obj = array_merge($places_obj,[['content'=>"<div class='place-name'>".$place->name."</div><div class='place-address'>".$place->address."</div>",'options'=>['id'=>'place'.$place->id]]]);
}
$placesNotIn_obj=[];
foreach ($placesNotIn as $place) {
    $placesNotIn_obj = array_merge($placesNotIn_obj,[['content'=>"<div class='place-name'>".$place->name."</div><div class='place-address'>".$place->address."</div>",'options'=>['id'=>'place'.$place->id]]]);
}

?>
<div class="places-container">
<div class="places-group-right">
    
<?php
echo '<h3>Точки пользователя </br>'.$user->name.'</h3>';
echo Sortable::widget([
    'options'=>[
        'id'=>'placesUser',
        'class'=>'placesUser'
    ],
    'connected'=>true,
    'items'=>$places_obj,
    'pluginEvents' => [
        'sortupdate' => 'function(ev, ui) { actionPlace(ui,ev);  }'
    ]
]);
?>
</div>    
<div class="places-group-left">
        <h3>Не задействованные</br>точки</h3>
<?php
echo Sortable::widget([
    'options'=>[
        'id'=>'placesOther',
        'class'=>'placesOther'
    ],
    'connected'=>true,
    'itemOptions'=>['class'=>'alert alert-warning'],
    'items'=>$placesNotIn_obj,
    'pluginEvents' => [
        'sortupdate' => 'function(ev, ui) { actionPlace(ui,ev);}'
    ]
]);

$this->registerJs("jQuery('#placesUser').unbind('sortupdate');"
." jQuery('#placesUser').sortable().one('sortupdate',function(ev, ui) { actionPlace(ui,ev);});"
." jQuery('#placesOther').unbind('sortupdate');"
." jQuery('#placesOther').sortable().one('sortupdate',function(ev, ui) { actionPlace(ui,ev);});");
?>
</div>
</div>