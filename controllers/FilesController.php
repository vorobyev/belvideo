<?php

namespace app\controllers;
use Yii;
use yii\web\Controller;
use app\models\File;
use app\models\User;
use app\models\FilePlace;
use app\models\PlayLists;
use app\models\VideoBlocks;
use app\models\Place;
use app\models\PromoKeys;
use yii\helpers\Json;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\data\ActiveDataProvider;


class FilesController extends Controller{
    
    //действие загрузки нового файла, его валидации, извлечения и записи его инфы в SQL-бд  
    //работает на странице пользователя (файлы идут в Yii::$app->user->identity->id папку)
    //на странице админа (файлы идут в Yii::$app->request->get('userId') папку)
    //на странице добавления видеоблоков (файлы идут в Yii::$app->user->identity->id папку). 
    //В параметре get при вызове этого метода передается флаг видеоблока, который фиксируется в базе
    
    public function actionAdd2() {
        if (Yii::$app->user->isGuest === false) {
            $model = new File();
            $model->file = $model->uploadProfilePicture();//получение инстанса файла (его мета, типа tmpname, name и т.д.)
            $dir=$model->getProfilePictureFile();//получение абсолютного пути к загруженному файлу (имя файла - хэш) в веб-доступной директории

            if ($model->validate()) { //проверка файла на extentions, maxSize и maxFiles
                $uploaded = $model->file->saveAs($dir);  //правильно перемещение файла из временной папки в целевую      
                $isError = $model->save(false,"");//сохранение модели в SQL-бд. Возвращает false в случае успеха, и массив ошибок в случае провала
                Yii::info('Загрузка файла '.$model->fileName.' пользователем '.$model->userId,__METHOD__);
                $info = new \SplFileInfo($dir);
                $mimeType = FileHelper::getMimeType($dir, null, false);
                $mes = "";
                if ($mimeType === null) {
                    $mes = "Не удается распознать MIME-тип";
                } else {
                    $extensionsByMimeType = FileHelper::getExtensionsByMimeType($mimeType); 
                    if ($info->getExtension()!=$extensionsByMimeType) { //полученный MIME-тип не соответствует расширению файла
                        $mes = "Тип файла не совпадает с его MIME-типом";
                    }
                }
                //эта процедура возвращает массив json. Если он пустой, то значит загрузка прошла успешно, если есть элемент error, то выводится 
                //его содержимое в виде ошибки плагина
                if ($isError != false){
                    if ($mes!=""){
                        $isError=$isError."<li>".$mes."</li>";
                        Yii::info('Ошибки загрузки файла '.$model->fileName.' пользователем '.$model->userId. ': '.$isError,__METHOD__);
                    }
                    echo Json::encode(['error'=>"Файл загружен и сохранен, но при его записи возникли следующие ошибки:<br/><ul>".$isError."</ul>Возможно, следует перезагрузить этот файл, следуя ".Html::a('рекомендациям', Url::to(["helpers/view",'id'=>'upload','#'=>'rekommend'],true),['target'=>'_blank'])."<br/>Чтобы увидеть загруженный файл в списке файлов выше, ".Html::a('обновите', Url::to(["site/files"],true))." страницу"]);
                } else {
                    echo Json::encode([]);
                }

            } else {
                Yii::info('Ошибки загрузки файла '.$dir.' пользователем '.Yii::$app->user->identity->id. ': '.$model->getFirstError('file'),__METHOD__);
                echo Json::encode(['error'=>$model->getFirstError('file')]);
                //echo Json::encode(['error'=>"Ошибка проверки загрузки файла на сервере. <br/>Разрешенные форматы файла: 'flv', 'avi', 'mov','mp4','mpg','mpeg','mpe','mp2v','m2v','m2s','wmv','qt','3gp','asf','rm','mkv'.<br/>Разрешенный размер: до 300МБ. <br/>Количество одновременно загружаемых файлов: 1"]);
            }
        }
    }
    

    //удаление файла из таблицы файлов по идентификатору в параметре POST (асинхронный запрос из JS)
    public function actionDelFileById(){
        if (Yii::$app->user->isGuest === false) {
            $id=Yii::$app->request->post()['id'];
            $file=File::findOne($id);
            if (file_exists(Yii::$app->params['uploadPath'] . "/" . $file->userId . "/".$file->href.".".$file->ext)){
                unlink(Yii::$app->params['uploadPath'] . "/" . $file->userId . "/".$file->href.".".$file->ext);//удаление файла в веб-доступной директории
                Yii::info('Удаление файла по id='.$id.' '.Yii::$app->params['uploadPath'] . "/" . $file->userId . "/".$file->href.".".$file->ext,__METHOD__);
            }
            if (file_exists(Yii::$app->params['uploadPath'] . "/" . $file->userId . "/".$file->href."_original")){
                unlink(Yii::$app->params['uploadPath'] . "/" . $file->userId . "/".$file->href."_original");
                Yii::info('Удаление файла по id='.$id.' '.Yii::$app->params['uploadPath'] . "/" . $file->userId . "/".$file->href."_original",__METHOD__);
            }
            if ($file->videoBlock == NULL) {
                $fold ="/users-files/";
                $fold2 ="users-files/";
            } else {
                $fold ="/video-blocks/";
                $fold2 ="video-blocks/";
            }

            if ($file->videoBlock == NULL){
                $filesInPlaces = FilePlace::find()->where(['fileId'=>$id])->all();
            } else {
                $filesInPlaces = VideoBlocks::find()->where(['fileId'=>$id])->all();
            }

            $playlists = PlayLists::find()->where(['fileId'=>$file->id])->all();
            if ($playlists != NULL) {
                foreach ($playlists as $playlist){ //цикл удаления записей в плейлистах об этом файле
                    if ($file->videoBlock == NULL){
                        $filePath = Yii::getAlias('@app')."/files/".(string)$playlist->placeId.$fold.$file->userId."_".$file->fileName; 
                    } else {
                        $filePath = Yii::getAlias('@app')."/files/".(string)$playlist->placeId.$fold.$file->fileName;
                    }
                    if (file_exists($filePath)) {
                        unlink($filePath); //удаление файла в директории точки
                        Yii::info('Удаление файла по id='.$id.' '.$filePath,__METHOD__);
                    }
                    $fileStr=file(Yii::getAlias('@app')."/files/".(string)$playlist->placeId."/playlist.m3u"); 
                    $pathToVideo = rtrim(file_get_contents(Yii::getAlias('@app')."/files/".(string)$playlist->placeId."/path.txt"));
                    $sizeStr = sizeof($fileStr);
                    for($i=0;$i<$sizeStr;$i++){
                        if ($file->videoBlock == NULL){
                            if($fileStr[$i]==$pathToVideo.$fold2.$file->userId."_".$file->fileName."\r\n") {
                                unset($fileStr[$i]);
                                Yii::info('Удаление строки из плейлиста по id='.$id.' '.$pathToVideo.$fold2.$file->userId."_".$file->fileName,__METHOD__);
                            }
                        } else {
                            if($fileStr[$i]==$pathToVideo.$fold2.$file->fileName."\r\n") {
                                unset($fileStr[$i]);
                                Yii::info('Удаление строки из плейлиста по id='.$id.' '.$pathToVideo.$fold2.$file->userId."_".$file->fileName,__METHOD__);
                            }                                   
                        }
                    }
                    //удаление строк с файлом из файла плейлиста
                    $fp=fopen(Yii::getAlias('@app')."/files/".(string)$playlist->placeId."/playlist.m3u","w"); 
                    fputs($fp,implode("",$fileStr)); 
                    fclose($fp);
                }
            }
            foreach ($filesInPlaces as $place){
                if ($file->videoBlock == NULL){
                    $filePath = Yii::getAlias('@app')."/files/".(string)$place->placeId.$fold.$file->userId."_".$file->fileName; 
                } else {
                    $filePath = Yii::getAlias('@app')."/files/".(string)$place->placeId.$fold.$file->fileName;
                }
                if (file_exists($filePath)) {
                    unlink($filePath);
                    Yii::info('Удален файл из папки точки id='.$id.' '.$filePath,__METHOD__);
                }
            }

            //удаление записей о файле из SQL-бд 
            PlayLists::deleteAll(['fileId'=>$file->id]);
            VideoBlocks::deleteAll(['fileId'=>$file->id]);
            FilePlace::deleteAll(['fileId'=>$file->id]);
            $file->delete();
            Yii::info('Удалены записи в БД id='.$id.' в таблицах PlayLists, VideoBlocks, FilePlace, File',__METHOD__);
            
        }
    }
    
