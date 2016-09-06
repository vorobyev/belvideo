<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = 'Ошибка';
?>
<div class="site-error">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-danger">



    <p>
        Произошла ошибка во время обработки сервером Вашего запроса. 
    </p>
    <p>
        Возможно, у Вас недостаточно прав на просмотр этой страницы, либо страница не существует. Вы можете обратиться к нам с помощью <?php echo Html::a("формы обратной связи",['site/contact']);?>
    </p>
    </div>
</div>
