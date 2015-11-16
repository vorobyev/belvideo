<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;
$this->title = 'Login';
?>
   <?php $form = ActiveForm::begin([
        'id' => 'login-form']); 
   ?>

        <?= $form->field($model, 'name')->label('Логин') ?>
        <?= $form->field($model, 'email')->label('E-mail') ?>
        <?= $form->field($model, 'password')->passwordInput()->label('Пароль') ?>
        <?= $form->field($model, 'password_repeat')->passwordInput()->label('Повторите пароль') ?>
        <?= $form->field($model, 'captcha')->widget(Captcha::className(), [
        ])->label('Введите код с картинки (англ.)') ?>
    <?= Html::submitButton('Зарегистироваться') ?>
   <?php ActiveForm::end(); ?>