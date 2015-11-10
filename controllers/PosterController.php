<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;

class PosterController extends Controller {
    
    public function actionCreate()
    {
        return $this->render('create');
    }
}
