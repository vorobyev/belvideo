<?php

namespace app\models;

use yii\base\Model;
use app\validators\AddressValidator;

class ModelAddress extends Model
{  
    public $codeRegion;
    public $codeArea;
    public $codeCity;
    public $codeLocality;
    public $codeStreet;
    public $region;
    public $area;
    public $city;
    public $locality;
    public $street;
    public $house;
    public $corps;
    public $flat;
    public $address;
    
    public function rules() 
    {
        return [
            ];
    }

    public function save()
    {
        
    }
    
 }