    //действие просмотра видео из веб-доступной директории через плеер. Получает href файла из get
    public function actionView() {
        if (Yii::$app->user->isGuest === false) {
            $href = Yii::$app->request->get()['href'];
            $userId = Yii::$app->request->get()['userId'];
            if ((isset($userId)&&($userId==Yii::$app->user->identity->id))||((isset($userId))&&(Yii::$app->user->identity->admin==1))) {
                $file = new File();
                $video = $file->getVideoByHref($href,$userId);
                return $this->render('view',
                        [
                            'video'=>$video
                        ]);
            }
        } else {
            return $this->render('error',[]);
        } 
    }
    
    //метод для скачивания файла с сервера
    public function actionDown() {
       if (Yii::$app->user->isGuest === false) {
           $href = Yii::$app->request->get()['href'];
           $userId = Yii::$app->request->get()['userId'];
           if ((isset($userId)&&($userId==Yii::$app->user->identity->id))||((isset($userId))&&(Yii::$app->user->identity->admin==1))) {
                $file = new File();
                $video = $file->getVideoByHref($href,$userId);
                Yii::info('Попытка скачать файл href='.$href.' userId='.$userId,__METHOD__);
                Yii::info('Попытка скачать файл id='.$video->id.'',__METHOD__);
                return \Yii::$app->response->sendFile(Yii::getAlias('@app').'/web/files/pre-actions/'.$video->userId.'/'.$video->href.".".$video->ext,$video->fileName);
           }
       }       
    }
 
