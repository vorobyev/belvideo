<?php
use yii\helpers\Url;
use yii\helpers\Html;

$this->title = "Загрузка файлов (информация)";
$this->params['breadcrumbs'][] = ['label' => 'Файлы', 'url' => ['site/files']];
$this->params['breadcrumbs'][] = $this->title;

?>
<h2 style='text-align:center'>Загрузка файлов (информация)</h2>
<h3 style='color: #084B8A;'>Оглавление</h3>
<ul style='list-style-type:circle;padding-left:55px;font-size:12pt;'>
    <li><a href='#page-upload'>Страница загрузки видеофайлов</a></li>
    <li><a href='#work-with-placegrid'>Работа со списком точек файлов</a></li>
    <li><a href='#work-with-upload'>Загрузка видеофайлов</a></li>
    <li><a href='#requirement-upload'>Требования к загружаемым файлам</a></li>
    <li><a href='#order-of-request'>Порядок обработки заявок</a></li>
</ul>

<a class='no_decor' name='page-upload'><h3>Страница загрузки видеофайлов</h3></a>
</p>
<p class="p-indent">
Страница загрузки видеофайлов состоит из двух областей (рис. 1): 
<ul>
    <li>область списка имеющихся видеофайлов (1)</li>
    <li>область загрузки видеофайлов (2)</li>
</ul>
<div id='img-help'>
<img src='img/img_load1.png'/><br/>
<i><b>Рис. 1. Страница загрузки видеофайлов</b></i>
</div>
</p>
<ol>
    <li>
<p class="p-indent">
Список видеофайлов - таблица со следующими полями: идентификатор, имя файла, размер, длительность, дата загрузки, точки, детальная информация. Также имеется поле
 с кнопками действий - просмотра (<span class='glyphicon mygliph glyphicon-eye-open' style="text-indent:0px; color:blue"></span>), изменения (<span class='glyphicon mygliph glyphicon-pencil' style="text-indent:0px; color:blue"></span>)
 и удаления (<span class='glyphicon mygliph glyphicon-trash' style="text-indent:0px; color:blue"></span>) записей (изменить возможно только имя видеофайла). Пояснения для некоторых полей:

<ul>
    <li>Идентификатор - нужен для однозначной идентификации файла в системе. В случае каких-либо проблем с файлом сообщайте администратору именно идентификатор файла.</li>
    <li>Длительность - длительность видеофайла в формате ЧАСЫ:МИНУТЫ:СЕКУНДЫ</li>
    <li>Точки - содержит кнопку, после нажатия на которую появится список точек, разрешенных для пользователя. В этом списке можно посмотреть/поменять статус файла
     в каждой из точек. Подробнее смотрите в разделе <a href='#work-with-placegrid'>Работа со списком точек файлов.</a></li>
</ul>
</p>
</li>
<li>
<p class="p-indent">
В области загрузки видеофайлов производятся действия по загрузке файлов в систему. Подробнее смотрите ниже, в разделе <a href='#work-with-upload'>Загрузка видеофайлов.</a>
</li>
</ol>
<a class='no_decor' name='work-with-placegrid'><h3>Работа со списком точек файлов</h3></a>
<p class="p-indent">
    Для начала работы со списком точек файла нажмите на кнопку "Точки" в нужной строке списка видеофайлов. Откроется окно (рис. 2), в котором перечислены
    все доступные для пользователя точки. 
</p>
    <div id='img-help'>
    <img src='img/img_place.png'/><br/>
    <i><b>Рис. 2. Окно списка точек видеофайла</b></i>
    </div> 
<p class="p-indent">
    Здесь можно выбрать, в каких точках будет размещаться и проигрываться Ваш видеофайл, или отказаться от 
    размещения уже выбранного ранее видеофайла, соответственно поставив или убрав галочки напротив нужных точек. После отметки нужных точек для сохранения
    изменений нужно нажать на кнопку "Сохранить". После сохранения формируются заявки на размещение файлов в точках, которые администратор должен проверить.
    Заявкам присваивается статус "Не подтверждена", который можно сразу увидеть в списке точек видеофайла. По результатам проверки администратор или подтверждает
    заявки (при этом статус заявки меняется на "Подтверждена"), или отклоняет. Причина отклонения заявки высылается на e-mail пользователя. О правилах размещения заявок
    и сроках появления заявленного видеофайла в плейлисте точки смотрите в разделе <a href='#order-of-request'>Порядок обработки заявок.</a>
</p>

