<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\validators\UserValidator;
use yii\helpers\Url;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{  
    public $captcha;
    public $password_repeat;
    
    public function rules() 
    {
        return [
            [['name', 'password', 'email', 'password_repeat','captcha'], 'required', 'message' => 'Поле обязательно для заполнения'],
            ['email', 'email', 'message' => 'Неправильно введен e-mail'],
            ['name', 'string', 'min' => '6', 'max' => '32', 'tooShort' => 'Логин должен содержать не менее 6 символов', 'tooLong' => 'Логин должен содержать не более 32 символов'],
            ['name', UserValidator::className()],
            [['password','password_repeat'], 'string', 'min' => '5', 'max' => '32', 'tooShort' => 'Пароль должен содержать не менее 5 символов', 'tooLong' => 'Пароль должен содержать не более 32 символов'],
            ['name','unique','message' => 'Пользователь с таким именем уже существует'],
            ['email','unique','message' => 'Пользователь с таким email уже существует'],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message'=>'Пароли должны быть равны'],
            ['captcha','captcha','message'=>'Код с картинки введен неправильно']
            ];
    }

    public static function tableName()
    {
        return "User";
    }
    
    protected function sendMailToUser($message,$email)
    {
       if (Yii::$app->mailer->compose('MessageToConfirmEmail',['message'=>$message])
        ->setFrom('vorobyev.it@gmail.com')
        ->setTo($email)
        ->setSubject('Prorad-Gid')
        ->send()){
        return true;
     } else{
        return false;
     }
    }
    
    public function save($runValidation = false, $attributeNames = NULL)
    {
        $keyHash = md5(Yii::$app->params['key'], true);
        $part_str = "prorab-gid|";
        $this->verificationHash = urlencode(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $keyHash, $part_str.$this->name, MCRYPT_MODE_ECB)));
        $message='Для активации Вашей учетной записи перейдите по ссылке <br/>'.Url::base(true).'?r=user/confirm&verificationHash='.$this->verificationHash.'<br/>Дата и время отправки сообщения: '.date("Y-m-d H:i:s").'. Код активации действителен в течение 15 дней с получения сообщения';
        if ($this->sendMailToUser($message,$this->email)){
            $this->createTime=date("Y-m-d H:i:s");
            $this->active=0;
            $this->accessToken="123";
            $this->authKey="321";
            $this->password=md5($this->password, false);
            return parent::save($runValidation);
        } else {
            $this->addError('mail','Произошла ошибка отправки кода подтверждения на Ваш e-mail. Пожалуйста, попробуйте зарегистрироваться позднее.');
            return false;
        }
    }
    
        public static function findIdentity($id)
    {
       return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['accessToken' => $token]);    
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->authKey;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword($password)
    {
        return $this->password === md5($password);
    }
    
    public static function findByUsername($username)
    {
        return static::findOne(['name' => $username]);
    }
 }


