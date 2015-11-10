<?php

namespace app\controllers;
use Yii;
use yii\web\Controller;
use app\models\User;

class UserController extends Controller {
    public function actionAdd()
    {
        $model = new User();
        if ($model->load(Yii::$app->request->post()) && $model->validate()){
            return $this->render('regSuccess',[
            'model' => $model,
        ]);
        } else {
            return $this->render('registration',[
            'model' => $model,
        ]);
        }
        
    }
}
