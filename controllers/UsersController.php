<?php
namespace app\controllers;

use yii\base\Controller;
use app\models\Place;
use app\models\UserRules;
use app\models\User;
use yii\helpers\Json;
use Yii;

class UsersController extends Controller 
{
    public function actionIndex() {
        if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1))
        {  
            $users=User::find();
            return $this->render('index',['users'=>$users]);
        }   
        else
        {
            return $this->render('nopermissions');
        }
    }
    
    public function actionPlaces() {
        if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1))
        {
            $request = Yii::$app->request;
            $id = $request->get('id');
            $user = User::find()->where(['id'=>$id])->one();
            $userPlace = $user->place;
            $not_place=[];
            foreach ($userPlace as $place) {
               array_push($not_place,$place->id); 
            }
            $placeNotIn = Place::find()->where(['not in','id',$not_place])->all();

          
            return $this->render('places',[
                'places'=>$userPlace,
                'placesNotIn'=>$placeNotIn,
                'user'=>$user
            ]);
        }
        else
        {
            return $this->render('nopermissions');
        }
    }
    
    public function actionAddPlaceToUser ()
    {
        $model = new UserRules();
        if ($model->load(Yii::$app->request->post(),'')) {
            if (!$model->save()) {
                echo Json::encode(['error'=>'2']);
                return;
            }
            echo Json::encode(['error'=>'0']);
        } else {
            echo Json::encode(['error'=>'1']);
        }
    }
    
    public function actionDelPlaceFromUser ()
    {
        $userId=Yii::$app->request->post()['userId'];
        $placeId=Yii::$app->request->post()['placeId'];
        if (($userId!=null)&&($placeId!=null)) {
            $model = UserRules::deleteAll(['and','userId=\''.$userId.'\'','placeId=\''.$placeId.'\'']);
            if (!$model) {
                echo Json::encode(['error'=>'2']);
                return;
            }
            echo Json::encode(['error'=>'0']);
        } else {
            echo Json::encode(['error'=>'1']);
        }
    }
}

