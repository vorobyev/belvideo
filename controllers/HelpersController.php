<?php
namespace app\controllers;

use Yii;
use yii\helpers\Html;
use yii\web\Controller;
use yii\helpers\Url;

class HelpersController extends Controller 
{
    public function actionView(){
        return $this->render(Yii::$app->getRequest()->getQueryParam('id'));
    }
}


