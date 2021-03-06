<?php
namespace app\components;

use yii\base\Widget;
use app\assets\AddressAsset;
use yii\jui\AutoComplete;
use yii\bootstrap\Tabs;
use yii\bootstrap\Html;
use yii\bootstrap\Modal;
use yii\web\JsExpression;
use yii\helpers\Json;
use yii\bootstrap\ActiveForm;

class Address extends Widget {
    public $address;
    public $form;
    public $model;
    public $minLength;
    public $delay;
    public $for;
    
    public function init()
    {
        parent::init();
        $view=$this->getView();
        $this->minLength=0;
        $this->delay=600;
        AddressAsset::register($view);
    }
    
    public function run() 
    {

        Modal::begin([
            'id'=>'addressModal',
            'header' => '<h2>Ввод адреса</h2>',
            'clientOptions'=>[
                'backdrop'=>true,
                'show'=>false
            ]
        ]);
        $form2 = ActiveForm::begin([
        'id' => 'addressForm']); 
 
        echo Html::beginTag('div',['class'=>'mydiv']);
        echo $form2->field($this->address, 'region',[
                'template' => "{hint}{beginLabel}{labelTitle}{endLabel}{input}"
            ])->widget(AutoComplete::className(), [
                'clientOptions'=>[
                    'source'=> New JsExpression('$.proxy(getSource,{'
                        . 'element:this,'
                        . 'type:1,'
                        . 'parentName:\''.$this->address->formName().'\''
                        . '})'),
                    'select'=>New JsExpression('$.proxy(selectActions,{'
                        . 'element:this,'
                        . 'type:1,'
                        . 'parentName:\''.$this->address->formName().'\''                   
                        . '})'),
                    'minLength'=>$this->minLength,
                    'delay'=>$this->delay,
                    'appendTo'=>'.modal-body',
                    'autoFocus'=>true    
                    ],
                'options'=>[
                    'onblur'=>"clearIfBlank(this.id,'".$this->address->formName()."','codeRegion',1)",
                    'onfocus'=>"if (this.value==\"\") {flagEmpty=true} else {flagEmpty=false}",
                    'oninput'=>"clearAll(1,'".$this->address->formName()."',0)",
                    'overflow-y'=> 'auto',
                    'overflow-x'=> 'hidden',    
                    'height'=> '100px',
                    'class'=>'myinput'
                    ]
            ])->label('Регион',['class'=>'mylabel']);  
        echo Html::button("...",[
                'onClick'=>'var e = jQuery.Event("keydown", {});  $("#modeladdress-region").trigger(e); $("#modeladdress-region").focus();',
                'id'=>'region'
            ]);


        
        echo $form2->field($this->address, 'codeRegion')->hiddenInput([
                'id'=>'codeRegion',
                'value'=>'00'
            ])->label(false);

        echo $form2->field($this->address, 'area',[
                'template' => "{hint}{beginLabel}{labelTitle}{endLabel}{input}"
            ])->widget(AutoComplete::className(), [
                'clientOptions'=>[
                    'source'=> New JsExpression('$.proxy(getSource,{'
                        . 'element:this,'
                        . 'type:2,'
                        . 'parentName:\''.$this->address->formName().'\''
                        . '})'),
                    'select'=>New JsExpression('$.proxy(selectActions,{'
                        . 'element:this,'
                        . 'type:2,'
                        . 'parentName:\''.$this->address->formName().'\''                   
                        . '})'),
                    'minLength'=>$this->minLength,
                    'delay'=>$this->delay,
                    'appendTo'=>'.modal-body',
                    'autoFocus'=>true],
                'options'=>[
                    'onblur'=>"clearIfBlank(this.id,'".$this->address->formName()."','codeArea',2)",
                    'onfocus'=>"if (this.value==\"\") {flagEmpty=true} else {flagEmpty=false}",
                    'oninput'=>"clearAll(2,'".$this->address->formName()."',0)",
                    'disabled'=>true,
                    'class'=>'myinput'
                    ]
        ])->label('Район',['class'=>'mylabel']); 
        echo Html::button("...",[
            'onClick'=>'var e = jQuery.Event("keydown", {});  $("#modeladdress-area").trigger(e); $("#modeladdress-area").focus();',
            'id'=>'area',
            'disabled'=>true
        ]);
        echo Html::endTag('br');        
        echo $form2->field($this->address, 'codeArea')->hiddenInput(['id'=>'codeArea','value'=>'000'])->label(false); 

        echo $form2->field($this->address, 'city',[
                'template' => "{hint}{beginLabel}{labelTitle}{endLabel}{input}"
            ])->widget(AutoComplete::className(), [
                'clientOptions'=>[
                    'source'=> New JsExpression('$.proxy(getSource,{'
                        . 'element:this,'
                        . 'type:3,'
                        . 'parentName:\''.$this->address->formName().'\''
                        . '})'),
                    'select'=>New JsExpression('$.proxy(selectActions,{'
                        . 'element:this,'
                        . 'type:3,'
                        . 'parentName:\''.$this->address->formName().'\''                   
                        . '})'),
                    'minLength'=>$this->minLength,
                    'delay'=>$this->delay,
                    'appendTo'=>'.modal-body',
                    'autoFocus'=>true
                    ],
                'options'=>[
                    'onblur'=>"clearIfBlank(this.id,'".$this->address->formName()."','codeCity',3)",
                    'onfocus'=>"if (this.value==\"\") {flagEmpty=true} else {flagEmpty=false}",
                    'oninput'=>"clearAll(3,'".$this->address->formName()."',0)",
                    'disabled'=>true,
                    'class'=>'myinput'
                    ]
            ])->label('Город',['class'=>'mylabel']);
        
        echo Html::button("...",[
            'onClick'=>'var e = jQuery.Event("keydown", {});  $("#modeladdress-city").trigger(e); $("#modeladdress-city").focus();',
            'id'=>'city',
            'disabled'=>true
            ]);
        echo Html::endTag('br');
        echo $form2->field($this->address, 'codeCity')->hiddenInput([
            'id'=>'codeCity',
            'value'=>'000'
            ])->label(false); 

        echo $form2->field($this->address, 'locality',[
                'template' => "{hint}{beginLabel}{labelTitle}{endLabel}{input}"
            ])->widget(AutoComplete::className(), [
                'clientOptions'=>[
                    'source'=> New JsExpression('$.proxy(getSource,{'
                        . 'element:this,'
                        . 'type:4,'
                        . 'parentName:\''.$this->address->formName().'\''
                        . '})'),
                    'select'=>New JsExpression('$.proxy(selectActions,{'
                        . 'element:this,'
                        . 'type:4,'
                        . 'parentName:\''.$this->address->formName().'\''                   
                        . '})'),
                    'minLength'=>$this->minLength,
                    'delay'=>$this->delay,
                    'appendTo'=>'.modal-body',
                    'autoFocus'=>true
                    ],
                'options'=>[
                    'onblur'=>"clearIfBlank(this.id,'".$this->address->formName()."','codeRegion',4)",
                    'onfocus'=>"if (this.value==\"\") {flagEmpty=true} else {flagEmpty=false}",
                    'oninput'=>"clearAll(4,'".$this->address->formName()."',0)",
                    'disabled'=>true,
                    'class'=>'myinput'
                    ]
            ])->label('Населенный пункт',['class'=>'mylabel']);
        
        echo Html::button("...",[
            'onClick'=>'var e = jQuery.Event("keydown", {});  $("#modeladdress-locality").trigger(e); $("#modeladdress-locality").focus();',
            'id'=>'locality',
            'disabled'=>true
            ]);
        echo Html::endTag('br');
        echo $form2->field($this->address, 'codeLocality')->hiddenInput([
            'id'=>'codeLocality',
            'value'=>'000'
            ])->label(false);

        echo $form2->field($this->address, 'street',[
              'template' => "{hint}{beginLabel}{labelTitle}{endLabel}{input}"
            ])->widget(AutoComplete::className(), [
                'clientOptions'=>[
                    'source'=> New JsExpression('$.proxy(getSource,{'
                        . 'element:this,'
                        . 'type:5,'
                        . 'parentName:\''.$this->address->formName().'\''
                        . '})'),
                    'select'=>New JsExpression('$.proxy(selectActions,{'
                        . 'element:this,'
                        . 'type:5,'
                        . 'parentName:\''.$this->address->formName().'\''                   
                        . '})'),
                    'minLength'=>$this->minLength,
                    'delay'=>$this->delay,
                    'appendTo'=>'.modal-body',
                    'autoFocus'=>true
                    ],
            'options'=>[
                'onblur'=>"clearIfBlank(this.id,'".$this->address->formName()."','codeStreet',5)",
                'onfocus'=>"if (this.value==\"\") {flagEmpty=true} else {flagEmpty=false}",
                'oninput'=>"clearAll(5,'".$this->address->formName()."',0)",
                'disabled'=>true,
                'class'=>'myinput'
                ]
        ])->label('Улица',['class'=>'mylabel']); 
       
        echo Html::button("...",[
            'onClick'=>'var e = jQuery.Event("keydown", {});  $("#modeladdress-street").trigger(e); $("#modeladdress-street").focus();',
            'id'=>'street',
            'disabled'=>true
            ]);
        echo Html::endTag('br');
        echo $form2->field($this->address, 'codeStreet')->hiddenInput([
            'id'=>'codeStreet',
            'value'=>'0000'
            ])->label(false);
        echo Html::dropDownList('house',0,[
            '0'=>'Дом',
            '1'=>'Владение',
            '2'=>'Домовладение'
        ],[
            'class'=>'myDropDown',
            'id'=>'selectHouse',
            'onchange'=>'getAddressText();'
            ]);
        echo $form2->field($this->address, 'house')->textInput([
            'id'=>'house',
            'class'=>'myinput',
            'onblur'=>'getAddressText();'
            ])->label(false);
        echo Html::dropDownList('corps',0,[
            '0'=>'Корпус',
            '1'=>'Строение',
            '2'=>'Литера',
            '3'=>'Сооружение',
            '4'=>'Участок'
        ],[
            'class'=>'myDropDown',
            'id'=>'selectCorps',
            'onchange'=>'getAddressText();'
            ]);
        echo $form2->field($this->address, 'corps')->textInput([
            'id'=>'corps',
            'class'=>'myinput',
            'onblur'=>'getAddressText();'
            ])->label(false);
        echo Html::dropDownList('flat',0,[
            '0'=>'Квартира',
            '1'=>'Офис',
            '2'=>'Бокс',
            '3'=>'Помещение',
            '4'=>'Комната'
        ],[
            'class'=>'myDropDown',
            'id'=>'selectFlat',
            'onchange'=>'getAddressText();'
            ]);
        echo $form2->field($this->address, 'flat')->textInput([
            'id'=>'flat',
            'class'=>'myinput',
            'onblur'=>'getAddressText();'
            ])->label(false);
        
        echo Html::textarea("addressText","",[
            'placeholder'=>'Ваш адрес...',
            'cols'=>'53',
            'readonly'=>true,
            'id'=>'addressText'
            ]);
        echo Html::endTag('div');
        
        echo $this->form->field($this->model, 'address'.$this->for.'Id')->hiddenInput([
            'id'=>'address'.$this->for.'Id',
            'value'=>'310200000000000'
            ])->label(false);
        echo $this->form->field($this->model, 'address'.$this->for)->hiddenInput([
            'id'=>'address'.$this->for
            ])->label(false);
        echo $this->form->field($this->model, 'addressOccur'.$this->for)->textInput([
            'id'=>'addressOccur'.$this->for,
            'value'=>'корпус 1, квартира 2а'
            ])->label(false);
        
        echo Html::button('ОК',['data-dismiss'=>"modal", 'aria-hidden'=>"true", 'onclick'=>"setModelAddressFields(\"".$this->for."\")"]);    
        ActiveForm::end();
        Modal::end();
        
        
    }
}

