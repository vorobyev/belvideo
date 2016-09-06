<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\models\User;

class PromoKeys Extends ActiveRecord {

    public function rules() {
       return [
           
       ];        
    }
    
    public static function tableName() {
        return "PromoKeys";
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'userId']);
    }
    
}
