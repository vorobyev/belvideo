<?php

/*
Контроллер для отображения информации по пользованию сайтом.
*/

namespace app\controllers;

use Yii;
use yii\helpers\Html;
use yii\web\Controller;
use yii\helpers\Url;

class HelpersController extends Controller 
{
    //метод вызывается только с параметром id, который идентифицирует хелпер
    public function actionView(){
        return $this->render(Yii::$app->getRequest()->getQueryParam('id'));//получение параметра id из строки адреса. Использование значения параметра в качестве имени вида для рендера
    }
}


