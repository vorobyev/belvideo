<?php

namespace app\controllers;
use Yii;
use yii\web\Controller;
use app\models\User;
use app\models\UserPass;
use app\models\ConfirmUser;
use app\models\ModelAddress;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\PromoKeys;

class UserController extends Controller {
    
    //добавление нового пользователя
    public function actionAdd()
    {   
        $model = new User();
        if ($model->load(Yii::$app->request->post())&& $model->validate()){
            Yii::info('Создание пользователя (проверка заполнения прошла успешно) '.$model->name,__METHOD__);
            if ($model->save()){
                Yii::info('Создание пользователя (сохранение в БД) '.$model->name,__METHOD__);
                /*
                создаем веб-доступную директорию с именем идентификатора пользователя для хранения видео с именами хешей, для просмотра
                */
                FileHelper::createDirectory(Yii::getAlias('@app').'/web/files/pre-actions/'.$model->id); 
                Yii::info('Создание пользователя (директория pre-actions/'.$model->id.') '.$model->name,__METHOD__);
                $promo = PromoKeys::find()->where(['promo'=>$model->promo])->one();//в найденном промо-коде выставляем юзера. Теперь он занят
                $promo->userId = $model->id;
                $promo->save();
                Yii::info('Создание пользователя (промо-код теперь занят) '.$model->promo.' '.$model->name,__METHOD__);    
                $mes = 'Пользователь '.$model->name.' успешно зарегистрирован. Для активации Вашей учетной записи на e-mail: '.$model->email.' отправлено письмо с кодом активации. Срок действия кода активации - 15 дней с момента регистрации.';
                return $this->render('regSuccess',[
                    'model' => $model,
                    'message'=>$mes
                ]);
            } else {
                Yii::info('Создание пользователя (Ошибка сохранения в БД) '.$model->name.' '.implode(",", $model->getFirstErrors()),__METHOD__);
                return $this->render('regError',[
                    'errors' => $model->getFirstErrors(),
                ]);
            }
        } else {
            return $this->render('registration',[
                'model' => $model
            ]);
        }
    }
    
    //действие подтверждения регистрации пользователя с помощью кода verificationHash, переданного в get-параметре, 
    //который был ранее отослан на почту юзера
    public function actionConfirm()
    {
        $model = new ConfirmUser();
        $model->verificationHash=Yii::$app->request->get('verificationHash');
        if ($confirmUser=$model->confirm()) {
            Yii::info('Подтверждение уч. записи пользователя '.$model->name.' успешно завершено!',__METHOD__);
            return $this->render('confirmSuccess',[
            'model' => $confirmUser,
            ]);            
        } else {
            Yii::info('Подтверждение уч. записи прошло с ошибками! '.implode(",", $model->getFirstErrors()).' varificationHash='.$model->verificationHash,__METHOD__);
            return $this->render('regError',[
                'errors' => $model->getFirstErrors(),
            ]);                 
        }

    }
    
