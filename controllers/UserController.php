<?php

namespace app\controllers;
use Yii;
use yii\web\Controller;
use app\models\User;
use app\models\ConfirmUser;

class UserController extends Controller {
    public function actionAdd()
    {   
        $model = new User();
        if ($model->load(Yii::$app->request->post()) && $model->validate()){
            if ($model->save()){
                return $this->render('regSuccess',[
                'model' => $model,
                ]);
            } else {
                return $this->render('regError',[
                'errors' => $model->getFirstErrors(),
                ]);
            }
        } else {
            return $this->render('registration',[
            'model' => $model,
        ]);
        }
    }
    
    public function actionConfirm()
    {
        $model = new ConfirmUser();
        $model->verificationHash=Yii::$app->request->get('verificationHash');
        if ($confirmUser=$model->confirm()) {
            return $this->render('confirmSuccess',[
            'model' => $confirmUser,
            ]);            
        } else {
            return $this->render('regError',[
            'errors' => $model->getFirstErrors(),
            ]);                 
        }

    }
}
