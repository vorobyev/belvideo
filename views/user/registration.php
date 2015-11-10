<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
$this->title = 'Login';
?>
   <?php $form = ActiveForm::begin([
        'id' => 'login-form']); ?>

        <?= $form->field($model, 'name')->label('Логин') ?>
        <?= $form->field($model, 'email')->label('E-mail') ?>
        <?= $form->field($model, 'password')->passwordInput()->label('Пароль') ?>
    <?= Html::submitButton('Зарегистироваться') ?>
   <?php ActiveForm::end(); ?>