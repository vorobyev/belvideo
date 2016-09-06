    <?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use app\assets\ReklamaAsset;
use yii\helpers\Url;
use app\models\FilePlace;


AppAsset::register($this);
ReklamaAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => 'Advertising portal',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    
    $proposal = FilePlace::find()->where(['confirm'=>0]);
    $count = (string)$proposal->Count();
    
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
            ['label' => 'Главная', 'url' => ['/site/index']],
            ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1))?['label' => 'Точки', 'url' => ['/places/index']]:"",
            ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1))?['label' => 'Пользователи', 'items'=>[
                    ['label' => 'Точки пользователей','url' => ['/users/index']],
                    ['label' => 'Промо-коды','url' => ['/users/promo']]
                ]
            ]
            
            :((Yii::$app->user->isGuest === false)?['label' => 'Мои файлы', 'url' => ['/site/files']]:""),
            ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1))?['label' => 'Файлы', 'items'=>[
                ['label'=>'Файлы пользователей','url' => ($count == '0')?['/files/admin','userId'=>'all','files'=>'true']:['/files/admin','userId'=>'all','files'=>'false']],
                ['label'=>'Видео-блоки точек','url' => ['/files/admin-videoblocks']],
                ['label'=>'Плейлисты','url'=>['/files/playlists']
            ]]]:['label' => 'Контакты', 'items'=>[
                ['label'=>'О нас','url' => ['/site/about']],
                ['label'=>'Обратная связь','url' => ['/site/contact']]    
                    ]],
            Yii::$app->user->isGuest ?
                ['label' => 'Войти', 'url' => ['/site/login']] :
                [
                    'label' => 'Выйти (' . Yii::$app->user->identity->name. ')',
                    'url' => ['/site/logout'],
                    'linkOptions' => ['data-method' => 'post']
                ]
        ],
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            'homeLink'=>['label'=>'Главная','url'=>Url::to(['site/index'])]
        ]) ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; Advertising portal <?= date('Y') ?></p>

        <p class="pull-right"><?= "" ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
