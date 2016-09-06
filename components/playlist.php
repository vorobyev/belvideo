<?php



namespace app\components;

use yii\base\Widget;


class playlist extends Widget {
    
    public $place;
    
    public function run() {
        return $this->render('playlist',['placeId'=>$this->place]);
    }

}
