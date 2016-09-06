<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\models\File;
use app\models\Place;

class VideoBlocks Extends ActiveRecord {

    public function rules() {
       return [
           
       ];        
    }
    
    public static function tableName() {
        return "VideoBlocks";
    }
    
    public function getFile() {
        return $this->hasOne(File::className(), ['id' => 'fileId']);
    }
    
    public function getPlace() {
        return $this->hasOne(Place::className(), ['id' => 'placeId']);
    }
    
}