    //повторная отправка кода активации пользователю. Ссылка на нее появляется при неправильном входе
    //в лк
    public function actionConfirmForm ()
    {
        $model = new User();
        $error = "";
        $post = Yii::$app->request->post();
        if (isset($post['User'])){
            $model->load(Yii::$app->request->post());
            $user = $model->findByEmail($model->email);
            if ($user == null){
                $error = "email";
            } else {
                if ($user->active == 1){
                    $error = "active";
                }
            }    
        }
        if (($error != "") || !isset($model->email)) {
            return $this->render('confirm-form',[
                'user'=>$model,
                'error'=>$error
            ]);
        } else if (($error == "") && isset($model->email)) {
            if ($user->resendMailConfirm()) {
                Yii::info('Повторная отправка пользователю письма о подтверждении регистрации на '.$model->email.' успех!',__METHOD__);
                $mes = 'Для активации Вашей учетной записи на e-mail: '.$user->email.' отправлено письмо с кодом активации. Срок действия кода активации - 15 дней с момента регистрации.';
                return $this->render('regSuccess',[
                    'model' => $user,
                    'message'=>$mes
                ]);
            } else {
                Yii::info('Не удалось отправить повторное письмо о подтверждении регистрации на '.$model->email,__METHOD__);
                $user->addError('confirm','Не удалось отправить письмо на указанный email. Попробуйте сделать это позже или обратитесь к администратору сайта');
                return $this->render('regError',[
                    'errors' => $user->getFirstErrors(),
                ]); 
            }
        }
    }
    //Страница смены пароля пользователя. На е-мейл отправляется письмо с описанием шагов для смены пароля и ссылкой с хеш кодом в параметре
    //Ссылка на нее появляется при неправильном входе в лк   
    public function actionChangepassForm (){
        $model = new User();
        $error = "";
        $post = Yii::$app->request->post();
        if (isset($post['User'])){
            $model->load(Yii::$app->request->post());
            $user = $model->findByEmail($model->email);
            if ($user == null){ //если нет пользователя с таким е-мейл
                $error = "email";
            }    
        }
        if (($error != "") || !isset($model->email)) {
            return $this->render('changepass-form',[//форма смены пароля (начальная)
                'user'=>$model,
                'error'=>$error
            ]);
        } else if (($error == "") && isset($model->email)) {
            if ($user->resendMailChangePass()) { // отправка сообщения о смене пароля
                Yii::info('Отправка письма о смене пароля на '.$model->email.' успех!',__METHOD__);
                $mes = 'Для смены пароля на e-mail: '.$user->email.' отправлено письмо с инструкциями.';
                return $this->render('regSuccess',[
                    'model' => $user,
                    'message'=>$mes
                ]);
            } else {
                Yii::info('Отправка письма о смене пароля на '.$model->email.' (ошибка)',__METHOD__);
                $user->addError('confirm','Не удалось отправить письмо на указанный email. Попробуйте сделать это позже или обратитесь к администратору сайта');
                return $this->render('regError',[
                    'errors' => $user->getFirstErrors(),
                ]); 
            }
        }      
    }  
    
    //действие непосредственно смены пароля (вызывается по ссылке смены пароля из письма пользователя). Вид regSuccess - вид успешной смены пароля. Вид для смены пароля - changepass-confirm.
    //в idHidd-скрытом поле храним get-параметр для передачи его в post для смены пароля с соответствующим id.
    public function actionChangePass () {
        $get = Yii::$app->request->get();
        
        $user = new UserPass();
        if ($user->load((Yii::$app->request->post()))&&($user->validate())) { //сначала проверяем, есть ли данные post для смены пароля (пароль был изменен)
            $user_old = new UserPass();
            $user_old = $user_old->findOne($user->idhidd);
            $user_old->password = md5($user->password, false);
            $kk = $user_old->save(false);
            Yii::info('Пользователь '.$user_old->name.' успешно сменил пароль своей уч. записи!',__METHOD__);
            $mes = "Пароль успешно изменен!<br/>".Html::a('Войти', Url::to(["site/login"],true),['class'=>'btn btn-info']);;
            return $this->render('regSuccess',[
                        'message'=>$mes
            ]);
        }
        
        //если post не заполнены, но заполнены get hash и userId
        if ((isset($get['hash'])) && (isset($get['userId']))) { 
            $keyHash = md5(\Yii::$app->params['key'], true);
            //Проверка 1++
            $decrypted=explode("|",mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $keyHash, base64_decode($get['hash']),MCRYPT_MODE_ECB));
            if ($decrypted[0]=="prorab-gid") {
                $user = UserPass::findIdentity($get['userId']);
                if ($user!=null){
                    if ($user->password == rtrim($decrypted[1])){
            //Проверка 1--
                        $user = new UserPass();
                        //заполнение модели
                        $user->hash = $get['hash'];
                        $user->idhidd = $get['userId'];
                        Yii::info('Пользователь id='.$user->idhidd.' получил доступ к смене пароля. Hash='.$user->hash,__METHOD__);
                            return $this->render('changepass-confirm',[
                                'user' => $user
                            ]);                       
                    }
                }
            }
        }
        Yii::info('Пользователю '.$get['userId'].' не удалось сменить пароль. Hash='.$get['hash'],__METHOD__);
        $mes = "Произошли ошибки при обработке строки адреса. Смена пароля невозможна. Попробуйте снова или обратитесь к администратору.";
        return $this->render('regSuccess',[
                    'message'=>$mes
                ]);
    }
    
}
