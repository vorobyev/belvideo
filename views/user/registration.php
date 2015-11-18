<?php

use Yii;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;
use app\assets\MyAsset;
use yii\jui\AutoComplete;

$this->title = 'Регистрация нового пользователя';
MyAsset::register($this);
?>
   <?php $form = ActiveForm::begin([
        'id' => 'login-form']); 
   ?>

        <?= $form->field($model, 'name')->label('Логин') ?>
        <?= $form->field($model, 'email')->label('E-mail') ?>
        <?= $form->field($model, 'password')->passwordInput()->label('Пароль') ?>
        <?= $form->field($model, 'password_repeat')->passwordInput()->label('Повторите пароль') ?>
        <?= $form->field($model, 'captcha')->widget(Captcha::className(), [])->label('Введите код с картинки (англ.)') ?>
        <div id="containerRegion">
        <?= $form->field($model, 'region')->widget(AutoComplete::className(), [
        'clientOptions' => [],
        'options'=>['onInput'=>"getPopupAddress(this.id,this.value,1,'User', 'hiddenRegion','hiddenArea','hiddenCity','hiddenLocality','area','city','locality');",'onblur'=>"clearIfBlank(this.id,'User','hiddenRegion','area','city','locality')"]
        ])->label('Регион'); ?>
        <?= $form->field($model, 'hiddenRegion')->hiddenInput(['options'=>['id'=>'hiddenRegion'],'value'=>'00'])->label(false); ?>
        </div>
        <div id="containerArea" >
        <?= $form->field($model, 'area')->widget(AutoComplete::className(), [
        'clientOptions' => [],
        'options'=>['onInput'=>"getPopupAddress(this.id,this.value,2,'User','hiddenRegion','hiddenArea','hiddenCity','hiddenLocality',undefined,'city','locality');",'onblur'=>"clearIfBlank(this.id,'User','hiddenArea',undefined,'city','locality')"]
        ])->label('Район'); ?>
        <?= $form->field($model, 'hiddenArea')->hiddenInput(['options'=>['id'=>'hiddenArea'],'value'=>'000'])->label(false); ?>
        </div>
        <div id="containerCity" >
        <?= $form->field($model, 'city')->widget(AutoComplete::className(), [
        'clientOptions' => [],
        'options'=>['onInput'=>"getPopupAddress(this.id,this.value,3,'User','hiddenRegion','hiddenArea','hiddenCity','hiddenLocality',undefined,undefined,'locality');",'onblur'=>"clearIfBlank(this.id,'User','hiddenCity',undefined,undefined,'locality')"]
        ])->label('Город'); ?>
        <?= $form->field($model, 'hiddenCity')->hiddenInput(['options'=>['id'=>'hiddenCity'],'value'=>'000'])->label(false); ?>
        </div>
        <div id="containerLocality" >
        <?= $form->field($model, 'locality')->widget(AutoComplete::className(), [
        'clientOptions' => [],
        'options'=>['onInput'=>"getPopupAddress(this.id,this.value,4,'User','hiddenRegion','hiddenArea','hiddenCity','hiddenLocality');",'onblur'=>"clearIfBlank(this.id,'User','hiddenLocality')"]
        ])->label('Населенный пункт'); ?>
        <?= $form->field($model, 'hiddenLocality')->hiddenInput(['options'=>['id'=>'hiddenLocality'],'value'=>'000'])->label(false); ?>
        </div>
        <?= Html::submitButton('Зарегистироваться')?>
    
   <?php ActiveForm::end(); ?>
