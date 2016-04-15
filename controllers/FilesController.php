<?php



namespace app\controllers;
use Yii;
use yii\web\Controller;
use app\models\File;
use app\models\FilePlace;
use yii\helpers\Json;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Url;


class FilesController extends Controller{
    public $kk;
 
    public function ResizeImage ($filename, $n_width,$n_height , $quality = 85, $path_save, $new_filename)
    {
        /*
        * Адрес директории для сохранения картинки
        */
        $dir=dirname($filename)."/".$path_save."/";
        
        /*
        * Извлекаем формат изображения, то есть получаем 
        * символы находящиеся после последней точки
        */
        $ext=strtolower(end((explode(".", $filename))));
        
        /*
        * Допустимые форматы
        */
        $extentions = array('jpg', 'gif', 'png', 'bmp');
    
        if (in_array($ext, $extentions)) {   
              // Высота изображения миниатюры
        
             list($width, $height) = getimagesize($filename); // Возвращает ширину и высоту
             if ($width>$n_width) {
                 if ($height>$n_height){
                     if (($width/$n_width)>($height/$n_height)){
                        $newheight    = $n_width * $height/$width;
                        $newwidth    = $n_width;
                     } else {
                        $newwidth    = $n_height * $width/$height; 
                        $newheight    = $n_height;
                     }
                 } else {
                        $newheight    = $n_width * $height/$width;
                        $newwidth    = $n_width;
                 }
             } else {
                 if ($height>$n_height) {
                        $newwidth    = $n_height * $width/$height; 
                        $newheight    = $n_height;
                 } else {
                     $newwidth=$width;
                     $newheight=$height;
                 }
             }

        
             $thumb = imagecreatetruecolor($newwidth, $newheight);
        
             switch ($ext) {
                 case 'jpg':
                     $source = @imagecreatefromjpeg($filename);
                     break;
                
                  case 'gif':
                     $source = @imagecreatefromgif($filename);
                     break;
                
                  case 'png':
                     $source = @imagecreatefrompng($filename);
                     break;
                
                  case 'bmp':
                      $source = @imagecreatefromwbmp($filename);
              }
    
            /*
            * Функция наложения, копирования изображения
            */
            imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        
            /*
            * Создаем изображение
            */
            switch ($ext) {
                case 'jpg':
                    imagejpeg($thumb, $dir . $new_filename, $quality);
                    break;
                    
                case 'gif':
                    imagegif($thumb, $dir . $new_filename);
                    break;
                    
                case 'png':
                    imagepng($thumb, $dir . $new_filename, $quality);
                    break;
                    
                case 'bmp':
                    imagewbmp($thumb, $dir . $new_filename);
                    break;
            }    
    } else {
        return false;
    }
    
    /* 
    *  Очищаем оперативную память сервера от временных файлов, 
    *  которые потребовались для создания миниатюры
    */
        @imagedestroy($thumb);         
        @imagedestroy($source);  
            
        return true;
    }
    
    public function actionAdd(){
       $model = new File();
       $oldFile = $model->getProfilePictureFile();
       $oldProfilePic = $model->profile_pic;
       $kk=Yii::$app->request->post();
       if ($model->load(Yii::$app->request->post())) {

           // process uploaded image file instance
           $image = $model->uploadProfilePicture();

           if($image === false && !empty($oldProfilePic)) {
               $model->profile_pic = $oldProfilePic;
           }

          // if ($model->save()) {
               // upload only if valid uploaded file instance found
               if ($image !== false) { // delete old and overwrite
                   if(!empty($oldFile) && file_exists($oldFile)) {
                       unlink($oldFile);
                   }
                   $path = $model->getProfilePictureFile();
                   $image->saveAs($path);
                   list($width, $height) = getimagesize($path);
                   $this->ResizeImage ($path, 100, 60, 85, "thumbnail",basename($path));
                   $this->ResizeImage ($path, 1024, 600, 85, "medium",basename($path));

               }
               \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
               $kk=$model->getProfilePictureUrl();
                try {
                    $exif = exif_read_data($path, 'IFD0');
                    if ($exif!==false) {
                        $exif["path"]=$path;
                        $model->save(false,$exif,$model->profile_pic,$image->size);
                    } else {
                        $model->save(false,$path,$model->profile_pic,$image->size);
                    }
                }
                catch (Exception $e) {
                    $model->save(false,$path,$model->profile_pic,$image->size);
                }
                $kkk=$model->getProfilePictureUrl("medium/");
                $kkkk=$model->getProfilePictureUrl("thumbnail/");
               return ["files"=> [
  [
    "name"=> $model->profile_pic,
    "size"=> $image->size,
    "url"=> $kkk,
    "thumbnailUrl"=> $kkkk,
    "deleteUrl"=> $kk,
    "deleteType"=> "DELETE"
  ]
]];
          // }
       }
    }
    
