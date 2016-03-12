<?php
namespace app\controllers;

use Yii;
use yii\base\Controller;
use yii\helpers\Json;
use app\models\Kladr;
use app\models\KladrStreet;

class AddressController extends Controller {

    public function actionGetAddressByNumber()
    {
        $region=Yii::$app->request->post()["region"];
        $area=Yii::$app->request->post()["area"];
        $city=Yii::$app->request->post()["city"];
        $locality=Yii::$app->request->post()["locality"]; 
        $street=Yii::$app->request->post()["street"];
        $obj=[];
        
        if ($region!=="00") {
            $address = new Kladr();
            $findAddress = $address->findRegion($region);
            $obj=  array_merge($obj,['region'=>$findAddress]);
        
            if ($area!=="000") {
                $address = new Kladr();
                $findAddress = $address->findArea($region,$area);   
                $obj=  array_merge($obj,['area'=>$findAddress]);
            } else {
                $address = new Kladr();
                $findStatus = $address->checkArea($region);
                if (!empty($findStatus)) {      
                    $obj=  array_merge($obj,["area"=>"1"]);
                } else {
                    $obj=  array_merge($obj,["area"=>"0"]);
                }    
            } 
            
            if ($city!=="000") {
                $address = new Kladr();
                $findAddress = $address->findCity($region,$area,$city);   
                $obj=  array_merge($obj,['city'=>$findAddress]);
            } else {
                $address = new Kladr();
                $findStatus = $address->checkCity($region,$area);
                if (!empty($findStatus)) {      
                    $obj=  array_merge($obj,["city"=>"1"]);
                } else {
                    $obj=  array_merge($obj,["city"=>"0"]);
                }    
            } 
            if ($locality!=="000") {
                $address = new Kladr();
                $findAddress = $address->findLocality($region,$area,$city,$locality);   
                $obj=  array_merge($obj,['locality'=>$findAddress]);
            } else {
                $address = new Kladr();
                $findStatus = $address->checkLocality($region,$area,$city);
                if (!empty($findStatus)) {      
                    $obj=  array_merge($obj,["locality"=>"1"]);
                } else {
                    $obj=  array_merge($obj,["locality"=>"0"]);
                }    
            } 
            if ($street!=="0000") {
                $address = new KladrStreet();
                $findAddress = $address->findStreet($region,$area,$city,$locality,$street);   
                $obj=  array_merge($obj,['street'=>$findAddress]);
            } else {
                $address = new KladrStreet();
                $findStatus = $address->checkAddress($region,$area,$city,$locality);
                if (!empty($findStatus)) {      
                    $obj=  array_merge($obj,["street"=>"1"]);
                } else {
                    $obj=  array_merge($obj,["street"=>"0"]);
                }    
            } 
        }
        else {
            $obj=  array_merge($obj,["region"=>"1"]);
        }
        
        return Json::encode($obj);
        
        
    }
    
    public function actionGetAddressStatus()
    {

        $region=Yii::$app->request->post()["region"];
        $area=Yii::$app->request->post()["area"];
        $city=Yii::$app->request->post()["city"];
        $locality=Yii::$app->request->post()["locality"]; 
        $type=Yii::$app->request->post()["type"];
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
