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
use yii\bootstrap\Modal;
use kartik\file\FileInput;
use yii\widgets\Pjax;
use app\components\playlist;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;


$filesFlag = Yii::$app->request->get()['files'];
if ($filesFlag=='true') {
    $this->title = 'Файлы';
} else {
    $this->title = 'Заявки';
}
$this->params['breadcrumbs'][] = $this->title;

Modal::begin([
    'header' => '<h2>Выбор пользователя</h2>',
    'options'=>['id'=>'modal-users'],
    'size'=>'modal-lg'
]);
echo GridView::widget([
    'dataProvider' => $providerUsers,
    'layout'=>'{pager}{errors}{items}',
    'emptyText'=>"Пользователи не найдены...",
    'options'=> [
        'style'=>'max-height:500px; overflow-y:scroll;'
    ],
    'columns' => [
        [        
            'attribute' => 'active',
            'format' => 'raw',
            'label' => '',
            'value' => function($data){
                $content=Html::a('Выбрать', Url::to(["files/admin",'userId'=>$data->id,'files'=>'true'],true),['class'=>'btn btn-info']);
                return $content;
            }
        ],
        [        
            'attribute' => 'id',
            'format' => 'text',
            'label' => 'Id'
        ],
        [        
            'attribute' => 'name',
            'format' => 'text',
            'label' => 'Логин'
        ],
        [        
            'attribute' => 'email',
            'format' => 'text',
            'label' => 'E-mail'
        ],       
        [        
            'attribute' => 'createTime',
            'format' => 'datetime',
            'label' => 'Дата регистрации'
        ],       
        [        
            'attribute' => 'active',
            'format' => 'raw',
            'label' => 'Статус',
            'contentOptions'=>['style'=>'text-align:center'],
            'value' => function($data){
                $status = ($data->active == '0')? "<span class='label label-warning'>Не активирован</span>":"<span class='label label-success'>Активирован</span>";
                return $status;
            }    
        ]
      

        
    ],
]);
echo Html::a('Выбрать всех', Url::to(["files/admin",'userId'=>'all','files'=>'true'],true),['class'=>'btn btn-info','style'=>'display:block; margin:0 auto;']);        
        
Modal::end();

$contentFiles = Html::button(($currentUser == 'all') ? "Отбор по пользователям: Все":"Отбор по пользователям: ".$currentUser->name,['data-toggle'=>'modal','data-target'=>'#modal-users','class'=>'btn btn-primary','style'=>'display:block; margin:0 auto;'])."<br/>";


