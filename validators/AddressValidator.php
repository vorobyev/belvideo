<?php

namespace app\validators;

use yii\validators\Validator;
use app\models\KladrStreet;

class AddressValidator extends Validator {
    public $code;
    
    public function init() {
        parent::init();
        $this->code='';
    }
    
    public function validateAttribute($model, $attribute) 
    {
        $this->code=$this->code.$model->$attribute;
        if ($attribute=='codeStreet'){
            $address = new KladrStreet();
            $check=$address->checkAddress($this->code.'00');
            if ($check!=[]){
                $this->addError($model,$attribute, 'Введенный адрес не найден');
            }
        }
    }
}
