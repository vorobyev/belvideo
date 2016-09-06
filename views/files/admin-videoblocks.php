<?php
use kartik\tabs\TabsX;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\popover\PopoverX;
use yii\grid\GridView;
use yii\bootstrap\Alert;
use kartik\checkbox\CheckboxX;
use app\models\FilePlace;
use app\models\UserRules;
use app\models\Place;
use app\models\User;
use app\models\PlayLists;
use yii\bootstrap\Modal;
use kartik\file\FileInput;

$this->title = 'Видео-блоки точек';
$this->params['breadcrumbs'][] = $this->title;

$contentFiles = "";
$contentFiles = $contentFiles.GridView::widget([
    'options'=>[
        'style'=>'width:100%'
    ],
    'dataProvider' => $providerFile,
    'layout'=>'{pager}{errors}{items}',
    'emptyText'=>"Файлы не найдены...",
    'columns' => [
        [        
            'attribute' => 'id',
            'format' => 'text',
            'label' => 'ID'
        ],
        [   
            'contentOptions'=>[
                'style'=>'word-break:break-all; max-width:200px;'
            ],      
            'attribute' => 'fileName',
            'format' => 'raw',
            'label' => 'Имя файла',
            'value' => function($data){
                $content=Html::input('text','fileName'.$data->id, explode(".",$data->fileName)[0],['id'=>'fileName'.$data->id]);
                $content=$content." .".$data->ext."<br/><br/>".Alert::widget([
                'options' => [
                        'class' => 'alert-warning',
                        'id'=>'alert'.$data->id,
                        'style'=>'display:none'
                     ],
                    'body' => 'Недопустимое имя файла',
                    'closeButton'=>false
                ]);
                return PopoverX::widget([
                    'id'=>'detail'.$data->id,
                    'header' => 'Изменение имени файла',
                    'placement' => PopoverX::ALIGN_RIGHT,
                    'content' => $content,
                    'footer' => Html::button('Изменить', ['class'=>'btn btn-sm btn-primary','onclick'=>'changeFileName("'.$data->id.'");',]),
                    'toggleButton'=>['label'=>$data->fileName, 'style'=>'word-break:break-all; max-width:200px; white-space:pre-line; text-align:left', 'class'=>'myBtn btn btn-default','id'=>'btn'.$data->id,'onClick'=>'return false;']
                ]);
            }
        ],
        [        
            'attribute' => 'size',
            'format' => 'text',
            'label' => 'Размер'
        ],
        [        
            'attribute' => 'duration',
            'format' => 'text',
            'label' => 'Длительность'
        ],       
        [        
            'attribute' => 'uploadDate',
            'format' => ['datetime','php:d.m.Y G:i:s'],
            'label' => 'Дата загрузки'
        ], 
        [        
            'attribute' => 'createDate',
            'format' => ['datetime','php:d.m.Y G:i:s'],
            'label' => 'Дата посл. изменения'
        ], 
        [        
            'format' => 'raw',
            'label' => 'Точки',
            'value' => function($data){
                $content = "";
                //$FilePlace = FilePlace::find()->where(['userId'=>Yii::$app->user->identity->id]);
                $places = Place::find()->all(); //тут
                if ($places == null) {
                    $content = "Точки отсутствуют.<br/> ".Html::a('Добавить точки',Url::toRoute(['places/index']));
                }               
                
                foreach ($places as $place) {
                    $playRec = PlayLists::find()->where(['and','fileId='.$data->id,'placeId='.$place->id])->one();
                    if ($playRec == null) {
                        $str = "<span class='label label-success' style='display:none;' id='al".(string)$place->id."_".(string)$data->id."_1"."'>В плэйлисте</span>";
                        $str_id = "null";
                    } else {
                        $str = "<span class='label label-success' id='al".(string)$place->id."_".(string)$data->id."_1"."'>В плэйлисте</span>";
                        $str_id = '1';
                    }
                    $tr_id = "tr".(string)$place->id."_".(string)$data->id."_0";
                    $name = "cell"."admin_".(string)$place->id."_".(string)$data->id."_".$str_id;
                    $content = $content."<tr id='".$tr_id."'><td style='text-align:center'>".CheckboxX::widget([
                    'name'=>$name,  
                    'options'=>['id'=>$name,'class'=>'cell'.(string)$data->id],
                    'pluginOptions'=>['threeState'=>false],
                    'pluginEvents'=>['change'=>'function() { changeColorOnCheck(this.id); }'],
                    'value'=>($playRec == null) ? 0 : 1
                ])."</td><td><label class='cbx-label' for='".$name."'>"."<div class='metaInfo' style='display:block; word-break:break-all; max-width:250px;'><b>".$place->name."</b></div>"."<div style='display:block;word-break:break-all; max-width:250px;' class='metaInfoData'><i>".$place->address."</i></div></label></td><td style='text-align:center'>".$str."</td></tr>";
                }

                return PopoverX::widget([
                    'pluginOptions'=>[
                        'closeOtherPopovers'=>true
                    ],
                    'header' => "<b>Точки</b>",
                    'placement' => PopoverX::ALIGN_BOTTOM_RIGHT,
                    'size'=>PopoverX::SIZE_LARGE,
                    'content' => "<div class='wrapper-table'><table class='place-files'><tbody>".$content."</tbody></table></div>",
                    'footer' =>Alert::widget([
                'options' => [
                        'class' => 'alert-success',
                        'id'=>'alertPlace'.$data->id,
                        'style'=>'display:none; position:relative; float:left; width:350px;text-align:left; margin:3px;'
                     ],
                    'body' => ''
                ]).Html::button('Сохранить', ['disabled'=>(($places == null)?true:false),'class'=>'btn btn-sm btn-primary','id'=>'btn'.(string)$data->id,'style'=>'margin:10px', 'onclick'=>'changeVideoblocks(this.id);']),
                    'toggleButton'=>['label'=>"Список", 'class'=>'btn btn-success','id'=>'btn'.$data->id,'style'=>'margin:0 auto; display:block;']
                ]);
            }
        ],
        [        
            'format' => 'raw',
            'label' => 'Детальная информация',
            'value' => function($data){
                return PopoverX::widget([
                    'header' => "<b>Метаданные</b>",
                    'placement' => PopoverX::ALIGN_BOTTOM_RIGHT,
                    'size'=>PopoverX::SIZE_LARGE,
                    'content' => TabsX::widget([
                        'items'=>[
                            [
                                'label'=>'Видео',
                                'content'=>$data->videoInfo
                            ],
                            [
                                'label'=>'Аудио',
                                'content'=>$data->audioInfo
                            ],
                        ],
                        'position'=>TabsX::POS_LEFT,
                        'encodeLabels'=>false,
                        'enableStickyTabs'=>true
                    ]),
                    'toggleButton'=>['label'=>"Подробнее", 'class'=>'btn btn-info','id'=>'btn'.$data->id,'onClick'=>'return false;','style'=>'margin:0 auto; display:block;']
                ]);
            }
        ], 
        [        
            'format' => 'raw',
            'label' => '',
            'value' => function($data){
                 return Html::button('<span style="font-size:20pt; color:blue" class="glyphicon glyphicon-transfer" ></span>',['class'=>'myBtn btn btn-default','title'=>($data->isCoding == NULL)?"Перекодировать":"Перекодировано","onClick"=>"coding(".$data->id.")","disabled"=>($data->isCoding == NULL)?false:true]);
            }
        ], 
        //glyphicon glyphicon-trash   pencil
        //['class' => 'yii\grid\ActionColumn', 'template' => '{view} {update} {delete}']
        [
  'class' => 'yii\grid\ActionColumn',
  'template' => '<div style="text-align:center">{new_action4}&nbsp {new_action3} {new_action2} {new_action1}</div>',
  'buttons' => [
     'new_action2' => function ($url, $model) {
        return Html::a('<span class="glyphicon glyphicon-pencil"></span>','',[
                    'onClick'=>'$("#btn'.$model->id.'").click(); return false;',
                    'title' => Yii::t('app', 'Изменить имя файла')
                ]);

    },
    'new_action1' => function ($url, $model) {
        return Html::a('<span class="glyphicon glyphicon-trash"></span>', "", [
                    'title' => Yii::t('app', 'Удалить'),
                    'onClick'=>"if (confirm(\"Вы действительно хотите удалить этот файл?\")) {"
            . 'deleteRowFiles("'.$url.'"); '
            . '} else {return false;}'
        ]);
    },
    'new_action3' => function ($url, $model) {
        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', Url::toRoute(['files/view', 'href' => $model->href,'files'=>'true','userId'=>$model->userId]), [
                    'title' => Yii::t('app', 'Просмотреть видео')
        ]);
    },
    'new_action4' => function ($url, $model) {
        return Html::a('<span class="glyphicon glyphicon-download-alt"></span>', Url::toRoute(['files/down', 'href' => $model->href,'userId'=>$model->userId]), [
                    'title' => Yii::t('app', 'Скачать файл')
        ]);
    }

  ],
          
          
  'urlCreator' => function ($action, $model, $key, $index) {
    if ($action === 'new_action1') {
        $url = $model->id;
        return $url;
    }
  }
],
        
    ],
]);



