<?php
namespace app\controllers;

use Yii;
use yii\base\Controller;
use yii\helpers\Json;
use app\models\Kladr;
use app\models\KladrStreet;

class AddressController extends Controller {
 
    public function actionGeta()
    {
        $type=Yii::$app->request->post()["type"];
        $region=Yii::$app->request->post()["region"];
        $area=Yii::$app->request->post()["area"];
        $city=Yii::$app->request->post()["city"];
        $locality=Yii::$app->request->post()["locality"]; 
        $obj=[];
        
        if ($type<'5') {
            $address = new KladrStreet();
            $findStatus = $address->checkAddress($region,$area,$city,$locality);
            if (!empty($findStatus)) {
                $obj=  array_merge($obj,['statusStreet'=>1]);
            } else {
                $obj=  array_merge($obj,['statusStreet'=>0]);
            }
        } 
        if ($type<'4') {
            $address = new Kladr();
            $findStatus = $address->checkLocality($region,$area,$city);
            if (!empty($findStatus)) {
                $obj=  array_merge($obj,['statusLocality'=>1]);
            } else {
                $obj=  array_merge($obj,['statusLocality'=>0]);
            }
        }     
        if ($type<'3') {
            $address = new Kladr();
            $findStatus = $address->checkCity($region,$area);
            if (!empty($findStatus)) {
                $obj=  array_merge($obj,['statusCity'=>1]);
            } else {
                $obj=  array_merge($obj,['statusCity'=>0]);
            }
        }           
         if ($type<'2') {
            $address = new Kladr();
            $findStatus = $address->checkArea($region);
            if (!empty($findStatus)) {
                $obj=  array_merge($obj,['statusArea'=>1]);
            } else {
                $obj=  array_merge($obj,['statusArea'=>0]);
            }
        }         
        
        $l=Json::encode($obj);
        echo $l;
    }
    
    public function actionGetAddress()
    {   
        $type=Yii::$app->request->post()["type"];
        $data=Yii::$app->request->post()["data"];
        $region=Yii::$app->request->post()["region"];
        $area=Yii::$app->request->post()["area"];
        $city=Yii::$app->request->post()["city"];
        $locality=Yii::$app->request->post()["locality"];
        
        if ($type=='5') {
            $street=Yii::$app->request->post()["street"];
            $address = new KladrStreet();
            
            $findAddress = $address->findAddress($data,$type,$region,$area,$city,$locality,$street);
            if (empty($findAddress)) {
                $dataArray = explode(" ",Yii::$app->request->post()["data"]);
                if (count($dataArray)>1) {
                    while (count($dataArray)>=1)
                    {
                        $dataLength=0;
                        $index=0;
                        foreach ($dataArray as $key=>$dataElement) {
                            if (strlen($dataElement)>$dataLength){
                                $data=$dataElement;
                                $dataLength=strlen($dataElement);
                                $index=$key;
                            }
                        }
                    $findAddress = $address->findAddress($data,$type,$region,$area,$city,$locality,$street); 
                    if (!empty($findAddress)){
                        break;
                    } else {
                        unset($dataArray[$index]); 
                        sort($dataArray);
                    }
                    }
                }
            }
        } else {
            $address = new Kladr();
            $findAddress=null;
            $findAddress = $address->findAddress($data,$type,$region,$area,$city,$locality);
            if (empty($findAddress)) {
                $dataArray = explode(" ",Yii::$app->request->post()["data"]);
                if (count($dataArray)>1) {
                    while (count($dataArray)>=1)
                    {
                        $dataLength=0;
                        $index=0;
                        foreach ($dataArray as $key=>$dataElement) {
                            if (strlen($dataElement)>$dataLength){
                                $data=$dataElement;
                                $dataLength=strlen($dataElement);
                                $index=$key;
                            }
                        }
                    $findAddress = $address->findAddress($data,$type,$region,$area,$city,$locality); 
                    if (!empty($findAddress)){
                        break;
                    } else {
                        unset($dataArray[$index]); 
                        sort($dataArray);
                    }
                    }
                }
            }
        }
        
            echo Json::encode($findAddress);
    }
   
}
