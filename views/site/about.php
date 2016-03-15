<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use kartik\file\FileInput;
use yii\helpers\Url;


$this->title = 'Файлы';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>
<?php    
//echo $form->field($file, 'avatar')->widget(FileInput::classname(), [
//    'options' => ['accept' => 'image/*'],
//]);

// With model & without ActiveForm
echo '<label class="control-label">Add Attachments</label>';
echo FileInput::widget([
    'model' => $file,
    'name'=>'file',
    'options' => ['multiple' => true,'accept' => 'image/*'],
    'pluginOptions' => [
        //'uploadUrl' => Url::to(['/site/file-upload']),
        'uploadExtraData' => [
            'album_id' => 20,
            'cat_id' => 'Nature'
        ],
    'maxFileCount' => 10
    ]
]);
?>
</div>