Modal::begin([
    'header' => '<h2>Конвертация видео-файла на сервере</h2>',
    'options'=>['id'=>'modal-convert'],
    'size'=>'modal-lg',
    'clientOptions'=>[
        'backdrop'=>'static',
        'show'=>false,
        'keyboard'=>false
    ]
]);
echo '<div style="text-align:center"><br/><br/><img src="img/file-loader.gif"></div><br/><br/>';
echo '<div style="text-align:left">CPU Usage (%): <div id="text-CPU" style=" display:inline">0</div></div><br/><br/>';
echo '<h4 style="text-align:center;">Командная строка сервера (вывод):</h4><br/>';
echo '<div id="text-load" style="text-align:center; background-color:black;color:white;">Инициализация</div><br/><br/>';
echo "<div style='text-align:center'><span style='display:inline;text-align:center;' class='label label-warning'>Не закрывайте окно, пока идет конвертация</span></div></br></br>";
Modal::end();

    $contentFiles = $contentFiles."<h3>Загрузка файлов</h3>"
    .FileInput::widget([
        'model' => $file,
        'attribute' => 'profile_pic',
        'options'=>[
            'multiple'=>false
        ],
        'language' => 'ru',
        'pluginOptions' => [
            'uploadUrl' => Url::to(['files/add2','videoBlock'=>'1']),
            'showPreview'=>true,
            //'allowedFileTypes'=>['video']   
            'allowedFileExtentions'=>['flv', 'avi', 'mov','mp4','mpg','mpeg','mpe','mp2v','m2v','m2s','wmv','qt','3gp','asf','rm','mkv']
        ],
        'pluginEvents'=> [
            'filebatchpreupload'=>"function(e, data) {statusLoad=filePreLoad(data,'".Yii::$app->user->identity->id."'); return statusLoad;}",
            'filecustomerror'=>"function(event, params, msg){}",
            'fileuploaded'=>"function(event, data, previewId, index) {location.reload();}" 
        ]
    ]);



