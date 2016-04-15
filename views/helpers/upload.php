<?php
use yii\helpers\Url;
use yii\helpers\Html;

$this->title = "Загрузка файлов (информация)";
$this->params['breadcrumbs'][] = ['label' => 'Файлы', 'url' => ['site/files']];
$this->params['breadcrumbs'][] = $this->title;

?>
<h2>Загрузка файлов (информация)</h2>
Загрузка видеофайлов производится на следующей странице сайта:<br/>
<?php
echo Html::a(Url::toRoute(['site/files'],true),['site/files']);
?>
<br/>
Далее инфа по пользованию сервисом загрузки файлов....<br/><br/><br/>
Для успешной загрузки видеофайла, необходимо, чтобы он соответствовал следующим условиям:
<a href='#size'><h4>1. Размер файла</h4></a>
Размер файла должен быть не более 300МБ. 
<a href='#ext'><h4>2. Расширение файла</h4></a>
Расширение файла должно соответствовать одному из расширений из следующего списка: flv, avi, mov, mp4, mpg, mpeg, mpe, mp2v, m2v, m2s, wmv, qt, 3gp, asf, rm, mkv