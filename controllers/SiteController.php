<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\File;
use yii\data\ActiveDataProvider;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }
    
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            Yii::info('Пользователь '.$model->name.' вошел',__METHOD__);
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionLogout()
    {
        Yii::info('Пользователь '.Yii::$app->user->identity->name.' вышел',__METHOD__);
        Yii::$app->user->logout();
        
        return $this->goHome();
    }
    
    //форма обратной связи
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::info('Отправлено письмо администратору от '.$model->name,__METHOD__);
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }
    
    //страница для загрузки файлов пользователем
    public function actionFiles()
    {   
        if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin != 1)) { //в админе эта страница недоступна
            $provider = new ActiveDataProvider([
                'query' => File::find()->where(['userId'=>Yii::$app->user->identity->id]),
                'pagination' => [
                'pageSize' => 10,
                 ],
            ]);

            $File=new File();


            return $this->render('load',
                    [
                        'file'=>$File,
                        'provider'=>$provider
                    ]);
        } else {
            return $this->render('error',[]);
        }  
    }
}
