<?php
/*
Вид смены пароля(начальный). Используется контроллером UserController методом ChangepassForm. Вызывается сервером
только если в параметрах, переданных контроллеру, отсутствует параметр User, который содержит в себе емейл пользователя (POST),
или есть какая-то ошибка $error.

 */
use yii\bootstrap\ActiveForm;
use app\models\User;
use yii\bootstrap\Alert;
use yii\helpers\Html;

$this->title = "Смена пароля";
echo "<h2>Смена пароля пользователя</h2>";
echo "На Ваш e-mail будет отправлено письмо с инструкциями по смене пароля";
$form = ActiveForm::begin([
        'id' => 'confirm-form',
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]);
/*
одно поле для ввода e-mail, на который будут отправлены инструкции по смене пароля (ссылка)
*/

echo $form->field($user,'email',[
                'template' => "{hint}{beginLabel}{labelTitle}{endLabel}{input}{error}",
                'inputOptions'=>['class'=>'form-control'],
                'labelOptions'=>['class'=>'control-label']
            ])->label("Введите Ваш e-mail");

echo Html::submitButton('Сменить пароль', ['class' => 'btn btn-primary', 'name' => 'login-button']);

if ($error == "email") {
    echo Alert::widget([
     'options' => [
             'class' => 'alert-danger',
             'style'=>''
          ],
         'body' => 'Пользователя с таким email не существует. '.Html::a('Зарегистрироваться', ['user/add'], ['class' => 'btn btn-success'])
     ]);
}


 ActiveForm::end();