<?php
namespace app\components;

use yii\base\Widget;
use app\assets\AddressAsset;
use yii\jui\AutoComplete;
use yii\bootstrap\Tabs;
use yii\bootstrap\Html;
use yii\web\JsExpression;
use yii\helpers\Json;

class Address extends Widget {
    public $address;
    public $form;
    public $minLength;
    public $delay;
    
    public function init()
    {
        parent::init();
        $view=$this->getView();
        $this->minLength=1;
        $this->delay=1100;
        AddressAsset::register($view);
    }
    
    public function run() 
    {
        $outputSimple="";
        $outputSimple=$outputSimple.$this->form->field($this->address, 'address')->label('Адрес');
        $output="";
        $output=$output.$this->form->field($this->address, 'region')->widget(AutoComplete::className(), [
            'clientOptions'=>['source'=> New JsExpression('$.proxy(getSource,{'
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
            'delay'=>$this->delay],
        'options'=>['onblur'=>"clearIfBlank(this.id,'".$this->address->formName()."','codeRegion',1)",
            'oninput'=>"clearAll(1,'".$this->address->formName()."')"]
        ])->label('Регион'); 
        $output=$output.$this->form->field($this->address, 'codeRegion')->hiddenInput(['id'=>'codeRegion','value'=>'00'])->label(false);

        $output=$output.$this->form->field($this->address, 'area')->widget(AutoComplete::className(), [
            'clientOptions'=>['source'=> New JsExpression('$.proxy(getSource,{'
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
            'delay'=>$this->delay],
        'options'=>['onblur'=>"clearIfBlank(this.id,'".$this->address->formName()."','codeArea',2)",
            'oninput'=>"clearAll(2,'".$this->address->formName()."')"]
        ])->label('Район'); 
        $output=$output.$this->form->field($this->address, 'codeArea')->hiddenInput(['id'=>'codeArea','value'=>'000'])->label(false); 

        $output=$output.$this->form->field($this->address, 'city')->widget(AutoComplete::className(), [
            'clientOptions'=>['source'=> New JsExpression('$.proxy(getSource,{'
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
            'delay'=>$this->delay],
        'options'=>['onblur'=>"clearIfBlank(this.id,'".$this->address->formName()."','codeCity',3)",
            'oninput'=>"clearAll(3,'".$this->address->formName()."')"]
        ])->label('Город'); 
        $output=$output.$this->form->field($this->address, 'codeCity')->hiddenInput(['id'=>'codeCity','value'=>'000'])->label(false); 

        $output=$output.$this->form->field($this->address, 'locality')->widget(AutoComplete::className(), [
            'clientOptions'=>['source'=> New JsExpression('$.proxy(getSource,{'
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
            'delay'=>$this->delay],
        'options'=>['onblur'=>"clearIfBlank(this.id,'".$this->address->formName()."','codeRegion',4)",
            'oninput'=>"clearAll(4,'".$this->address->formName()."')"]
        ])->label('Населенный пункт');
        $output=$output.$this->form->field($this->address, 'codeLocality')->hiddenInput(['id'=>'codeLocality','value'=>'000'])->label(false);

        $output=$output.$this->form->field($this->address, 'street')->widget(AutoComplete::className(), [
            'clientOptions'=>['source'=> New JsExpression('$.proxy(getSource,{'
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
            'delay'=>$this->delay],
        'options'=>['onblur'=>"clearIfBlank(this.id,'".$this->address->formName()."','codeStreet',5)",
            'oninput'=>"clearAll(5,'".$this->address->formName()."')"]
        ])->label('Улица'); 
        $output=$output.$this->form->field($this->address, 'codeStreet')->hiddenInput(['id'=>'codeStreet','value'=>'0000'])->label(false);
        $output=$output.$this->form->field($this->address, 'house')->textInput(['id'=>'house'])->label('Дом');
        $output=$output.$this->form->field($this->address, 'corps')->textInput(['id'=>'corps'])->label('Корпус');
        $output=$output.$this->form->field($this->address, 'flat')->textInput(['id'=>'flat'])->label('Квартира');
        return Tabs::widget([
    'items' => [
        [
            'label' => 'Адрес',
            'content' => $output,
            'active' => true
        ],
        [
            'label' => 'Ввести вручную',
            'content' => $outputSimple,
            'headerOptions' => [],
            'options' => ['onclick' => "fillAddress('".$this->address->formName()."', 'address')"],
        ]
    ],
]);
        
    }
}

