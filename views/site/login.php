<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Вход';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Введите следующие данные для входа на сайт:</p>

    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]); ?>

        <?= $form->field($model, 'name',[
                'template' => "{hint}{beginLabel}{labelTitle}{endLabel}{input}",
                'inputOptions'=>['class'=>'inputMy form-control'],
                'labelOptions'=>['class'=>'labelMy col-lg-1 control-label']
            ])->label("Пользователь") ?>

        <?= $form->field($model, 'password',[
                'template' => "{hint}{beginLabel}{labelTitle}{endLabel}{input}",
                'inputOptions'=>['class'=>'inputMy form-control'],
                'labelOptions'=>['class'=>'labelMy col-lg-1 control-label']
            ])->passwordInput()->label("Пароль") ?>

        <?= $form->field($model, 'rememberMe')->checkbox([
            'template' => "{hint}<div class=\"myLabelInner\">{beginLabel}{labelTitle}{endLabel}{input}</div>"
        ])->label("Запомнить меня") ?>

        <div class="margin10 form-group">

                <?= Html::submitButton('Войти', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                <?= Html::a('Зарегистрироваться', ['user/add'], ['class' => 'btn btn-success']) ?>

        </div>

    <?php ActiveForm::end(); ?>
    

</div>
