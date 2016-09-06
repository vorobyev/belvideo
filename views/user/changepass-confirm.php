<?php
/*
Вид смены пароля (после проверок). Используется контроллером UserController методом ChangepassForm. Вызывается сервером только тогда, когда
все входящие параметры прошли проверку: hash расшифровался в правильный пароль и нашелся в пользователе с номером userId.
Также имеются скрытые поля для хранения значений idhidd - ид пользователя и hash - hash пользователя. Это нужно для передачи этих параметров 
методом post после сабмита формы для смены пароля текущим контроллером.
*/

use yii\bootstrap\ActiveForm;
use app\models\User;
use yii\helpers\Html;

$this->title = "Смена пароля";
echo "<h2>Смена пароля пользователя</h2>";
$form = ActiveForm::begin([
        'id' => 'confirm-form',
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]);

echo $form->field($user,'password',[
                'template' => "{hint}{beginLabel}{labelTitle}{endLabel}{input}{error}",
                'inputOptions'=>['class'=>'form-control'],
                'labelOptions'=>['class'=>'control-label']
            ])->passwordInput()->label("Введите новый пароль");

echo $form->field($user,'password_repeat',[
                'template' => "{hint}{beginLabel}{labelTitle}{endLabel}{input}{error}",
                'inputOptions'=>['class'=>'form-control'],
                'labelOptions'=>['class'=>'control-label']
            ])->passwordInput()->label("Повторите ввод пароля");

echo Html::activeHiddenInput($user, 'hash');
echo Html::activeHiddenInput($user, 'idhidd'); //id пользователя

echo Html::submitButton('Сменить пароль', ['class' => 'btn btn-primary', 'name' => 'login-button']);


 ActiveForm::end();