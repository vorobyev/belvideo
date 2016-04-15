<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Place".
 *
 * @property integer $id
 * @property string $name
 * @property string $address
 */
class UserRules extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'UserRules';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userId', 'placeId'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
//    public function attributeLabels()
//    {
//        return [
//            'id' => 'ID',
//            'name' => 'Name',
//            'address' => 'Address',
//        ];
//    }
    

 
    
}