    public function actionAdd2() {
        $model = new File();
        $model->file = $model->uploadProfilePicture();
        $dir=$model->getProfilePictureFile();
 
        if ($model->validate()) {
            $uploaded = $model->file->saveAs($dir);
            $isError = $model->save(false,"");
            
            $info = new \SplFileInfo($dir);
            $mimeType = FileHelper::getMimeType($dir, null, false);
            $mes = "";
            if ($mimeType === null) {
                $mes = "Не удается распознать MIME-тип";
            } else {

                $extensionsByMimeType = FileHelper::getExtensionsByMimeType($mimeType);

                if ($info->getExtension()!=$extensionsByMimeType) {
                    $mes = "Тип файла не совпадает с его MIME-типом";
                }
            }
            if ($isError != false){
                if ($mes!=""){
                    $isError=$isError."<li>".$mes."</li>";
                }
                echo Json::encode(['error'=>"Файл загружен и сохранен, но при его записи возникли следующие ошибки:<br/><ul>".$isError."</ul>Возможно, следует перезагрузить этот файл, следуя ".Html::a('рекомендациям', Url::to(["helpers/view",'id'=>'upload','#'=>'rekommend'],true),['target'=>'_blank'])."<br/>Чтобы увидеть загруженный файл в списке файлов выше, ".Html::a('обновите', Url::to(["site/files"],true))." страницу"]);
            } else {
                echo Json::encode([]);
            }
            
        } else {
            echo Json::encode(['error'=>$model->getFirstError('file')]);
            //echo Json::encode(['error'=>"Ошибка проверки загрузки файла на сервере. <br/>Разрешенные форматы файла: 'flv', 'avi', 'mov','mp4','mpg','mpeg','mpe','mp2v','m2v','m2s','wmv','qt','3gp','asf','rm','mkv'.<br/>Разрешенный размер: до 300МБ. <br/>Количество одновременно загружаемых файлов: 1"]);
        }
    }
    
    public function actionDelFileById(){
        $id=Yii::$app->request->post()['id'];
        $file=File::findOne($id);
        if (unlink(Yii::$app->params['uploadPath'] . "/" . Yii::$app->user->identity->id . "/".$file->href.".".$file->ext)) {
            $file->delete();
        }
    }
    
    public function actionView() {
        $href = Yii::$app->request->get()['href'];
        $file = new File();
        $video = $file->getVideoByHref($href);
        return $this->render('view',
                [
                    'video'=>$video
                ]);
    }
    
    public function actionChangeFilename() {
        $name=Yii::$app->request->post()['name'];
        $id=Yii::$app->request->post()['id'];
        if (preg_match("/(^[a-zа-яA-ZА-Я0-9]+([a-zа-яA-ZА-Я\_0-9-]*))$/u" , $name)==NULL) {
            echo Json::encode(['error'=>'Недопустимое имя файла']);
        
        } else {
            if (isset($name)) {
                $file = File::findOne($id);
                $file2 = File::find()->where(['and','fileName="'.$this->str2url($name).".".$file->ext.'"','userId='.Yii::$app->user->identity->id])->all();
                if ($file2 == null) {
                    $file->fileName = $this->str2url($name).".".$file->ext;
                    $href_old = $file->href;
                    $file->save(false,NULL,'change');
                    rename(Yii::$app->params['uploadPath'] . "/" . Yii::$app->user->identity->id . "/".$href_old.".".$file->ext,Yii::$app->params['uploadPath'] . "/" . Yii::$app->user->identity->id . "/".$file->href.".".$file->ext);
                    echo Json::encode(['success'=>true]);
                } else {
                    echo Json::encode(['error'=>'Такое имя уже есть у другого файла']);
                }
            }
        }
    }
    
    public function actionCheckByName() {
        $name=$this->str2url(Yii::$app->request->post()['name']);
        $userId=Yii::$app->request->post()['user'];
        $file = File::find()->where(['and','fileName="'.$this->str2url($name).'"','userId='.$userId])->all();
//        return Json::encode(['success'=>$this->str2url($name)]);
        if ($file == null) {
            return Json::encode(['success'=>true]);
        } else {
            return Json::encode(['success'=>false]);
        }
    }
    
    
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

    public function str2url($str) {
        // переводим в транслит
        
        $str = str_replace(" ","_",$str);
        $str = preg_replace('|_+|', '_', $str);
        $str = $this->rus2translit($str);
        // в нижний регистр
        $str = strtolower($str);
        // заменям все ненужное нам на "-"
        $str = preg_replace('~[^a-z0-9_\.]+~u', "_", $str);
        $str = preg_replace('|_+|', '_', $str);
        // удаляем начальные и конечные '-'
        //$str = trim($str, "-");
        return $str;
    }
    
    public function actionRewriteFilePlaces() {
        $places = Yii::$app->request->post()['places'];
        $places = Json::decode($places);
        $user = Yii::$app->request->post()['user'];
        $file = Yii::$app->request->post()['file'];
        
        foreach ($places as $place=>$val) {
            if ($val == '1') {
                $filePlace = FilePlace::find()->where(['and','userId='.$user,'fileId='.$file,'placeId='.$place])->one();
                if ($filePlace == null) {
                    $FP = new FilePlace();
                    $FP->userId = $user;
                    $FP->fileId = $file;
                    $FP->placeId = $place;
                    $FP->confirm = 0;
                    $FP->save();
                }
            } else {
                $filePlace = FilePlace::find()->where(['and','userId='.$user,'fileId='.$file,'placeId='.$place])->one();
                if ($filePlace != null) {
                    $filePlace->delete();
                }
            }
        }
    }
}
