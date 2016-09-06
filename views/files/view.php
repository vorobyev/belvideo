<?php

use wbraganca\videojs\VideoJsWidget;
use yii\helpers\Html;
$name = "Файлы";
if (Yii::$app->user->identity->admin === 1) {
    $filesFlag = Yii::$app->request->get()['files'];
    if ($filesFlag == 'false') {
       $name = "Заявки"; 
    }
}

$this->title = $video->fileName;
if ($video->videoBlock != 1) {
    $this->params['breadcrumbs'][] = ((Yii::$app->user->identity->admin === 1))?['label' => $name, 'url' => ['/files/admin','userId'=>'all','files'=>$filesFlag]]:['label' => 'Файлы', 'url' => ['site/files']];
} else {
    if (Yii::$app->user->identity->admin === 1){
        $this->params['breadcrumbs'][] = ['label' => 'Видео-блоки точек', 'url' => ['files/admin-videoblocks']];
    }
}

$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h2><?= Html::encode($this->title) ?></h2>
<?php
if ((Yii::$app->user->identity->admin === 1)&&($video->videoBlock != 1)) {
    echo "<h3>ID пользователя: ".$video->userId."</h3>";
} else if ((Yii::$app->user->identity->admin === 1)&&($video->videoBlock == 1))
{
    echo "<h3>Видеоблок: id=".$video->id."</h3>";
}
echo VideoJsWidget::widget([
        'options' => [
            'class' => 'video-js vjs-default-skin vjs-big-play-centered',
            'controls' => true,
            'preload' => 'auto',
            'width' => '1024',
            'height' => '768',
        ],
        'tags' => [
            'source' => [
                ['src' => 'files/pre-actions/'.$video->userId."/".$video->href.".".$video->ext, 'type' => 'video/mp4']
            ]
        ]
    ]);




//$hul2=$ffprobe->streams(Yii::$app->params['uploadPath'] ."/".Yii::$app->user->identity->id."/"."1.mp4")->videos()->first()->get('codec_name'); 


?>
    </div>