Modal::begin([
    'header' => '<h2>Плейлист точки такой-то2...</h2>',
    'options'=>['id'=>'modal-playlist'],
    'size'=>'modal-lg'
]);

echo "1) Настройка плейлиста. <br/>2)Отправка сообщения на е-мейл пользователя о подтверждении";

Modal::end();

Modal::begin([
    'header' => '<h2>Конвертация видео-файла на сервере</h2>',
    'options'=>['id'=>'modal-convert'],
    'size'=>'modal-lg',
    'clientOptions'=>[
        'backdrop'=>'static',
        'show'=>false,
        'keyboard'=>false
    ]
]);
echo '<div style="text-align:center"><br/><br/><img src="img/file-loader.gif"></div><br/><br/><br/>';
echo '<h4 style="text-align:center;">Командная строка сервера (вывод):</h4><br/><br/>';
echo '<div id="text-load" style="text-align:center; background-color:black;color:white;">Инициализация</div><br/><br/>';
echo '<div id="text-CPU" style=" display:inline">0</div><br/><br/>';
echo "<span style='display:block;text-align:center;' class='label label-warning'>Не закрывайте окно, пока идет конвертация</span></br></br>";
Modal::end();


Modal::begin([
    'header' => '<h2>Сообщение о причине отказа</h2>',
    'options'=>['id'=>'modal-not_agree'],
    'size'=>'modal-lg'
]);

echo "Отправка на е-мейл пользователя о причине отказа размещения видео в точке...";

Modal::end();



echo $contentFiles;
