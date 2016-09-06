<?php
/*
Модель пользователя. В основном используется контроллером UserController для регистрации. В остальных случаях используется
компонент user (для автологина, проверки авторизации и флага админа и т.д.). Помимо основных полей, имеет вспомогательные поля:
$captcha - для защиты от ботов, $password_repeat - повтор ввода пароля, $promo - для проверки промо-кода.
Класс наследуется от IdentityInterface, поэтому нельзя трогать некоторые функции, объявленные в этом интерфейсе.


*/

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\validators\UserValidator;
use yii\helpers\Url;
use yii\web\IdentityInterface;
use app\models\Place;
use app\models\PromoKeys;

class User extends ActiveRecord implements IdentityInterface
{  
    public $captcha;
    public $password_repeat;
    public $promo;
    
    public function rules() 
    {
        return [
            [['name', 'password', 'email', 'password_repeat','captcha','promo'], 'required', 'message' => 'Поле обязательно для заполнения'],
            ['email', 'email', 'message' => 'Неправильно введен e-mail'],
            ['name', 'string', 'min' => '6', 'max' => '32', 'tooShort' => 'Логин должен содержать не менее 6 символов', 'tooLong' => 'Логин должен содержать не более 32 символов'],
            ['name', UserValidator::className()], //внешний валидатор имени пользователя через RegExp на допустимые символы и т.д. (как серверная так и клиентская)
            [['password','password_repeat'], 'string', 'min' => '5', 'max' => '32', 'tooShort' => 'Пароль должен содержать не менее 5 символов', 'tooLong' => 'Пароль должен содержать не более 32 символов'],
            ['name','unique','message' => 'Пользователь с таким именем уже существует'],
            ['email','unique','message' => 'Пользователь с таким email уже существует'],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message'=>'Пароли должны быть равны'],
            ['captcha','captcha','message'=>'Код с картинки введен неправильно'],
            ['promo','promoValidation'] //валидатор объявлен ниже
            ];
    }

/*
Валидатор промо-кода. Выдает ошибки, если промо-кода нет в системе или он уже использован другим пользователем.
Проверка проходит только если найдена запись промо-кода в БД и ее параметр userId == NULL
*/
    public function promoValidation($attribute, $params)
    {
        $promo = PromoKeys::find()->where(['promo'=>$this->$attribute])->one();
        if ($promo == NULL) {
            $this->addError($attribute, 'Промо-код не найден!');
            return;
        } else if ($promo->userId != NULL) {
            $this->addError($attribute, 'Промо-код уже использован!');
        }
        
    }
    
    public static function tableName()
    {
        return "User";
    }
    
/*
Внутренняя функция отправки сообщения на е-мейл. Учетные данные отправителя см. в настройках компонента mailer. Возвращает false если 
письмо не отправилось. Используется для отправки сообщений для подтверждения регистрации пользователя и для смены пароля.
*/
    protected function sendMailToUser($message,$email)
    {
       if (Yii::$app->mailer->compose('MessageToConfirmEmail',['message'=>$message])
        ->setFrom('vorobyev.it@gmail.com')//отправитель
        ->setTo($email)
        ->setSubject('Bel-video')//тема
        ->send()){
        return true;
     } else{
        return false;
     }
    }
    
    
     protected function createMessage($verificationHash)
    {
        date_default_timezone_set( 'Europe/Moscow' );//это для того, чтобы время вычислилось по нашему часовому поясу, а не по гринвичу
        $message='Для активации Вашей учетной записи перейдите по ссылке <br/>'.Url::base(true).'?r=user/confirm&verificationHash='.$verificationHash.'<br/>Дата и время отправки сообщения: '.date("Y-m-d H:i:s").'. Код активации действителен в течение 15 дней с получения сообщения';
        return $message; 
    }
    
     protected function createMessageChangePass($hash)
    {
        date_default_timezone_set( 'Europe/Moscow' );//см. выше
        $message='Для смены пароля Вашей учетной записи перейдите по ссылке: <br/>'.Url::base(true).'?r=user/change-pass&hash='.$hash.'&userId='.$this->id.' <br/>Дата и время отправки сообщения: '.date("Y-m-d H:i:s").'.<br/> Если Вы знаете свой пароль и не запрашивали о его смене, ничего не предпринимайте!';
        return $message; 
    }
    
    //метод модели отправляет повторное сообщение об активации аккаунта. Используется в контроллере UserController в методе actionConfirmForm после проверки существования e-mail
    //и статуса пользователя (он должен быть неактивен). Иначе выдается сообщение об ошибке (соответствующее). Возвращает false если сообщение не отправлено.
    public function resendMailConfirm()
    {
        $message = $this->createMessage($this->verificationHash);
        return $this->sendMailToUser($message,$this->email);
    }
    
/*
Отправляет сообщение со ссылкой на смену пароля. Ссылка содержит 2 параметра: hash - зашифрованный пароль, и userId - идентификатор пользователя. Вызвается 
 в контроллере UserController в методе actionChangepassForm после проверки на существование e-mail и отсутствие ошибок.
*/
    public function resendMailChangePass()
    {
        $keyHash = md5(Yii::$app->params['key'], true);
        $part_str = "prorab-gid|";
        $message = $this->createMessageChangePass(urlencode(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $keyHash, $part_str.$this->password, MCRYPT_MODE_ECB))));
        return $this->sendMailToUser($message,$this->email);
    }
    
    /*
    метод сохранения пользователя в базе. Используется в контроллере UserController методом actionAdd только после 
     валидации полей и загрузки этих полей в модель.
     */
    public function save($runValidation = false, $attributeNames = NULL)
    {
        $keyHash = md5(Yii::$app->params['key'], true);//ключ можно найти в параметрах пользователя params.php
        $part_str = "prorab-gid|";
        $this->verificationHash = urlencode(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $keyHash, $part_str.$this->name, MCRYPT_MODE_ECB)));
        $message = $this->createMessage($this->verificationHash);
        if ($this->sendMailToUser($message,$this->email)){
            date_default_timezone_set( 'Europe/Moscow' );//установка часового пояса для кооректности текущей даты
            $this->createTime=date("Y-m-d H:i:s");
            $this->active=0;//пока что неактивированный
            $this->admin=0;
            $this->accessToken=$this->createTime.$this->name;//ну примерно уникальный
            $this->authKey=$this->createTime.$this->name;//тоже примерно уникальный
            $this->password=md5($this->password, false);
            return parent::save($runValidation);//родительский метод save
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
    
    public function isAdmin()
    {
        return $this->admin === 1;
    }
    
    public static function findByUsername($username)
    {
        return static::findOne(['name' => $username]);
    }
    
    //связь с таблицей place. Связь один ко многим через таблицу UserRules - права пользователя на точку. Возвращает массив точек, которые разрешены для пользователя.
    public function getPlace() {
        return $this->hasMany(Place::className(), ['id' => 'placeId'])
        ->viaTable('UserRules', ['userId' => 'id']);
    }
    
    public static function findByEmail($email)
    {
        return $user = static::findOne(['email' => $email]);
    }
    
}