    //просмотр и загрузка видео-блоков из режима администратора
    public function actionAdminVideoblocks() {    
        if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1)) {
            $providerFile = new ActiveDataProvider([
                'query' => File::find()->where(['videoBlock'=>'1']), //где файл является видеоблоком
                'pagination' => [
                'pageSize' => 20,
                 ],
            ]);    
            
            $File=new File();
            
            return $this->render('admin-videoblocks',
                  [
                      'file'=>$File,
                      'providerFile'=>$providerFile
                  ]);            
        } else {
            return $this->render('error',[]);
        }      
    }
    
    //действие просмотра, редактирования и загрузки файлов пользователей, а также обработки заявок пользователей.
    //работает только в режиме админа. Ловит следующие get - параметры:
    //userId - если 'all', то выводит все файлы, которые без флага videoblocks, иначе выводит файлы конкретного юзера с ид=userId
    //files - если true то выводит вкладку с файлами, если false - то выводит заказы пользователей.
    //playlist - номер точки. Наличие этого параметра указывает на необходимость работы с соответствующим плейлистом
    //fid - в режиме работы с плейлистом указывает на ID нового файла плейлиста.
    public function actionAdmin() {
        if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1)) {
            $userId = Yii::$app->request->get()['userId'];
            if ($userId == "all") {
                $providerFile = new ActiveDataProvider([
                    'query' => File::find()->where(['videoBlock'=>NULL]), //все файлы без видео-блоков
                    'pagination' => [
                    'pageSize' => 20,
                     ],
                ]);
                $currentUser = 'all';
            } else {
                $providerFile = new ActiveDataProvider([
                    'query' => File::find()->where(['and',['userId'=>$userId,'videoBlock'=>NULL]]), //файлы определенного пользователя без видео-блоков
                    'pagination' => [
                    'pageSize' => 20,
                     ],
                ]);
                $currentUser = User::find()->where(['id'=>$userId])->one();
            }
            $providerUsers = new ActiveDataProvider([
                    'query' => User::find()->where(['not like','admin',1,false]) //юзеры для списка юзеров (без админа)
                ]);
            
            $proposal = FilePlace::find()->where(['confirm'=>0]); //неподтвержденные заявки
            $providerProposal = new ActiveDataProvider([
                'query' => $proposal,
                'pagination' => [
                'pageSize' => 20,
                 ],
            ]);
            $count = (string)$proposal->Count(); //количество неподтвержденных заявок
            
            $File=new File();
            
            return $this->render('admin',
                   [
                       'count'=>$count,
                       'proposal'=>$providerProposal,
                       'file'=>$File,
                       'currentUser'=>$currentUser,
                       'providerUsers'=>$providerUsers,
                       'providerFile'=>$providerFile,
                   ]);           
        } else {
            return $this->render('error',[]);
        } 
    }
    
    //действие меняет имя файла пользователя/видео-блока (зависит от контекста). Предварительно проводится проверка имени файла. Расширение не меняется
    //Меняется: 
    //имя файла в базе данных (таблица Files поле fileName)
    //хэш имени файла в базе данных (таблица Files поле href)
    //имя файла в веб-доступной директории (соответствующее хэшу имени файла)
    //у всех файлов имя файла в директориях точек, где используется файл (IDпользователя_имяФайла для пользовательских файлов и 
    //простое имяФайла для видеоблоков)
    //все записи о файле в плейлистах (соответствует именам файла в директориях точек)
    public function actionChangeFilename() {
        if (Yii::$app->user->isGuest === false) {
            $name=Yii::$app->request->post()['name'];
            $id=Yii::$app->request->post()['id'];
            if (preg_match("/(^[a-zа-яA-ZА-Я0-9]+([a-zа-яA-ZА-Я\_0-9-]*))$/u" , $name)==NULL) {
                echo Json::encode(['error'=>'Недопустимое имя файла']);
            } else {
                if (isset($name)) { //если поле имени заполнено
                    $file = File::findOne($id);   
                    Yii::info('Изменение имени файла id='.$id,__METHOD__);
                    $fileName1 = explode(".",$this->str2url($name))[0];                     
                    $file2 = File::find()->where(['and',['like','fileName',$fileName1.'.%',false],'userId='.$file->userId])->all();
                    if ($file2 == null) { //если отсутствует файл с таким именем у данного пользователя
                        $href_old = $file->href;
                        $name_old = $file->fileName; 
                        $file->fileName = $this->str2url($name).".".$file->ext; //преобразование имени файла к корректному
                        if ($file->videoBlock == NULL){
                            $filesInPlaces = FilePlace::find()->where(['fileId'=>$id,'confirm'=>'1'])->all();
                        } else {
                            $filesInPlaces = VideoBlocks::find()->where(['fileId'=>$id])->all();
                        }
                        
                        $filesInPlaylists = PlayLists::find()->where(['fileId'=>$id])->all();//поиск всех вхождений файла в плейлисты
                        if ($file->videoBlock == NULL) {
                            $fold ="/users-files/";
                            $fold2 = "users-files/";
                        } else {
                            $fold ="/video-blocks/";
                            $fold2 = "video-blocks/";
                        }
                        //изменяем имя файла во всех директориях точек, соответствующих записям в таблице UserFiles. Видеоблоки и файлы пользователей
                        //именуются по разному принципу
                        foreach ($filesInPlaces as $fileInPlace) {
                            $place = $fileInPlace->placeId;
                            if ($file->videoBlock == NULL){
                                $fileNameOld = Yii::getAlias('@app')."/files/".(string)$place.$fold.$file->userId."_".$name_old;
                                $fileNameNew = Yii::getAlias('@app')."/files/".(string)$place.$fold.$file->userId."_".$file->fileName;
                            } else {
                                $fileNameOld = Yii::getAlias('@app')."/files/".(string)$place.$fold.$name_old;
                                $fileNameNew = Yii::getAlias('@app')."/files/".(string)$place.$fold.$file->fileName;                            
                            }
                            rename($fileNameOld,$fileNameNew);
                            Yii::info('Изменение имени файла id='.$id.' в директории точки с '.$fileNameOld.' на '.$fileNameNew,__METHOD__);
                        }
                        //переименование записей о файлах пользователей и видеоблоках в файлах плейлистов. Файлы соответствуют записям в таблице PlayLists.
                        //Один файл может встречаться несколько раз в одном плейлисте
                        foreach ($filesInPlaylists as $fileInPlace) {
                            $place = $fileInPlace->placeId;
                            if ($file->videoBlock == NULL){
                                $fileNameOld = Yii::getAlias('@app')."/files/".(string)$place.$fold.$file->userId."_".$name_old;
                                $fileNameNew = Yii::getAlias('@app')."/files/".(string)$place.$fold.$file->userId."_".$file->fileName;
                            } else {
                                $fileNameOld = Yii::getAlias('@app')."/files/".(string)$place.$fold.$name_old;
                                $fileNameNew = Yii::getAlias('@app')."/files/".(string)$place.$fold.$file->fileName;                            
                            }

                            $fileStr=file(Yii::getAlias('@app')."/files/".(string)$place."/playlist.m3u");
                            $pathToVideo = rtrim(file_get_contents(Yii::getAlias('@app')."/files/".(string)$place."/path.txt"));//парс файла в массив строк
                            $sizeStr = sizeof($fileStr);
                            for($i=0;$i<$sizeStr;$i++){ //просмотр всех записей плейлиста и переименование только нужных записей
                                if ($file->videoBlock == NULL){
                                    if ($fileStr[$i]==$pathToVideo.$fold2.$file->userId."_".$name_old."\r\n") {
                                        $fileStr[$i] = $pathToVideo.$fold2.$file->userId."_".$file->fileName."\r\n";
                                        Yii::info('Изменение имени файла id='.$id.' в плейлисте точки с '.$pathToVideo.$fold2.$file->userId."_".$name_old.' на '.$pathToVideo.$fold2.$file->userId."_".$file->fileName,__METHOD__);
                                    }
                                } else {
                                    if ($fileStr[$i]==$pathToVideo.$fold2.$name_old."\r\n") {
                                        $fileStr[$i] = $pathToVideo.$fold2.$file->fileName."\r\n";
                                        Yii::info('Изменение имени файла id='.$id.' в плейлисте точки с '.$pathToVideo.$fold2.$name_old.' на '.$pathToVideo.$fold2.$file->fileName,__METHOD__);
                                    }                                    
                                }
                            }
                            //перезапись файла плейлиста
                            $fp=fopen(Yii::getAlias('@app')."/files/".(string)$place."/playlist.m3u","w"); 
                            fputs($fp,implode("",$fileStr)); 
                            fclose($fp);

                        }
                        //сохранение файла в БД в режиме переименования, о чем свидетельствует третий параметр
                        $file->save(false,NULL,'change');
                        Yii::info('Изменение имени файла id='.$id.' в БД',__METHOD__);
                        //переименование файла в веб-доступной директории
                        rename(Yii::$app->params['uploadPath'] . "/" . $file->userId. "/".$href_old.".".$file->ext,Yii::$app->params['uploadPath'] . "/" . $file->userId . "/".$file->href.".".$file->ext);
                        Yii::info('Изменение имени файла id='.$id.' с '.Yii::$app->params['uploadPath'] . "/" . $file->userId. "/".$href_old.".".$file->ext.' на '.Yii::$app->params['uploadPath'] . "/" . $file->userId . "/".$file->href.".".$file->ext,__METHOD__);
                        if (file_exists(Yii::$app->params['uploadPath'] . "/" . $file->userId. "/".$href_old."_original")) {
                            rename(Yii::$app->params['uploadPath'] . "/" . $file->userId. "/".$href_old."_original",Yii::$app->params['uploadPath'] . "/" . $file->userId . "/".$file->href."_original");
                            Yii::info('Изменение имени файла id='.$id.' с '.Yii::$app->params['uploadPath'] . "/" . $file->userId. "/".$href_old."_original".' на '.Yii::$app->params['uploadPath'] . "/" . $file->userId . "/".$file->href."_original",__METHOD__);
                        }
                        echo Json::encode(['success'=>true]); //любой параметр, кроме error обработчик js ловит как успешный результат и перезагружает страницу
                    } else {
                        echo Json::encode(['error'=>'Такое имя уже есть у другого файла']);//js выводит текст ошибки
                    }
                }
            }
        }
    }
    //действие для проверки имени файла ПЕРЕД загрузкой на уникальность в пределах пользователя
    //Проверка видеоблоков на уникальность производится в пределах администратора, то есть не может быть двух видеоблоков
    //с одним именем
    public function actionCheckByName() {
        if (Yii::$app->user->isGuest === false) {
            $name=$this->str2url(Yii::$app->request->post()['name']); //преобразование имени файла к валидному
            /*
            идентификатор пользователя берется из:
            1) сессии, если файл загружает пользователь, или админ загружает видеоблок
            2) параметра get userId если файл загружает админ за пользователя
            */
            $userId=Yii::$app->request->post()['user'];
                    
            $fileName1 = explode(".",$this->str2url($name))[0];        
            $file = File::find()->where(['and',['like','fileName',$fileName1.'.%',false],'userId='.$userId])->all();
            if ($file == null) {
                return Json::encode(['success'=>true]);//js возвращает пустой массив в обработчик события filebatchpreupload плагина FileInput
            } else {
                return Json::encode(['success'=>false]);//js возвращает message в обработчик события filebatchpreupload плагина FileInput, который выводится как сообщение об ошибке
            }
        }
    }
    
    //вспомогательная функция перевода кирилицы в латиницу
    public function rus2translit($string) {
        $converter = array(
            'а' => 'a',   'б' => 'b',   'в' => 'v',
            'г' => 'g',   'д' => 'd',   'е' => 'e',
            'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
            'и' => 'i',   'й' => 'y',   'к' => 'k',
            'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',   'р' => 'r',
            'с' => 's',   'т' => 't',   'у' => 'u',
            'ф' => 'f',   'х' => 'h',   'ц' => 'c',
            'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
            'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
            'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

            'А' => 'A',   'Б' => 'B',   'В' => 'V',
            'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
            'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
            'И' => 'I',   'Й' => 'Y',   'К' => 'K',
            'Л' => 'L',   'М' => 'M',   'Н' => 'N',
            'О' => 'O',   'П' => 'P',   'Р' => 'R',
            'С' => 'S',   'Т' => 'T',   'У' => 'U',
            'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
            'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
            'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
            'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya', '.' => '.', 
        );
        return strtr($string, $converter);
    }
    //функция перевода имени файла в валидную форму
    public function str2url($str) {
        $str = str_replace(" ","_",$str);
        $str = preg_replace('|_+|', '_', $str);
        // переводим в транслит
        $str = $this->rus2translit($str);
        // в нижний регистр
        $str = strtolower($str);
        // заменям все ненужное нам на "_"
        $str = preg_replace('~[^a-z0-9_\.]+~u', "_", $str);
        $str = preg_replace('|_+|', '_', $str);
        // удаляем начальные и конечные '-'
        //$str = trim($str, "-");
        return $str;
    }
    
    //функция работы с заявками пользователя, которая обрабатывает действия при формировании заявок на размещение файла в точке, формируемых
    //из контекста списка файлов пользователя в режимах Пользователя и Админа
    //Окончательное размещение файла в точке следует только после подтверждения заявки Админом. В этой функции файл в точке (и плейлисте) можно 
    //только удалить, убрав галочку из соответстующего контекстного меню.
    public function actionRewriteFilePlaces() {
        if (Yii::$app->user->isGuest === false) {
            /*
            Принимает параметры:
             - Массив places - точки, в которых надо разместить файл
             - user - идентификатор пользователя, который направляет заявку (или от имени которого направляет заявку админ)
             - file - идентификатор файла
            */
            $places = Yii::$app->request->post()['places']; 
            $places = Json::decode($places);
            $user = Yii::$app->request->post()['user'];
            $file = Yii::$app->request->post()['file'];

            foreach ($places as $place=>$val) {
                if ($val == '1') { //если файл помечен к размещению в точке, то добавляем запись в базу
                    $filePlace = FilePlace::find()->where(['and','userId='.$user,'fileId='.$file,'placeId='.$place])->one();
                    if ($filePlace == null) { //если такой записи нет в базе
                        $FP = new FilePlace();
                        $FP->userId = $user;
                        $FP->fileId = $file;
                        $FP->placeId = $place;
                        $FP->confirm = 0; //неподтвержденная заявка
                        $FP->save();
                        Yii::info('Добавление заявки по файлу id='.$file.' на точку id='.$place.' в БД',__METHOD__);
                    }
                } else { //иначе удаляем этот файл и запись о файле из базы и плейлистов
                    $filePlace = FilePlace::find()->where(['and','userId='.$user,'fileId='.$file,'placeId='.$place])->one();
                    if ($filePlace != null) {
                        Yii::info('Отзыв заявки id='.$file.' на точку id='.$place,__METHOD__);
                        $filePlace->delete();
                        Yii::info('Удален файл id='.$file.' из БД',__METHOD__);
                        $fileObject = File::findOne($file);
                        $filePath = Yii::getAlias('@app')."/files/".(string)$place."/users-files/".$fileObject->user->id."_".$fileObject->fileName;
                        unlink($filePath);
                        Yii::info('Удален файл '.$filePath,__METHOD__);
                        PlayLists::deleteAll(['and','fileId='.$file,'placeId='.$place]);
                        Yii::info('Удален файл id='.$file.' из таблицы PlayLists',__METHOD__);
                        $fileStr=file(Yii::getAlias('@app')."/files/".(string)$place."/playlist.m3u");
                        $pathToVideo = rtrim(file_get_contents(Yii::getAlias('@app')."/files/".(string)$place."/path.txt"));
                        $sizeStr = sizeof($fileStr);
                        for($i=0;$i<$sizeStr;$i++){
                            if($fileStr[$i]==$pathToVideo."users-files/".$fileObject->user->id."_".$fileObject->fileName."\r\n") {
                                unset($fileStr[$i]);
                                Yii::info('Удалена запись по файлу id='.$file.' '.$pathToVideo."users-files/".$fileObject->user->id."_".$fileObject->fileName.' из плейлиста',__METHOD__);
                            }
                        }
                        $fp=fopen(Yii::getAlias('@app')."/files/".(string)$place."/playlist.m3u","w"); 
                        fputs($fp,implode("",$fileStr)); 
                        fclose($fp);
                    }
                }
            }
        }
    }
    
    //действие записи видеоблока, загруженного админом, в точку, или его удаления.
    //Работает только в режиме Админа. Работает с массивом точек, записывает или удаляет в зависимости от состояния чекбокса
    //контекстного меню видеоблока. Не требует подтверждения
    public function actionRewritePlaylist() {
        if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1)) {
            /*
            Принимает параметры:
             - Массив places - точки, в которых надо разместить видеоблок
             - file - идентификатор файла
            */
            $places = Yii::$app->request->post()['places'];
            $places = Json::decode($places);
            $file = Yii::$app->request->post()['file'];
            $ret = true;
            foreach ($places as $place=>$val) {
                if ($val == '1') { //если установлен флаг размещения файла в точке
                    $playList = PlayLists::find()->where(['and','fileId='.$file,'placeId='.$place])->one();
                    if ($playList == null) {// если файла нет в плейлисте точки, то добавляем его в конец плейлиста
                        $max = PlayLists::find()->where(['placeId'=>$place])->max('sortNumber'); 
                        $FP = new PlayLists();
                        $FP->fileId = $file;
                        $FP->placeId = $place;
                        if ($max == null) {
                          $FP->sortNumber = 1;  
                        } else {
                          $FP->sortNumber = (int)$max+1;  
                        }
                        //запись в таблицу видеоблоков
                        $VideoBlockOld = VideoBlocks::find()->where(['and','fileId='.$file,'placeId='.$place])->one();
                        if ($VideoBlockOld == null) {
                            $VideoBlock = new VideoBlocks();
                            $VideoBlock->fileId = $file;
                            $VideoBlock->placeId = $place;
                        }
                        
                        Yii::info('Назначение видеоблока точке fileId='.$file.' placeId='.$place,__METHOD__);
                        $fileObject = File::findOne($file);
                        $filePath = Yii::getAlias('@app')."/files/".(string)$place."/video-blocks/".$fileObject->fileName;
                        $filePathFrom = Yii::getAlias('@app')."/web/files/pre-actions/".(string)$fileObject->user->id."/".$fileObject->href.".".$fileObject->ext;
                        if (copy($filePathFrom, $filePath)) {//копирование файла в соответствующую папку точки
                            Yii::info('Копирование видеоблока в точку из '.$filePathFrom.' в '.$filePath,__METHOD__);
                            $FP->save();
                            Yii::info('Сохранение в таблице PlayLists записи о видеоблоке id='.$FP->fileId.' с номером порядка '.$FP->sortNumber,__METHOD__);
                            if ($VideoBlockOld == null) {
                                $VideoBlock->save();
                                Yii::info('Сохранение в таблице VideoBlocks записи о видеоблоке id='.$FP->fileId,__METHOD__);
                            }
                            $pathToVideo = rtrim(file_get_contents(Yii::getAlias('@app')."/files/".(string)$place."/path.txt"));
                            $fp=fopen(Yii::getAlias('@app')."/files/".(string)$place."/playlist.m3u","a");  
                            fwrite($fp, $pathToVideo."video-blocks/".$fileObject->fileName."\r\n");  
                            fclose($fp);
                            Yii::info('Добавление в плейлист записи о видеоблоке id='.$FP->fileId.' '.$pathToVideo."video-blocks/".$fileObject->fileName,__METHOD__);

                        } else {
                            $ret = false;
                        }
                    }
                } else { //удаление файла отовсюду, кроме удаления самого файла из базы и веб-доступной директории
                    $playList = PlayLists::find()->where(['and','fileId='.$file,'placeId='.$place])->one();
                    $fileObject = File::findOne($file);
                    if ($playList != null) {
                        
                        $filePath = Yii::getAlias('@app')."/files/".(string)$place."/video-blocks/".$fileObject->fileName;
                        if (unlink($filePath)) {
                            Yii::info('Удаление видеоблока id='.$file.' '.$filePath,__METHOD__);
                            $fileObject = File::findOne($file);
                            PlayLists::deleteAll(['and','fileId='.$file,'placeId='.$place]);
                            Yii::info('Удаление записи о видеоблоке в таблице БД PlayLists fileId='.$file.' placeId='.$place,__METHOD__);
                            $VideoBlock = VideoBlocks::find()->where(['and','fileId='.$file,'placeId='.$place])->one();
                            $VideoBlock->delete();
                            Yii::info('Удаление записи о видеоблоке в таблице БД VideoBlocks fileId='.$file.' placeId='.$place,__METHOD__);
                            $fileStr=file(Yii::getAlias('@app')."/files/".(string)$place."/playlist.m3u");
                            $pathToVideo = rtrim(file_get_contents(Yii::getAlias('@app')."/files/".(string)$place."/path.txt"));
                            $sizeStr = sizeof($fileStr);
                            for($i=0;$i<$sizeStr;$i++){
                                if($fileStr[$i]==$pathToVideo."video-blocks/".$fileObject->fileName."\r\n") {
                                    unset($fileStr[$i]);
                                    Yii::info('Удаление записи о видеоблоке в плейлисте точки '.$place.' '.$pathToVideo."video-blocks/".$fileObject->fileName,__METHOD__);
                                }
                            }
                            $fp=fopen(Yii::getAlias('@app')."/files/".(string)$place."/playlist.m3u","w"); 
                            fputs($fp,implode("",$fileStr)); 
                            fclose($fp);

                        } else {
                            $ret = false;
                        }
                    }
                    $fold ="/video-blocks/";
                    $filesInPlaces = VideoBlocks::find()->where(['fileId'=>$file])->all();
                    foreach ($filesInPlaces as $place){
                        $filePath = Yii::getAlias('@app')."/files/".(string)$place->placeId.$fold.$filesInPlaces->fileName;
                        if (file_exists($filePath)) {
                            unlink($filePath);
                            Yii::info('Удален видеоблок из папки точки id='.$file.' '.$filePath,__METHOD__);
                        }
                    }
                }
            }

            if ($ret == true) {
               return Json::encode(['success'=>true]);  
            } else {
               return Json::encode(['success'=>false,'msg'=>'Произошли ошибки при выполнении операций с файлами. Некоторые файлы не записаны/удалены']); //обычным алертом
            }
        }
    }
      
    //функция подтверждения заявки пользователя Админом. Получает для этого необходимые параметры: ID файла и точки.
    //после отработки функции в случае успешного результата появляется модальное окно редактирования плейлиста
    public function actionRewritePlaylistProp() {
        if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1)) {
            $place = Yii::$app->request->post()['place'];
            $file = Yii::$app->request->post()['file'];
            $ret = true;

            $playList = PlayLists::find()->where(['and','fileId='.$file,'placeId='.$place])->one();
            if ($playList == null) {
                $max = PlayLists::find()->where(['placeId'=>$place])->max('sortNumber'); 
                $FP = new PlayLists();
                $FP->fileId = $file;
                $FP->placeId = $place;
                if ($max == null) {
                  $FP->sortNumber = 1;  
                } else {
                  $FP->sortNumber = (int)$max+1;  
                }
                
                Yii::info('Подтверждение заявки файла id='.$file.' на точку id='.$place,__METHOD__);
                $fileObject = File::findOne($file);
                $filePath = Yii::getAlias('@app')."/files/".(string)$place."/users-files/".$fileObject->user->id."_".$fileObject->fileName;
                $filePathFrom = Yii::getAlias('@app')."/web/files/pre-actions/".(string)$fileObject->user->id."/".$fileObject->href.".".$fileObject->ext;
                if (copy($filePathFrom, $filePath)) { //копирование файла в папку точки
                    Yii::info('Копирование файла в папку точки id='.$file.' на точку id='.$place.' '.$filePathFrom.' '.$filePath,__METHOD__);
                    $FP->save(); //запись в конец плейлиста нового файла
                    Yii::info('Запись в таблицу PlayLists файла id='.$file.' на точку id='.$place,__METHOD__);
                    $pathToVideo = rtrim(file_get_contents(Yii::getAlias('@app')."/files/".(string)$place."/path.txt"));
                    $fp=fopen(Yii::getAlias('@app')."/files/".(string)$place."/playlist.m3u","a");  
                    fwrite($fp, $pathToVideo."users-files/".$fileObject->user->id."_".$fileObject->fileName."\r\n");  
                    fclose($fp);
                    Yii::info('Запись в файл плейлиста точки id='.$place.' файла '.$pathToVideo."users-files/".$fileObject->user->id."_".$fileObject->fileName,__METHOD__);
                    $filePlace = FilePlace::find()->where(['and','userId='.$fileObject->user->id,'fileId='.$file,'placeId='.$place])->one();
                    if ($filePlace != null) {
                        $filePlace->confirm = 1;//теперь файл имеет статус подтвержденого
                        $filePlace->save();
                        Yii::info('Запись в таблицу БД FilePlace файла id='.$file.' в точку id='.$place,__METHOD__);
                    }
                } else {
                    $ret = false;
                }
            }


            if ($ret == true) {
               return Json::encode(['success'=>true]);  
            } else {
               return Json::encode(['success'=>false,'msg'=>'Произошли ошибки при выполнении операций с файлами. Некоторые файлы не записаны/удалены']);//алертом
            }
        }
    }

    public function actionAfterCoding(){
        if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1)) {
            $file = Yii::$app->request->post()['file'];
            $fileObject = File::findOne($file);
            
            $href_old = $fileObject->href;
            $name_old = $fileObject->fileName; 
            
            $fileObject->fileName = explode(".",$fileObject->fileName)[0].".mkv";
            $fileObject->ext = 'mkv';
            $fileObject->save(false,NULL,'fileName');
            Yii::info('Перезапись имени файла в БД на '.explode(".",$fileObject->fileName)[0].".mkv",__METHOD__);
            if ($fileObject->videoBlock == NULL){
                $filesInPlaces = FilePlace::find()->where(['fileId'=>$file,'confirm'=>'1'])->all();
            } else {
                $filesInPlaces = VideoBlocks::find()->where(['fileId'=>$file])->all();
            }
            $filesInPlaylists = PlayLists::find()->where(['fileId'=>$file])->all();//поиск всех вхождений файла в плейлисты
            if ($fileObject->videoBlock == NULL) {
               $fold ="/users-files/";
               $fold2 = "users-files/";
            } else {
               $fold ="/video-blocks/";
               $fold2 = "video-blocks/";
            }          
            
            foreach ($filesInPlaces as $fileInPlace) {
                 $place = $fileInPlace->placeId;
                 if ($fileObject->videoBlock == NULL){
                     $fileNameOld = Yii::getAlias('@app')."/files/".(string)$place.$fold.$fileObject->userId."_".$name_old;
                     $fileNameNew = Yii::getAlias('@app')."/files/".(string)$place.$fold.$fileObject->userId."_".$fileObject->fileName;
                 } else {
                     $fileNameOld = Yii::getAlias('@app')."/files/".(string)$place.$fold.$name_old;
                     $fileNameNew = Yii::getAlias('@app')."/files/".(string)$place.$fold.$fileObject->fileName;                            
                 }
                 unlink($fileNameOld);
                 Yii::info('Удаление файла '.$fileNameOld,__METHOD__);
                 $name_new = Yii::getAlias('@app')."/web/files/pre-actions/".(string)$fileObject->userId."/".$fileObject->href.".mkv";
                 copy($name_new,$fileNameNew);
                 Yii::info('Копирование файла из '.$name_new.' в '.$fileNameNew,__METHOD__);
             }
             //переименование записей о файлах пользователей и видеоблоках в файлах плейлистов. Файлы соответствуют записям в таблице PlayLists.
             //Один файл может встречаться несколько раз в одном плейлисте
             foreach ($filesInPlaylists as $fileInPlace) {
                 $place = $fileInPlace->placeId;
                 if ($fileObject->videoBlock == NULL){
                     $fileNameOld = Yii::getAlias('@app')."/files/".(string)$place.$fold.$fileObject->userId."_".$name_old;
                     $fileNameNew = Yii::getAlias('@app')."/files/".(string)$place.$fold.$fileObject->userId."_".$fileObject->fileName;
                 } else {
                     $fileNameOld = Yii::getAlias('@app')."/files/".(string)$place.$fold.$name_old;
                     $fileNameNew = Yii::getAlias('@app')."/files/".(string)$place.$fold.$fileObject->fileName;                            
                 }

                 $fileStr=file(Yii::getAlias('@app')."/files/".(string)$place."/playlist.m3u");
                 $pathToVideo = rtrim(file_get_contents(Yii::getAlias('@app')."/files/".(string)$place."/path.txt"));//парс файла в массив строк
                 $sizeStr = sizeof($fileStr);
                 for($i=0;$i<$sizeStr;$i++){ //просмотр всех записей плейлиста и переименование только нужных записей
                     if ($fileObject->videoBlock == NULL){
                         if ($fileStr[$i]==$pathToVideo.$fold2.$fileObject->userId."_".$name_old."\r\n") {
                             $fileStr[$i] = $pathToVideo.$fold2.$fileObject->userId."_".$fileObject->fileName."\r\n";
                             Yii::info('Переименование файла в плейлисте на '.$pathToVideo.$fold2.$fileObject->userId."_".$fileObject->fileName.' в строке '.$i,__METHOD__);
                         }
                     } else {
                         if ($fileStr[$i]==$pathToVideo.$fold2.$name_old."\r\n") {
                             $fileStr[$i] = $pathToVideo.$fold2.$fileObject->fileName."\r\n";
                             Yii::info('Переименование файла в плейлисте на '.$pathToVideo.$fold2.$fileObject->fileName.' в строке '.$i,__METHOD__);
                         }                                    
                     }
                 }
                 //перезапись файла плейлиста
                 $fp=fopen(Yii::getAlias('@app')."/files/".(string)$place."/playlist.m3u","w"); 
                 fputs($fp,implode("",$fileStr)); 
                 fclose($fp);

             }
            
            Yii::info('Конец кодирования файла id='.$file,__METHOD__); 
        }
    }
    
    public function actionCoding() {
         if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1)) {
            $file = Yii::$app->request->post()['file'];
            $fileObject = File::findOne($file);
            
             if (($fileObject != NULL)&&($fileObject->isCoding == NULL)) {
                Yii::info('Начало кодирования файла id='.$fileObject->id,__METHOD__); 
                $href = $fileObject->generateStr(explode(".",$fileObject->fileName)[0].".mkv" . "elfxf", 1);
                $filePath = Yii::getAlias('@app')."/web/files/pre-actions/".(string)$fileObject->user->id."/".$href.".mkv";
                $filePathFrom = Yii::getAlias('@app')."/web/files/pre-actions/".(string)$fileObject->user->id."/".$href."_original";
                $fileOutputLog = Yii::getAlias('@app')."/web/files/pre-actions/log.txt";

                $fp = fopen($fileOutputLog, 'a'); //Открываем файл в режиме записи
                ftruncate($fp, 0); // очищаем файл
                fclose($fp);

                $href_old = $fileObject->href;
                $ext_old = $fileObject->ext;
                rename(Yii::$app->params['uploadPath'] . "/" . $fileObject->userId. "/".$href_old.".".$ext_old,Yii::$app->params['uploadPath'] . "/" . $fileObject->userId . "/".$href."_original");
                Yii::info('Переименование файла с '.Yii::$app->params['uploadPath'] . "/" . $fileObject->userId. "/".$href_old.".".$ext_old. ' на '.Yii::$app->params['uploadPath'] . "/" . $fileObject->userId . "/".$href."_original",__METHOD__);  
                exec('(mencoder '.$filePathFrom.'   -o '.$filePath.'  -of lavf   -oac mp3lame   -ovc x264 -vf scale=1280:-2 ; echo "endfile") > '.$fileOutputLog.' &'); //копирование файла в папку точки с перекодированием

             } else {
                 return "error";
             }
        }
    }
    
    public function actionRewritePlaylistPropCode() {
        if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1)) {
            $place = Yii::$app->request->post()['place'];
            $file = Yii::$app->request->post()['file'];
            $ret = true;

            $playList = PlayLists::find()->where(['and','fileId='.$file,'placeId='.$place])->one();
            if ($playList == null) {
                $max = PlayLists::find()->where(['placeId'=>$place])->max('sortNumber'); 
                $FP = new PlayLists();
                $FP->fileId = $file;
                $FP->placeId = $place;
                if ($max == null) {
                  $FP->sortNumber = 1;  
                } else {
                  $FP->sortNumber = (int)$max+1;  
                }

                $fileObject = File::findOne($file);
                $filePath = Yii::getAlias('@app')."/files/".(string)$place."/users-files/".$fileObject->user->id."_".explode(".",$fileObject->fileName)[0].".mkv";
                $filePathFrom = Yii::getAlias('@app')."/web/files/pre-actions/".(string)$fileObject->user->id."/".$fileObject->href.".".$fileObject->ext;
                $fileOutputLog = Yii::getAlias('@app')."/web/files/pre-actions/log.txt";
                
                $fp = fopen($fileOutputLog, 'a'); //Открываем файл в режиме записи
                ftruncate($fp, 0); // очищаем файл
                fclose($fp);
                
                exec('(mencoder '.$filePathFrom.'   -o '.$filePath.'  -of lavf   -oac mp3lame   -ovc x264 -vf scale=1280:-2 ; echo "endfile") > '.$fileOutputLog.' &'); //копирование файла в папку точки с перекодированием
                $href_old = $fileObject->href;
                $ext_old = $fileObject->ext;
                        
                $fileObject->fileName = explode(".",$fileObject->fileName)[0].".mkv";
                $fileObject->ext = 'mkv';
                $fileObject->save(false,NULL,'fileName');
                
                rename(Yii::$app->params['uploadPath'] . "/" . $fileObject->userId. "/".$href_old.".".$ext_old,Yii::$app->params['uploadPath'] . "/" . $fileObject->userId . "/".$fileObject->href.".".$fileObject->ext);
                
                $FP->save(); //запись в конец плейлиста нового файла
                $pathToVideo = rtrim(file_get_contents(Yii::getAlias('@app')."/files/".(string)$place."/path.txt"));
                $fp=fopen(Yii::getAlias('@app')."/files/".(string)$place."/playlist.m3u","a");  
                fwrite($fp, $pathToVideo."users-files/".$fileObject->user->id."_".$fileObject->fileName."\r\n");  
                fclose($fp);
                $filePlace = FilePlace::find()->where(['and','userId='.$fileObject->user->id,'fileId='.$file,'placeId='.$place])->one();
                if ($filePlace != null) {
                    $filePlace->confirm = 1;//теперь файл имеет статус подтвержденого
                    $filePlace->save();
                }
            }


            if ($ret == true) {
               return Json::encode(['success'=>true]);  
            } else {
               return Json::encode(['success'=>false,'msg'=>'Произошли ошибки при выполнении операций с файлами. Некоторые файлы не записаны/удалены']);//алертом
            }
        }
    }
    
    //функция удаления (отказа) заявки пользователя. После отработки функции появляется модальное окно отправки письма на е-мейл пользователя
    //о причине отказа в заявке на размещение видеофайла в точке.
     public function actionDeleteProp() {
        if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1)) { 
            $place = Yii::$app->request->post()['place'];
            $file = Yii::$app->request->post()['file'];
            $fileObject = File::findOne($file);
            $filePlace = FilePlace::find()->where(['and','userId='.$fileObject->user->id,'fileId='.$file,'placeId='.$place])->one();
            if ($filePlace != null) {
                $filePlace->delete();//удаляется только cама заявка из базы, больше ничего и не требуется
                Yii::info('Удаление заявки файла id='.$file.' на точку id='.$place,__METHOD__); 
            }    
            return Json::encode(['success'=>true]);
        }
     }
     
     //функция перезаписи содержимого плейлиста (файла и БД), соответсвующего точке. Исходные данные для перезаписи - 
     //список плейлиста в режиме редактирования плейлиста. Старый плейлист полностью затирается, и записывается новый.
     //Дописать сообщение об успешном выполнении перезаписи плейлиста
     public function actionWritePlaylist(){
        if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1)) {
            $files = Yii::$app->request->post()['files'];
            $files = Json::decode($files);
            $place = Yii::$app->request->post()['place'];

            PlayLists::deleteAll(['placeId'=>$place]);//удаление всех записей о точке (всего плейлиста точки)
            $pathToVideo = rtrim(file_get_contents(Yii::getAlias('@app')."/files/".(string)$place."/path.txt"));
            $fp=fopen(Yii::getAlias('@app')."/files/".(string)$place."/playlist.m3u","w");//открытие файла плейлиста для записи с затиранием предыдущей инфы       
            foreach ($files as $numb=>$fileId) {
                $playlist = new PlayLists();
                $playlist->fileId = $fileId;
                $playlist->placeId = $place;
                $playlist->sortNumber = $place;
                $playlist->save();//новая запись в плейлисте БД
                $fileObject = File::findOne($fileId);
                if ($fileObject->videoBlock=='1') { // новая запись в файле плейлиста
                    fwrite($fp, $pathToVideo."video-blocks/".$fileObject->fileName."\r\n"); 
                } else {
                    fwrite($fp, $pathToVideo."users-files/".$fileObject->user->id."_".$fileObject->fileName."\r\n");
                }

            }
            Yii::info('Перезапись плейлиста точки id='.$place,__METHOD__);
            fclose($fp);
        }
     }
    //действие просмотра всех плейлистов. Плейлист доступен после клика на название точки из списка точек. 
    public function actionPlaylists() {    
        if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1)) {
            $providerPlace = new ActiveDataProvider([
                'query' => Place::find(),
                'pagination' => [
                'pageSize' => 20,
                 ],
            ]);    
            
            return $this->render('playlistsview',
                  [
                      'providerPlace'=>$providerPlace
                  ]);            
        } else {
            return $this->render('error',[]);
        }           
    }
    // функция отправки сообщения пользователю на е-мейл с сообщение об удалении его заявки (отказе)
    public function actionSendMailToUser() {    
        if ((Yii::$app->user->isGuest === false)&&(Yii::$app->user->identity->admin === 1)) {
            $fileId = Yii::$app->request->post('id');
            $mail = Yii::$app->request->post('mail');
            $email = File::findOne($fileId)->user->email;
            Yii::$app->mailer->compose()
                ->setTo($email)
                ->setFrom(['vorobyev.it@gmail.com'])
                ->setSubject('Bel-video')
                ->setTextBody($mail)
                ->send();
            Yii::info('Сообщение об удалении точки отпрвалено на email='.$email,__METHOD__);
        }           
    }
    
    //вспомогательная фукнция генерации 5 случайных символов (0-9) для промо-кода
    protected function generatePromo(){
        $chars="0123456789"; 
        $max=5; 
        $size=StrLen($chars)-1; 
        $password=null; 
        while($max--) 
            $password.=$chars[rand(0,$size)]; 
        return $password;
    }
   
    //функция генерации промо-кода и сохранения его в БД
    public function actionGeneratePromo(){
        $pass = "";  
        $pass.=$this->generatePromo()."-";
        $pass.=$this->generatePromo()."-";
        $pass.=$this->generatePromo();
        $promoDB = PromoKeys::find()->where(['promo'=>$pass])->all();
        if ($promoDB  == null) {//не дает записать промо, который уже существует, но такое наврядли возможно
            $promo = new PromoKeys();
            $promo->promo = $pass;
            $promo->save();
            Yii::info('Сгенерирован промо-код '.$pass,__METHOD__);
        }
    }
    
    // удаляет промо-код из БД
    public function actionDeletePromo(){
        $promoId = Yii::$app->request->post('id');
        $promoDB = PromoKeys::findOne($promoId);
        if ($promoDB  != null) {
            $promoDB->delete();
            Yii::info('Удален промо-код '.$promoDB->promo,__METHOD__);
        }
    }
    
     public function actionCheckFile(){
         $fileOutputLog = Yii::getAlias('@app')."/web/files/pre-actions/log.txt";
        $f = fopen($fileOutputLog, "r");
        if($f){
            if(fseek($f, -1, SEEK_END) == 0){//в конец файла -1 символ перевода строки
                $len = ftell($f);
                for($i = $len; $i > ($len-75); $i--){//75 - длина строки
                    fseek($f, -1, SEEK_CUR);
                }
                $ret = fread($f, $len - $i);//последняя строка
            }
            
//            fseek($f, -1, SEEK_END);
//            $len = ftell($f);
//            $ret = fread($f, $len - 75);
//            fclose($f);
        } 
        return Html::encode($ret);
     } 
     
    public function actionCheckCpu(){
        return shell_exec('top -bn 1 | awk \'NR>7{s+=$9} END {print s/4}\'');
    }
}
