<?php

/*
модель заявок пользователей на размещение файла в точке
*/

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\models\User;
use app\models\File;
use app\models\Place;

class FilePlace Extends ActiveRecord {
    
    public static function tableName() {
        return "FilePlace";
    }
    
    //возвращает модель пользователя, связанного с этой моделью
    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'userId']);
    }
    //тоже самое
    public function getFile() {
        return $this->hasOne(File::className(), ['id' => 'fileId']);
    }
    //тоже самое
    public function getPlace() {
        return $this->hasOne(Place::className(), ['id' => 'placeId']);
    }
    
}
