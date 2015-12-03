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
        if ($type=="1") {
            return self::find()->where(['and',['like','name',$value],['like','id','__000000000%',false]])->limit(5)->all();
        } elseif ($type=="2") {
            return self::find()->where(['and',['like','name',$value],['like','id',$region.'___000000%',false],['not like','id',$region.'000000000%',false]])->limit(5)->all();
        } elseif ($type=="3") {
            return self::find()->where(['and',['like','name',$value],['like','id',$region.$area.'___000%',false],['not like','id',$region.$area.'000000%',false]])->limit(5)->all();
        } elseif ($type=="4") {
            return self::find()->where(['and',['like','name',$value],['like','id',$region.$area.$city.'%',false],['not like','id',$region.$area.$city.'000%',false]])->limit(5)->all();
        }         
    }
}
