<?php

use Yii;
use yii\bootstrap\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;
use app\components\Address;

$this->title = 'Регистрация нового пользователя';
?>
   <?php $form = ActiveForm::begin([
        'id' => 'registration-form']); 
   ?>

        <?= $form->field($model, 'name')->label('Логин') ?>
        <?= $form->field($model, 'email')->label('E-mail') ?>
        <?= $form->field($model, 'password')->passwordInput()->label('Пароль') ?>
        <?= $form->field($model, 'password_repeat')->passwordInput()->label('Повторите пароль') ?>
        <?= $form->field($model, 'captcha')->widget(Captcha::className(), [])->label('Введите код с картинки (англ.)') ?>


<?= Html::submitButton('Зарегистироваться')?> 
  <?php ActiveForm::end(); ?>
        

        
    
