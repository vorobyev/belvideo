<?php

use wbraganca\videojs\VideoJsWidget;
use yii\helpers\Html;

$this->title = $video->fileName;
$this->params['breadcrumbs'][] = ['label' => 'Файлы', 'url' => ['site/files']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>
<?php
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
                ['src' => 'files/pre-actions/'.Yii::$app->user->identity->id."/".$video->href.".".$video->ext, 'type' => 'video/mp4']
            ]
        ]
    ]);




//$hul2=$ffprobe->streams(Yii::$app->params['uploadPath'] ."/".Yii::$app->user->identity->id."/"."1.mp4")->videos()->first()->get('codec_name'); 


?>
    </div>