<a class='no_decor' name='work-with-upload'><h3>Загрузка видеофайлов</h3></a>
<p class="p-indent">
Для того, чтобы добавить Ваш видеофайл в плейлист точки, для начала его надо загрузить на сервер. Загрузка выполняется на странице: <br/>
<?php
echo Html::a(Url::toRoute(['site/files'],true),['site/files']);
?>
</p>
<p class="p-indent">
Для загрузки видеофайла необходимо перетащить его на специально обозначенную область, или выбрать его на компьютере, нажав кнопку "Выбрать" (рис. 3). Выбирается один файл (мультизагрузка отсутствует).
Перед загрузкой видеофайла необходимо убедиться, что он соответствует необходимым требованиям (см. раздел <a href='#requirement-upload'>Требования к загружаемым файлам</a>).
</p>
    <div id='img-help'>
    <img src='img/img_load3.png'/><br/>
    <i><b>Рис. 3. Область загрузки видеофайлов</b></i>
    </div> 
<p class="p-indent">
    После выбора файла необходимо дождаться, пока видеофайл появится в области предварительного просмотра (превью). В этот момент файл еще не загружен на сервер.
    Чтобы загрузить его на сервер, необходимо нажать на кнопку "Загрузить". Если Вы передумали загружать выбранный файл, то отменить загрузку можно после появления превью, нажав кнопку "Удалить", либо
    во время загрузки файла, нажав кнопку "Отмена". Если файл уже загружен, то удалить его возможно только из списка видеофайлов.
</p>
<p class="p-indent">
    
    После успешной загрузки страница перезагрузится, и файл появится в списке видеофайлов. Имя файла автоматически преобразуется к допустимому (убираются запрещенные символы, а кириллица заменяется латиницей). Если файл не появился в списке видеофайлов, попробуйте обновить страницу.
    Если после обновления страницы загруженный файл отсутствует, то попробуйте загрузить файл заново, или обратитесь к администратору через <?php echo Html::a("форму обратной связи.",['site/contact']);?>
</p>
<p class="p-indent">
    Если во время загрузки возникли ошибки, возможно, Ваш файл не соответствует <a href='#requirement-upload'>требованиям</a>. Также, возможно, у Вас возникли неполадки в интернет-соединении.
    Так как видеофайлы обычно имеют большой размер, время их загрузки на сервер бывает более 10 минут. На все время загрузки файла необходимо обеспечить стабильность Вашего интернет-соединения, иначе
    загрузка будет прервана и придется начать все заново.
</p>
<a class='no_decor' name='requirement-upload'><h3>Требования к загружаемым файлам</h3></a>
<p class="p-indent">
Для успешной загрузки видеофайла необходимо, чтобы он соответствовал следующим требованиям:
<a class='no_decor' name='size'><h4>1. Размер файла</h4></a>
Размер файла должен быть не более 300МБ. 
<a class='no_decor' name='ext'><h4>2. Расширение файла</h4></a>
Расширение файла должно соответствовать одному из расширений из следующего списка: flv, avi, mov, mp4, mpg, mpeg, mpe, mp2v, m2v, m2s, wmv, qt, 3gp, asf, rm, mkv.
<a class='no_decor' name='filename'><h4>3. Имя файла</h4></a>
Имя файла должно быть уникальным (не совпадать в именем любого из загруженных ранее пользователем файлов).
</p>
<p class="p-indent">
    <i>Просьба не загружать видеофайлы, содержащие ненормативную лексику или сцены эротического/сексуального характера. Спасибо заранее!</i>
</p>
<a class='no_decor' name='order-of-request'><h3>Порядок обработки заявок</h3></a>
<p class="p-indent">
    Обработка заявок на размещение файлов в плейлистах точек проиходит в несколько этапов:
<ol>
    <li>Создание заявки на размещение файла в плейлисте точки (см. <a href='#work-with-placegrid'>Работа со списком точек файлов</a>).</li>
    <li>Подтверждение/отклонение заявки администратором. При отклонении заявки пользователю отсылается письмо с причиной отклонения на указанный при регистрации e-mail. При подтверждении заявки ее статус меняется на "Подтверждена".</li>
    <li>Добавление видеофайла в плейлист заявленной точки, корректировка плейлиста (работа администратора).</li>
    <li>Загрузка видеофайла и нового плейлиста на плеер точки (автоматически).</li>
</ol>
</p>
<p class="p-indent">
    Обработка заявок администратором происходит каждый день после 21-00 по МСК. Загрузка новых видеофайлов и новых плейлистов на плееры точек происходит каждую ночь.
     Следовательно, отсюда вытекают следующие рекомендации:
<ul>
    <li>Отправлять заявки лучше до 21-00, если Вы хотите, чтобы файл проигрывался на нужной точке уже на следующий день;</li>
    <li>Не требовать от администратора как можно быстрее подтвердить Вашу заявку. Это не ускорит появление файла в плейлисте точки, так как обмен файлами с точками
        все-равно происходит один раз в сутки.</li>
</ul>
</p>
<p class="p-indent"><i>Спасибо за внимание и понимание!</i></p>