<?php

use Yii;
use yii\bootstrap\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;
use app\components\Address;
use yii\widgets\MaskedInput;

$this->title = 'Регистрация нового пользователя';
?>
   <?php $form = ActiveForm::begin([
        'id' => 'registration-form']); 
   ?>

        <?= $form->field($model, 'name')->label('Логин') ?>
        <?= $form->field($model, 'email')->label('E-mail') ?>
        <?= $form->field($model, 'password')->passwordInput()->label('Пароль') ?>
        <?= $form->field($model, 'password_repeat')->passwordInput()->label('Повторите пароль') ?>
        <?= $form->field($model, 'promo')->widget(MaskedInput::className(),['mask'=>'99999-99999-99999'])->label('Промо - код')?>
        <?= $form->field($model, 'captcha')->widget(Captcha::className(), [])->label('Введите код с картинки (англ.)') ?>


<?= Html::submitButton('Зарегистироваться',['class'=>'btn btn-success'])?> 
  <?php ActiveForm::end(); ?>
        

        
    
