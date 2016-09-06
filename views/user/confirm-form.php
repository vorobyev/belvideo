<?php

use yii\bootstrap\ActiveForm;
use app\models\User;
use yii\bootstrap\Alert;
use yii\helpers\Html;

$this->title = "Активация пользователя";
echo "<h2>Отправка повторного сообщения для активации пользователя</h2>";

$form = ActiveForm::begin([
        'id' => 'confirm-form',
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]);

echo $form->field($user,'email',[
                'template' => "{hint}{beginLabel}{labelTitle}{endLabel}{input}{error}",
                'inputOptions'=>['class'=>'form-control'],
                'labelOptions'=>['class'=>'control-label']
            ])->label("Введите Ваш e-mail");

echo Html::submitButton('Отправить код активации повторно', ['class' => 'btn btn-primary', 'name' => 'login-button']);

if ($error == "email") {
    echo Alert::widget([
     'options' => [
             'class' => 'alert-danger',
             'style'=>''
          ],
         'body' => 'Пользователя с таким email не существует. '.Html::a('Зарегистрироваться', ['user/add'], ['class' => 'btn btn-success'])
     ]);
}
if ($error == "active") {
    echo Alert::widget([
     'options' => [
             'class' => 'alert-danger',
             'style'=>''
          ],
         'body' => 'Пользователь с таким e-mail уже активирован. '.Html::a('Войти', ['site/login'], ['class' => 'btn btn-success'])
     ]);
}

 ActiveForm::end();