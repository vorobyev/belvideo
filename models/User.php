<?php

namespace app\models;

use Yii;
use yii\base\Model;

class User extends \yii\base\Model
{
    public $id;
    public $name;
    public $password;
    public $email;
    public $city;
    public $varificationHash;
    public $active;
    public $authMethodId;
    public $oauthId;
 
 public function rules()
 {
     return [
    [['name','password','email'],'required','message'=>'Поле обязательно для заполнения'],
    ['email','email','message'=>'Неправильно введен e-mail']  
    ];

}
}