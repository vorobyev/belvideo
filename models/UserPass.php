<?php
/*
Модель пользователя для смены пароля. Используется контроллером UserController методом actionChangePass.
Помимо определенных для пользователя полей, присутствуют вспомогательные: $password_repeat - повтор пароля, $hash - хэш, содержащий зашифрованный пароль пользователя,
$idhidd - ид пользователя экземпляра модели. В модели объявлен пользовательский валидатор validateHash для валидации хэша.
*/

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\validators\UserValidator;
use yii\helpers\Url;
use app\models\Place;

class UserPass extends ActiveRecord
{  
    public $password_repeat;
    public $hash;
    public $idhidd;
    
    public function rules() 
    {
        return [
            [['password','password_repeat','hash','idhidd'], 'required', 'message' => 'Поле обязательно для заполнения'],
            [['password','password_repeat'], 'string', 'min' => '5', 'max' => '32', 'tooShort' => 'Пароль должен содержать не менее 5 символов', 'tooLong' => 'Пароль должен содержать не более 32 символов'],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message'=>'Пароли должны быть равны'],
            ['hash','validateHash']//метод объявлен ниже
            ];
    }

    public static function tableName()
    {
        return "User";
    }
    
    public function validateHash($attribute, $params)
    {
        $keyHash = md5(\Yii::$app->params['key'], true);
        //Проверка 2++
        $decrypted=explode("|",mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $keyHash, base64_decode($this->hash),MCRYPT_MODE_ECB));
        if ($decrypted[0]=="prorab-gid") {
            $user = static::findIdentity($this->idhidd);
            if ($user!=null){
                if ($user->password == rtrim($decrypted[1])){
        //Проверка 2--
                    return true;
                }
            }
        }
        $this->addError($attribute, 'Произошли ошибки при обработке строки адреса. Смена пароля невозможна. Попробуйте снова или обратитесь к администратору.');
    }    

    public static function findIdentity($id)
    {
       return static::findOne($id);
    }
    
}


