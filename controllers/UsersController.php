<?php
namespace app\controllers;

use Yii;
use yii\base\Controller;
use app\models\Place;
use app\models\UserRules;
use app\models\User;
use app\models\PromoKeys;
use yii\helpers\Json;
use yii\data\ActiveDataProvider;

class UsersController extends Controller 
{
    //страница с таблицей пользователей, нажав на которые можно провалиться на точки пользователей (действие Places этого контроллера). 
    //Точки пользователей редактируются (добавление/удаление точки от пользователя). При этом сама точка не трогается. 
    public function actionIndex() {
        if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1))
        {  
            $users=User::find()->where(['admin'=>0]);//админ не выводится в список
            return $this->render('index',['users'=>$users]);
        }   
        else
        {
            return $this->render('error');
        }
    }

    //страница промо-кодов. Просто таблица с промо-кодами и кнопками для их добавления и удаления.
    public function actionPromo() {
        if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1)) {
            $provider = new ActiveDataProvider([
                'query' => PromoKeys::find()->where(['userId'=>NULL]), //только свободные промо-коды
                'pagination' => [
                'pageSize' => 20,
                 ],
            ]);  
            return $this->render('promo',['provider'=>$provider]);
        } 
        else
        {
            return $this->render('error');
        }
    }
    
    //точки пользователей. Слева - точки, не задействованные у пользователя. Справа - задействованные. Перетаскиванием меняется принадлежность.
    //работа с таблицей UserRules
    public function actionPlaces() {
        if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1))
        {
            $request = Yii::$app->request;
            $id = $request->get('id');
            $user = User::find()->where(['id'=>$id])->one();
            $userPlace = $user->place;//точки viaTable UserRules (ссылки на все точки, связанные с пользователем через таблицу UserRules) 
            $not_place=[];
            foreach ($userPlace as $place) {
               array_push($not_place,$place->id); //заполнение массива этими точками (их ID)
            }
            $placeNotIn = Place::find()->where(['not in','id',$not_place])->all(); //поиск точек, которые не связаны с пользователем (левый столбец)

          
            return $this->render('places',[//вид редактирования принадлежности точек через sortable со свойством connected=true (две колонки)
                'places'=>$userPlace,//принадлежащие точки
                'placesNotIn'=>$placeNotIn,//непринадлежащие точки
                'user'=>$user
            ]);
        }
        else
        {
            return $this->render('error');
        }
    }
    
    //метод вызывается яваскриптом при перетаскивании точки в событии sortable. Если startparent = placesOther , а endparent = placesUser,
    //то есть точка добавляется к пользователю. Методом post передаются параметры userId (берется из строки адреса get) и placeId 
    //(берется из ид элемента sortable)
    public function actionAddPlaceToUser ()
    {
        if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1)){
            $model = new UserRules();
            if ($model->load(Yii::$app->request->post(),'')) {
                if (!$model->save()) {
                    Yii::info('Ошибка добавления точки (сервер не смог записать данные). Юзер '.$model->userId.' точка '.$model->placeId,__METHOD__);
                    echo Json::encode(['error'=>'2']);// Серверу не удалось записать эти данные в базу
                    return;
                }
                Yii::info('Пользователю '.$model->userId.' админ добавил точку '.$model->placeId,__METHOD__);
                echo Json::encode(['error'=>'0']);//все ок
            } else {
                Yii::info('Ошибка добавления точки (отсутствуют нужные данные)',__METHOD__);
                echo Json::encode(['error'=>'1']); // Сервер не получил эти данные. Перезагрузите страницу и попробуйте еще раз
            }
        }
    }
    
    
    //удаляет точку пользователя из таблицы UserRules. Параметры используются те же, что и у предыдущего метода, и получены так же.
    //Если startparent = placesUser , а endparent = placesOther,
    //то есть точка уже не принадлежит пользователю.
    public function actionDelPlaceFromUser ()
    {
        if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1)){
            $userId=Yii::$app->request->post()['userId'];
            $placeId=Yii::$app->request->post()['placeId'];
            if (($userId!=null)&&($placeId!=null)) {
                $model = UserRules::deleteAll(['and','userId=\''.$userId.'\'','placeId=\''.$placeId.'\'']);
                if (!$model) {
                    Yii::info('Ошибка удаления точки (сервер не смог записать данные). Юзер '.$model->userId.' точка '.$model->placeId,__METHOD__);
                    echo Json::encode(['error'=>'2']);// Серверу не удалось удалить эти данные из базы
                    return;
                }
                
                //тут удаляем все нахрен из точек по этому юзеру?
                Yii::info('У пользователя '.$model->userId.' админ удалил точку '.$model->placeId,__METHOD__);
                echo Json::encode(['error'=>'0']);//все ок
            } else {
                Yii::info('Ошибка удаления точки (отсутствуют нужные данные)',__METHOD__);
                echo Json::encode(['error'=>'1']);//Сервер не получил все нужные данные. Перезагрузите страницу и попробуйте еще раз
            }
        }
    }
}

