<?php
namespace app\controllers;

use Yii;
use yii\base\Controller;
use yii\helpers\Json;
use app\models\Kladr;

class AddressController extends Controller {
    
    public function actionCity()
    {
        $address = new Kladr();
        $data=Yii::$app->request->post()["data"];
        $type=Yii::$app->request->post()["type"];
        $region=Yii::$app->request->post()["region"];
        $area=Yii::$app->request->post()["area"];
        $city=Yii::$app->request->post()["city"];
        $locality=Yii::$app->request->post()["locality"];
        $findAddress = $address->findAddress($data,$type,$region,$area,$city,$locality);
        echo Json::encode($findAddress);
    }
   
}
