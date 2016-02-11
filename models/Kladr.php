<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Kladr extends ActiveRecord {
    
    
    public static function tableName()
    {
        return "Kladr";
    }
    
    public function findAddress($value,$type,$region,$area,$city,$locality) 
    {
        if ($value==""){
             if ($type=="1") {
                return self::find()->where(['like','id','__000000000%',false])->all();
            } elseif ($type=="2") {
                return self::find()->where(['and',['like','id',$region.'___000000%',false],['not like','id',$region.'000000000%',false]])->all();
            } elseif ($type=="3") {
                return self::find()->where(['and',['like','id',$region.$area.'___000%',false],['not like','id',$region.$area.'000000%',false]])->all();
            } elseif ($type=="4") {
                return self::find()->where(['and',['like','id',$region.$area.$city.'%',false],['not like','id',$region.$area.$city.'000%',false]])->all();
            }           
        }
        else {
            if ($type=="1") {
                return self::find()->where(['and',['like','name',$value],['like','id','__000000000%',false]])->limit(20)->all();
            } elseif ($type=="2") {
                return self::find()->where(['and',['like','name',$value],['like','id',$region.'___000000%',false],['not like','id',$region.'000000000%',false]])->limit(20)->all();
            } elseif ($type=="3") {
                return self::find()->where(['and',['like','name',$value],['like','id',$region.$area.'___000%',false],['not like','id',$region.$area.'000000%',false]])->limit(20)->all();
            } elseif ($type=="4") {
                return self::find()->where(['and',['like','name',$value],['like','id',$region.$area.$city.'%',false],['not like','id',$region.$area.$city.'000%',false]])->limit(20)->all();
            }
        }
    }
    
    public function checkArea($region) {
        return self::find()->where(['and',['like','id',$region.'___000000%',false],['not like','id',$region.'000000000%',false]])->limit(1)->one();
    }
    
    public function checkCity($region,$area) {
        return self::find()->where(['and',['like','id',$region.$area.'___000%',false],['not like','id',$region.$area.'000000%',false]])->limit(1)->one();
    }
    
    public function checkLocality($region,$area,$city) {
        return self::find()->where(['and',['like','id',$region.$area.$city.'___%',false],['not like','id',$region.$area.$city.'000%',false]])->limit(1)->one();
    }
}
