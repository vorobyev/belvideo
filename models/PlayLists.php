<?php


namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class PlayLists Extends ActiveRecord{

    public static function tableName() {
        return "PlayLists";
    }
    
    public function getFile() {
        return $this->hasOne(File::className(), ['id' => 'fileId']);
    }    
    
}
