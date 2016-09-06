<?php

namespace app\controllers;

use Yii;
use app\models\Place;
use app\models\FilePlace;
use app\models\PlayLists;
use app\models\UserRules;
use app\models\VideoBlocks;
use app\models\PlaceSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;

/**
 * PlacesController implements the CRUD actions for Place model.
 */
class PlacesController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Place models.
     * @return mixed
     */
    public function actionIndex()
    {
         if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1)) {
            $searchModel = new PlaceSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
         } else {
            return $this->render('error',[]);
        }  
    }

    /**
     * Displays a single Place model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
         if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1)) {
            return $this->render('view', [
                'model' => $this->findModel($id),
            ]);
         } else {
            return $this->render('error',[]);
        }  
    }

    /**
     * Creates a new Place model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
         if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1)) {
            $model = new Place();
            $gg=Yii::$app->request->post();
            $model->load(Yii::$app->request->post());
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::info('Новая точка '.$model->id.' сохранена в БД',__METHOD__);
                FileHelper::createDirectory(Yii::getAlias('@app').'/files/'.$model->id);
                FileHelper::createDirectory(Yii::getAlias('@app').'/files/'.$model->id.'/video-blocks');
                FileHelper::createDirectory(Yii::getAlias('@app').'/files/'.$model->id.'/users-files');
                Yii::info('Созданы 3 папки новой точки '.$model->id.' сохранена в БД '.Yii::getAlias('@app').'/files/'.$model->id.'...',__METHOD__);
                $fp = fopen(Yii::getAlias('@app').'/files/'.(string)$model->id.'/playlist.m3u', "a");
                fclose($fp);
                Yii::info('Создан плейлист новой точки '.Yii::getAlias('@app').'/files/'.(string)$model->id.'/playlist.m3u',__METHOD__);
                $fp = fopen(Yii::getAlias('@app').'/files/'.(string)$model->id.'/path.txt', "a");
                fclose($fp);
                Yii::info('Создан файл пути новой точки '.Yii::getAlias('@app').'/files/'.(string)$model->id.'/path.txt . Необходимо его заполнение!',__METHOD__);
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
         } else {
            return $this->render('error',[]);
        }  
    }

    /**
     * Updates an existing Place model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
         if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1)) {
            $model = $this->findModel($id);

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::info('Точка '.$model->id.' была изменена',__METHOD__);
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
         } else {
            return $this->render('error',[]);
        }  
    }

    /**
     * Deletes an existing Place model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
         if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1)) {
            $model = $this->findModel($id);
            Yii::info('Удаление точки '.$model->id,__METHOD__);
            FileHelper::removeDirectory(Yii::getAlias('@app').'/files/'.$model->id);
            Yii::info('Удалена директория '.Yii::getAlias('@app').'/files/'.$model->id,__METHOD__);
            $model->delete();
            Yii::info('Удалена точка '.$id.' из БД',__METHOD__);
            FilePlace::deleteAll(['placeId'=>$id]);
            PlayLists::deleteAll(['placeId'=>$id]);
            UserRules::deleteAll(['placeId'=>$id]);
            VideoBlocks::deleteAll(['placeId'=>$id]);
            Yii::info('Удалена инфа, связанная с точкой '.$id.', их таблиц БД FilePlace, PlayLists, UserRules, VideoBlocks',__METHOD__);
            
            return $this->redirect(['index']);
         }
    }

    /**
     * Finds the Place model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Place the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
         if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1)) {
            if (($model = Place::findOne($id)) !== null) {
                return $model;
            } else {
                throw new NotFoundHttpException('The requested page does not exist.');
            }
         }
    }
}
