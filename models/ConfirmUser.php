<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\validators\UserValidator;
use yii\helpers\Url;

class ConfirmUser extends ActiveRecord
{   
    public static function tableName()
    {
        return "User";
    }
    
    public function confirm()
    {
        $keyHash = md5(\Yii::$app->params['key'], true);
        $decrypted=explode("|",mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $keyHash, base64_decode($this->verificationHash),MCRYPT_MODE_ECB));
        Yii::info($this->verificationHash);
        if ($decrypted[0]=="prorab-gid") {
            $user=self::find()->where(['name' => rtrim($decrypted[1])])->one();
            if ($user->active==0){
                $diffTime=(time()-strtotime($user->createTime));
                if ($diffTime<=1296000){ 
                    $user->active=1;
                    $user->save();
                } else {
                    $this->addError('confirm','Код активации проcрочен! Срок действия кода активации 15 дней. Пожалуйста, зарегистрируйтесь еще раз');
                    return false;
                }
                Yii::info($diffTime);
                return $user;
            } else {
                $this->addError('confirm','Пользователь '.$user->name.' уже был активирован ранее!');
                return false; 
            }
        } else {
            $this->addError('confirm','Код активации не прошел проверку');
            return false;
        }
    }
 }


