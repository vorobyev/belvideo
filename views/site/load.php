<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\file\FileInput;
use yii\helpers\Url;
use kartik\popover\PopoverX;
use yii\grid\GridView;
use yii\bootstrap\Alert;
use kartik\tabs\TabsX;
use kartik\checkbox\CheckboxX;
use app\models\FilePlace;
use app\models\UserRules;
use app\models\Place;


$this->title = 'Файлы';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?><?php echo " ".Html::a('<span class="glyphicon mygliph glyphicon-question-sign" style="font-size:18pt; color:#5bc0de"></span>', Url::toRoute(['helpers/view', 'id' => 'upload']), [
                    'title' => Yii::t('app', 'Информация по загрузке файлов')
        ]);?></h1>
<?php    
echo GridView::widget([
    'dataProvider' => $provider,
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
                $UserRules = UserRules::find()->where(['userId'=>Yii::$app->user->identity->id])->all(); 
                
                foreach ($UserRules as $place) {
                    $FileUser = FilePlace::find()->where(['and','userId='.Yii::$app->user->identity->id,'placeId='.$place->placeId,'fileId='.$data->id])->one();
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
                ])."</td><td><label class='cbx-label' for='".$name."'>"."<div class='metaInfo' style='display:block; word-break:break-all; max-width:250px;'><b>".$placeUser->name."</b></div>"."<div style='word-break:break-all; max-width:250px; display:block' class='metaInfoData'><i>".$placeUser->address."</i></div></label></td><td style='text-align:center'>".$str."</td></tr>";
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
                ]).Html::button('Сохранить', ['class'=>'btn btn-sm btn-primary','id'=>'btn'.(string)$data->id,'style'=>'margin:10px', 'onclick'=>'changeUserFiles(this.id,0);']),
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
        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', Url::toRoute(['files/view', 'href' => $model->href,'userId'=>$model->userId]), [
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


echo "<h3>Загрузка файлов:</h3>";
echo FileInput::widget([
    'model' => $file,
    'attribute' => 'profile_pic',
    'options'=>[
        'multiple'=>false
    ],
    'language' => 'ru',
    'pluginOptions' => [
        'uploadUrl' => Url::to(['files/add2']),
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



?>
    </div>
