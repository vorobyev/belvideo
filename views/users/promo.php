<?php

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = "Свободные промо-коды";
$this->params['breadcrumbs'][] = $this->title;
?>

    <h2><?= Html::encode($this->title) ?></h2>
<?php
echo GridView::widget([
    'dataProvider' => $provider,
    'columns' => [
        [
            'attribute' => 'id',
            'format' => 'text',
            'label' => 'Код'],
        [
            'label'=>'Промо-код',
            'format'=>'text',
            'attribute'=>'promo'
        ],
        
        
         [
            'class' => 'yii\grid\ActionColumn',
            'template' => '<div style="text-align:center"> {new_action3} {new_action2} {new_action1}</div>',
            'buttons' => [
              'new_action1' => function ($url, $model) {
                  return Html::a('<span class="glyphicon glyphicon-trash"></span>', "", [
                              'title' => Yii::t('app', 'Удалить'),
                              'onClick'=>'deletePromo("'.$url.'"); '
                  ]);
              }
         ],
          
          
  'urlCreator' => function ($action, $model, $key, $index) {
    if ($action === 'new_action1') {
        $url = $model->id;
        return $url;
    }
  }
],
        
        

    ],
    'layout'=>'{errors}{items}{pager}',
    'emptyText'=>"Свободные промо-коды не найдены..."
]);
echo Html::button('Сгенерировать новый код',['class'=>'btn btn-primary', 'onclick'=>'generatePromo();']);


?>