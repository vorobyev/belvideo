<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class FilePlace Extends ActiveRecord {

    public function rules() {
       return [
           
       ];        
    }
    
    public static function tableName() {
        return "UserFiles";
    }


}