$contentFiles = $contentFiles.GridView::widget([
    'dataProvider' => $providerFile,
    'layout'=>'{pager}{errors}{items}',
    'emptyText'=>"Файлы не найдены...",
    'options'=>[
        'style'=>'width:100%'
    ],
    'columns' => [
        [        
            'attribute' => 'id',
            'format' => 'text',
            'label' => 'ID'
        ],
        [        
            'contentOptions'=>[
                'style'=>'word-break:break-all; max-width:250px;'
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
                    'toggleButton'=>['label'=>$data->fileName, 'style'=>'word-break:break-all; max-width:250px; white-space:pre-line; text-align:left', 'class'=>'myBtn btn btn-default','id'=>'btn'.$data->id,'onClick'=>'return false;']
                ]);
            }
        ],
        [
            'contentOptions'=>[
                'style'=>'word-break:break-all; max-width:250px;'
            ],
            'format' => 'raw',
            'label' => 'Пользователь',
            'value' => function($data){
                return User::find()->where(['id'=>$data->userId])->one()['name'];
            },
            'visible'=>( $currentUser == 'all') ? true : false 
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
            'format' => 'raw',
            'label' => 'Точки',
            'value' => function($data){
                $content = "";
                //$FilePlace = FilePlace::find()->where(['userId'=>Yii::$app->user->identity->id]);
                $UserRules = UserRules::find()->where(['userId'=>$data->userId])->all(); 
                if ($UserRules == null) {
                    $content = "У пользователя нет доступных точек.<br/> ".Html::a('Добавить точки пользователю',Url::toRoute(['users/places', 'id' => $data->userId]));
                }
                foreach ($UserRules as $place) {
                    $FileUser = FilePlace::find()->where(['and','userId='.$data->userId,'placeId='.$place->placeId,'fileId='.$data->id])->one();
                    if ($FileUser == null) {
                        $str = "<span class='label label-warning' style = 'display:none' id='al".(string)$place->placeId."_".(string)$data->id."_0"."'>Не подтверждена</span>";
                        $str_id = "null";
                    } else {
                        $str = ($FileUser->confirm == 1) ? "<span class='label label-success' id='al".(string)$place->placeId."_".(string)$data->id."_1"."'>Подтверждена</span><span class='label label-warning' style = 'display:none' id='al".(string)$place->placeId."_".(string)$data->id."_0"."'>Не подтверждена</span>":"<span class='label label-warning' id='al".(string)$place->placeId."_".(string)$data->id."_0"."'>Не подтверждена</span>";
                        $str_id = (string)$FileUser->confirm;
                    }
                    $placeUser = Place::find()->where(['id'=>$place->placeId])->one();
                    $tr_id = "tr".(string)$place->placeId."_".(string)$data->id."_0";
                    $name = "cell".(string)$place->userId."_".(string)$place->placeId."_".(string)$data->id."_".$str_id;
                    $content = $content."<tr id='".$tr_id."'><td style='text-align:center'>".CheckboxX::widget([
                    'name'=>$name,  
                    'options'=>['id'=>$name,'class'=>'cell'.(string)$data->id],
                    'pluginOptions'=>['threeState'=>false],
                    'pluginEvents'=>['change'=>'function() { changeColorOnCheck(this.id); }'],
                    'value'=>($FileUser == null) ? 0 : 1
                ])."</td><td><label class='cbx-label' for='".$name."'>"."<div class='metaInfo' style='display:block; word-break:break-all; max-width:250px;'><b>".$placeUser->name."</b></div>"."<div style='word-break:break-all; max-width:250px;' class='metaInfoData'><i>".$placeUser->address."</i></div></label></td><td style='text-align:center'>".$str."</td></tr>";
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
                ]).Html::button('Сохранить', ['disabled'=>(($UserRules == null)?true:false),'class'=>'btn btn-sm btn-primary','id'=>'btn'.(string)$data->id,'style'=>'margin:10px', 'onclick'=>'changeUserFiles(this.id,1);']),
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

if ($currentUser != 'all') {
    $contentFiles = $contentFiles."<h3>Загрузка файлов для пользователя ".$currentUser->name."</h3>"
    .FileInput::widget([
        'model' => $file,
        'attribute' => 'profile_pic',
        'options'=>[
            'multiple'=>false
        ],
        'language' => 'ru',
        'pluginOptions' => [
            'uploadUrl' => Url::to(['files/add2','userId'=>$currentUser->id]),
            'showPreview'=>true,
            //'allowedFileTypes'=>['video'] 
            'allowedFileExtentions'=>['flv', 'avi', 'mov','mp4','mpg','mpeg','mpe','mp2v','m2v','m2s','wmv','qt','3gp','asf','rm','mkv']
        ],
        'pluginEvents'=> [
            'filebatchpreupload'=>"function(e, data) {statusLoad=filePreLoad(data,'".$currentUser->id."'); return statusLoad;}",
            'filecustomerror'=>"function(event, params, msg){}",
            'fileuploaded'=>"function(event, data, previewId, index) {location.reload();}" 
        ]
    ]);
} else {
    $contentFiles = $contentFiles."<h3>Загрузка файлов</h3><span class='label label-warning'>Загрузка файлов доступна только при отборе по конкретному пользователю!</span></br></br>"
    .FileInput::widget([
        'model' => $file,
        'attribute' => 'profile_pic',
        'disabled'=>'true',
        'options'=>[
            'multiple'=>false
        ],
        'language' => 'ru',
        'pluginOptions' => [
            'uploadUrl' => Url::to(['files/add2']),
            'allowedFileTypes'=>['video'],
            'showPreview'=>false,
        ]
    ]);
}


 Pjax::begin(); 
 $playlists = Place::find()->all();
 foreach ($playlists as $playlist){
    echo Html::a("", ['files/admin','userId'=>'all','files'=>'false','playlist'=>$playlist->id],['id'=>'play'.$playlist->id,'style'=>'display:none']);
 }
 echo Html::a("", ['files/admin','userId'=>'all','files'=>'false'],['id'=>'play0','style'=>'display:none']);
 
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
echo '<div style="text-align:left">CPU Usage (%): <div id="text-CPU" style=" display:inline">0</div></div><br/><br/>';
echo '<h4 style="text-align:center;">Командная строка сервера (вывод):</h4><br/>';
echo '<div id="text-load" style="text-align:center; background-color:black;color:white;">Инициализация</div><br/><br/>';
echo "<div style='text-align:center'><span style='display:inline;text-align:center;' class='label label-warning'>Не закрывайте окно, пока идет конвертация</span></div></br></br>";
Modal::end();

Modal::begin([
    'header' => '<h2>Сообщение о причине отказа</h2>',
    'options'=>['id'=>'modal-not_agree'],
    'size'=>'modal-lg'
]);
echo '<div style="margin:0 auto; display:block; text-align:center">';
echo Html::textarea('mail_abort','',['id'=>'mail-abort','cols'=>'100','rows'=>'7','style'=>'display:block;margin:0 auto;']);
echo '<br/>'.Html::button('',['id'=>'btnsend','class'=>'btn btn-success','onclick'=>'sendMailToUser(this.id);']).' '.Html::button('Закрыть и не отправлять',['id'=>'btnnotsend','class'=>'btn btn-primary','onclick'=>"$('#modal-not_agree').modal('hide');"]);
echo "</div>";
Modal::end();

$contentProposal = GridView::widget([
    'options'=>[
        'style'=>'width:100%'
    ],
    'dataProvider' => $proposal,
    'layout'=>'{pager}{errors}{items}',
    'emptyText'=>"Заявки не найдены...",
    'columns' => [
         [ 
            'format' => 'raw',
            'label' => 'Действия',
            'value' => function($data){
                
                //return Html::button("Подтвердить",['data-toggle'=>'modal','data-target'=>'#modal-playlist','class'=>'btn btn-primary','style'=>'display:block; margin:0 auto; width:100%','onClick'=>'addToPlaylist(this.id);','id'=>'btn'.$data->file->id."_".$data->place->id])."".Html::button("Отказать",['data-toggle'=>'modal','data-target'=>'#modal-not_agree','class'=>'btn btn-danger','style'=>'display:block; margin:0 auto;width:100%;','onClick'=>'delProp(this.id);','id'=>'btncancel'.$data->file->id."_".$data->place->id]);
                return Html::button("Подтвердить",['class'=>'btn btn-primary','style'=>'display:block; margin:0 auto; width:100%','onClick'=>'addToPlaylist(this.id,"'.$data->place->id.'","'.$data->file->id.'"); ','id'=>'btn'.$data->file->id."_".$data->place->id])."".Html::button("Отказать",['class'=>'btn btn-danger','style'=>'display:block; margin:0 auto;width:100%;','onClick'=>'delProp(this.id);','id'=>'btncancel'.$data->file->id."_".$data->place->id]);
            }
        ],       
        [ 
            'format' => 'raw',
            'label' => 'Пользователь',
            'value' => function($data){
                return '<div style="word-break:break-all; max-width:200px;" id="emailf'.$data->file->id.'">'.$data->user->name."</div>";
            }
        ],
        [ 
            'format' => 'raw',
            'label' => 'ID',
            'value' => function($data){
                return $data->file->id;
            }
        ],
        [ 
            'contentOptions'=>[
                'style'=>'word-break:break-all; max-width:250px;'
            ],
            'format' => 'raw',
            'label' => 'Имя файла',
            'value' => function($data){
                return '<div style="word-break:break-all; max-width:250px;" id="namef'.$data->file->id.'">'.$data->file->fileName."</div>";
            }
        ],
        [ 
            'format' => 'raw',
            'label' => 'Размер',
            'value' => function($data){
                return $data->file->size;
            }
        ],
        [ 
            'format' => 'raw',
            'label' => 'Длительность',
            'value' => function($data){
                return $data->file->duration;
            }
        ],
        [ 
            'format' => 'raw',
            'label' => 'Дата загрузки',
            'value' => function($data){
                return $data->file->uploadDate;
            }
        ],               
        [ 
            'format' => 'raw',
            'label' => 'Точка',
            'value' => function($data){
                return '<div style="word-break:break-all; max-width:250px;" id="placef'.$data->place->id.'">'.$data->place->name."</div>";
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
                                'content'=>$data->file->videoInfo
                            ],
                            [
                                'label'=>'Аудио',
                                'content'=>$data->file->audioInfo
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
            'class' => 'yii\grid\ActionColumn',
            'template' => '<div style="text-align:center"> {new_action3}</div>',
            'buttons' => [
              'new_action3' => function ($url, $model) {
                  return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', Url::toRoute(['files/view', 'href' => $model->file->href,'files'=>'false','userId'=>$model->file->userId]), [
                              'title' => Yii::t('app', 'Просмотреть видео')
                  ]);
              }

            ],
            'urlCreator' => function ($action, $model, $key, $index) {
              if ($action === 'new_action1') {
                  $url = $model->file->id;
                  return $url;
              }
            }
        ],
    ]
]);

//echo TabsX::widget([
//                        'items'=>[
//                            [
//                                'label'=>'Файлы',
//                                'content'=>$contentFiles,
//                                'active'=>($filesFlag == 'true')?true:false,
//                                'options'=>['id'=>'Tab1']
//                            ],
//                            [
//                                'label'=>'Заявки('.$count.")",
//                                'content'=>$contentProposal,
//                                'active'=>($filesFlag == 'true')?false:true,
//                                'options'=>['id'=>'Tab2']
//                            ],
//                        ]
//                ]);
//echo Yii::getAlias('@app');

   echo Nav::widget([
        'options' => ['class' => 'nav nav-tabs'],
        'items' => [
            ['label' => 'Файлы', 'url' => ['/files/admin','userId'=>'all','files'=>'true'],'active'=>($filesFlag=='true')?true:false],
            ['label' => 'Заявки('.$count.")", 'url' => ['/files/admin','userId'=>'all','files'=>'false'],'active'=>($filesFlag=='true')?false:true],
        ],
    ]);
 
   if ($filesFlag=='true') {
       echo $contentFiles;
   } else {
       echo $contentProposal;
   }