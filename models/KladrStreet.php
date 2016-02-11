<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class KladrStreet extends ActiveRecord {
    
    
    public static function tableName()
    {
        return "KladrStreet";
    }
    
    public function findAddress($value,$type,$region,$area,$city,$locality,$street) 
    {
        if ($value==""){
            return self::find()->where(['and',['like','name',$value],['like','id',$region.$area.$city.$locality.'%',false],['not like','id',$region.$area.$city.$locality.'0000%',false]])->all();      
        }
        else {
            return self::find()->where(['and',['like','name',$value],['like','id',$region.$area.$city.$locality.'%',false],['not like','id',$region.$area.$city.$locality.'0000%',false]])->limit(5)->all();
        }    
    }
    
    public function checkAddress($region,$area,$city,$locality) {
        return self::find()->where(['and',['like','id',$region.$area.$city.$locality.'%',false],['not like','id',$region.$area.$city.$locality.'0000%',false]])->limit(1)->one();
    }
}
