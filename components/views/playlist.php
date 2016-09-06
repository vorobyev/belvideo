<?php
use kartik\sortable\Sortable;
use app\models\FilePlace;
use app\models\PlayLists;
use app\models\Place;
use app\models\VideoBlocks;
use kartik\checkbox\CheckboxX;
use yii\bootstrap\Html;
use yii\helpers\Url;

$filesPlace = FilePlace::find()->where(['placeId'=>$placeId])->all();
$videoBlocks = VideoBlocks::find()->where(['placeId'=>$placeId])->all();
$playList = PlayLists::find()->where(['placeId'=>$placeId])->all();

$places_obj=[];
        
$new_file = Yii::$app->request->get('fid');


foreach ($filesPlace as $place) {
    $span1 = ($new_file == (string)$place->file->id)?"<span style='color:#0000FF'>":"";
    $span2 = ($new_file == (string)$place->file->id)?"</span>":"";
    $places_obj = array_merge($places_obj,[
        ['content'=>""
        . "<div class='files-user'><table style='width:100%;'><tr><td style='width:78%; word-break: break-all;'><div class='file-name' id='filename".$place->file->id."'>".$span1.$place->file->userId."_".$place->file->fileName.$span2."</div><div class='file-info' id='fileinfo".$place->file->id."'>".$place->file->size." ".$place->file->duration."</div></td><td style='text-align:center'>"
        .Html::a("<span class='glyphicon glyphicon-eye-open' style='color:blue'></span> ", Url::toRoute(['files/view', 'href' => $place->file->href,'userId'=>$place->file->userId,'files'=>'true']),  [
                    'title' => Yii::t('app', 'Просмотреть видео'),'target'=>'_blank']). Html::button('>>',['onclick'=>'addToPL(this.id);','id'=>'btn'.$place->file->id."_"."0",'class'=>'btn btn-primary btn-my'])
            ."</td></tr></table></div>",
        'options'=>['id'=>'file'.$place->file->id]]
        ]);
}
foreach ($videoBlocks as $place) {
    $places_obj = array_merge($places_obj,[
    ['content'=>""
        . "<div class='files-blocks'><table style='width:100%;'><tr><td style='width:78%; word-break: break-all;'><div class='file-name' id='filename".$place->file->id."'>".$place->file->fileName."</div><div class='file-info' id='fileinfo".$place->file->id."'>".$place->file->size." ".$place->file->duration."</div></td><td style='text-align:center'>"
        .Html::a("<span class='glyphicon glyphicon-eye-open' style='color:blue'></span> ", Url::toRoute(['files/view', 'href' => $place->file->href,'userId'=>$place->file->userId,'files'=>'true']),  [
                    'title' => Yii::t('app', 'Просмотреть видео'),'target'=>'_blank']). Html::button('>>',['onclick'=>'addToPL(this.id);','id'=>'btn'.$place->file->id."_"."1",'class'=>'btn btn-primary btn-my'])
            ."</td></tr></table></div>",
        'options'=>['id'=>'file'.$place->file->id]]]);
}

$playlist_obj=[];

foreach ($playList as $place) {
    if ($place->file->videoBlock == '1'){
        $playlist_obj = array_merge($playlist_obj,[['content'=>"<div class='files-blocks'><table style='width:100%;'><tr><td style='width:90%; word-break: break-all;'><div class='file-name'>".$place->file->fileName."</div><div class='file-info'>".$place->file->size." ".$place->file->duration."</div></div></td><td style='text-align:center'>". Html::button('x',['onclick'=>'delPL(this);','id'=>'btn'.$place->file->id."_"."1",'class'=>'btn btn-danger btn-my'])
            ."</td></tr></table></div>",
            'options'=>['id'=>'file'.$place->file->id]]]);
    } else {
        $span1 = ($new_file == (string)$place->file->id)?"<span style='color:#0000FF'>":"";
        $span2 = ($new_file == (string)$place->file->id)?"</span>":"";
        $playlist_obj = array_merge($playlist_obj,[['content'=>"<div class='files-user'><table style='width:100%;'><tr><td style='width:90%; word-break: break-all;'><div class='file-name'>".$span1.$place->file->userId."_".$place->file->fileName.$span2."</div><div class='file-info'>".$place->file->size." ".$place->file->duration."</div></div></td><td style='text-align:center'>". Html::button('x',['onclick'=>'delPL(this);','id'=>'btn'.$place->file->id."_"."0",'class'=>'btn btn-danger btn-my'])
            ."</td></tr></table></div>",'options'=>['id'=>'file'.$place->file->id]]]);
    }   
}
?>
<div style="text-align:center">
<div style="display:inline-block;background-color:#5CCCCC;width:10px;height:10px; color:#5CCCCC"></div> - видео-блоки
<div style="display:inline-block;background-color:#FFB273;width:10px;height:10px; color:#FFB273"></div> - видео-файлы пользователей
<?php
if (isset($new_file)) {
   echo '<div style="display:inline-block;background-color:#0000FF;width:10px;height:10px; color:#0000FF"></div> - новый файл пользователя';
}
?>
</div>
<div style="display:inline;position:absolute; float:left; margin-left:100px">

    
<?php
echo '<h4>Видео-файлы точки </h4>';
echo Sortable::widget([
    'options'=>[
        'id'=>'filesFolder',
        'class'=>'filesFolder'
    ],
    'disabled'=>true,
    'items'=>$places_obj,
    'connected'=>false
]);

?>
    </div>
 <div style="margin-left:450px">   
    <?php
echo '<h4>Плейлист </h4>';
echo Sortable::widget([
    'options'=>[
        'id'=>'filesPlaylist',
        'class'=>'filesPlaylist'
    ],
    'items'=>$playlist_obj,
    'connected'=>true

]);

echo Html::button('Сохранить плейлист', ['id'=>'save-playlist','class'=>'btn btn-primary','style'=>'margin:0 auto', 'onclick'=>'savePlaylist('.Yii::$app->request->get('playlist').');']);
?>
     <br/><br/>
     <div class = 'alert alert-info' style='display:none' id='alert-playlist'>Плейлист успешно сохранен!</div>
</div>