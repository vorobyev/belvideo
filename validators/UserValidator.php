<?php
namespace app\validators;

use yii\validators\Validator;

class UserValidator extends Validator {
    public function init() {
        parent::init();
    }
    
    public function validateAttribute($model, $attribute) 
    {
        if ($attribute=='name'){
            $patterns=array(
                "/^[a-zA-Z]+.*/"=>"Логин должен начинаться с символа латинского алфавита",
                "/^[a-zA-Z0-9]*[-_a-zA-Z0-9]*$/"=>"Логин может состоять из символов латинского алфавита, цифр, знаков подчеркивания и тире"
                );
            foreach ($patterns as $pattern=>$error) {
                if (!preg_match($pattern, $model->$attribute)) {
                    $this->addError($model,$attribute, $error);
                    break;
                }
            }
        }
    }
    
    public function clientValidateAttribute($model, $attribute, $view)
    { 
        return <<<JS
if ("{$attribute}"=='name'){
    var patterns={
        "Логин должен начинаться с символа латинского алфавита":/^[a-zA-Z]+.*/,
        "Логин может состоять из символов латинского алфавита, цифр, знаков подчеркивания и тире":/^[a-zA-Z0-9]*[-_a-zA-Z0-9]*$/   
        } ;  
    for (key in patterns){      
        if (!patterns[key].test(value)) {
            messages.push(key);
            break;
        }
    }
} 
JS;
    }
}
