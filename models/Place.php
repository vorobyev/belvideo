<?php

namespace app\models;

use Yii;
use app\models\User;

/**
 * This is the model class for table "Place".
 *
 * @property integer $id
 * @property string $name
 * @property string $address
 */
class Place extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Place';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'address'], 'required'],
            [['name', 'address'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'address' => 'Address',
        ];
    }
 
    public function getUser() {
        return $this->hasMany(User::className(), ['id' => 'userId'])
        ->viaTable('UserRules', ['placeId' => 'id']);
    }
    
}
