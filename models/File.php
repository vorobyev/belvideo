<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;
use FFMpeg\FFProbe;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate;
use FFMpeg\Format;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\base\ErrorException;
use yii\base\ExitException;
use app\models\User;

class File Extends ActiveRecord {

    public $profile_pic;
    public $file;

    public function rules() {
       return [
            ['file','file', 'extensions' => ['flv', 'avi', 'mov','mp4','mpg','mpeg','mpe','mp2v','m2v','m2s','wmv','qt','3gp','asf','rm','mkv'],'wrongExtension'=>"Неизвестный формат файла. ".Html::a('Подробнее', Url::to(["helpers/view",'id'=>'upload','#'=>'ext'],true),['target'=>'_blank']),'checkExtensionByMimeType'=>false],
            ['file','file', 'maxSize' => 1024*1024*300, 'tooBig' => 'Ошибка загрузки файла на сервере:<br/>Размер файла не должен превышать 300 МБ! '.Html::a('Подробнее', Url::to(["helpers/view",'id'=>'upload','#'=>'size'],true),['target'=>'_blank'])],
            ['file','file', 'maxFiles' => 1,'tooMany'=>'Разрешена загрузка не более одного файла'.Html::a('Подробнее', Url::to(["helpers/view",'id'=>'upload','#'=>'count'],true),['target'=>'_blank'])]
            ];        
    }
    
    public static function tableName() {
        return "Files";
    }

    public function upload($filesize) {
        
    }

    public function filesize_get_bit($filesize) {
        // Если размер переданного в функцию файла больше 1кб 
        if ($filesize > 1000) {
            $filesize = ($filesize / 1000);
            // если размер файла больше одного килобайта 
            // пересчитываем в мегабайтах 
            if ($filesize > 1000) {
                $filesize = ($filesize / 1000);
                // если размер файла больше одного мегабайта 
                // пересчитываем в гигабайтах 
                if ($filesize > 1000) {
                    $filesize = ($filesize / 1000);
                    $filesize = round($filesize, 1);
                    return $filesize . " Гбит";
                } else {
                    $filesize = round($filesize, 1);
                    return $filesize . " Мбит";
                }
            } else {
                $filesize = round($filesize, 1);
                return $filesize . " Кбит";
            }
        } else {
            $filesize = round($filesize, 1);
            return $filesize . " бит";
        }
    }

    public function filesize_get($filesize) {
        // Если размер переданного в функцию файла больше 1кб 
        if ($filesize > 1024) {
            $filesize = ($filesize / 1024);
            // если размер файла больше одного килобайта 
            // пересчитываем в мегабайтах 
            if ($filesize > 1024) {
                $filesize = ($filesize / 1024);
                // если размер файла больше одного мегабайта 
                // пересчитываем в гигабайтах 
                if ($filesize > 1024) {
                    $filesize = ($filesize / 1024);
                    $filesize = round($filesize, 1);
                    return $filesize . " ГБ";
                } else {
                    $filesize = round($filesize, 1);
                    return $filesize . " МБ";
                }
            } else {
                $filesize = round($filesize, 1);
                return $filesize . " КБ";
            }
        } else {
            $filesize = round($filesize, 1);
            return $filesize . " Байт";
        }
    }

    public function save($runValidation = false, $attributeNames = NULL, $mode = NULL) {
        $errorMessage = "";
        if ($mode == 'change') {
           $this->href = $this->generateStr($this->fileName . "elfxf", 1); 
        } else if ($mode == 'fileName') {
            $this->isCoding = 1;
            $this->href = $this->generateStr($this->fileName . "elfxf", 1);
            $ffprobe = FFProbe::create(array(
                    'ffmpeg.binaries' => '/home/prog/FFMPEG/ffmpeg/ffmpeg',
                    'ffprobe.binaries' => '/home/prog/FFMPEG/ffmpeg/ffprobe',
            ));
            $probe = $ffprobe->format(Yii::$app->params['uploadPath'] . "/" . $this->userId . "/" . $this->href.".".$this->ext);
            if ($probe!=null) {
                $this->size = $this->filesize_get((int) $probe->get('size'));
                $this->duration = date("H:i:s", (int) $probe->get('duration'));
                $this->bitrate = $this->filesize_get_bit((int) $probe->get('bit_rate')) . "/сек";
                $this->longName = $probe->get('format_long_name');
            } else {
                try {
                    $this->size = $this->filesize_get((int)filesize(Yii::$app->params['uploadPath'] . "/" . $this->userId . "/" . $this->href.".".$this->ext));
                } catch (ErrorException $e) {
                    $this->size = "Неизвестно";
                }
                $this->duration = "Неизвестно";
                $this->bitrate = "Неизвестно";
                $this->longName = "Неизвестно";

                $errorMessage = $errorMessage."<li>Не удалось определить основные данные видеофайла</li>";
            }
            
            
            $videoInfo = "";
            $videoMeta = $ffprobe->streams(Yii::$app->params['uploadPath'] . "/" . $this->userId . "/" . $this->href.".".$this->ext)->videos()->first();

            if ($videoMeta!=null) {
                $codec_name = $videoMeta->get('codec_long_name');
                $codec_tag_string = $videoMeta->get('codec_tag_string');
                $videoInfo = $videoInfo."<div class=\"metaInfo\">Кодек: </div>"."<div class=\"metaInfoData\">".$codec_name." (".$codec_tag_string.")</div><br/>";

                $codec_name = $videoMeta->get('codec_name');
                $videoInfo = $videoInfo."<div class=\"metaInfo\">Кодек (сокр.): </div>"."<div class=\"metaInfoData\">".$codec_name."</div><br/>";

                $profile = $videoMeta->get('profile');
                $videoInfo = $videoInfo."<div class=\"metaInfo\">Профиль : </div>"."<div class=\"metaInfoData\">".$profile."</div><br/>";

                $width = $videoMeta->get('width');
                $height = $videoMeta->get('height');
                $videoInfo = $videoInfo."<div class=\"metaInfo\">Разрешение: </div>"."<div class=\"metaInfoData\">".$width."х".$height."</div><br/>";

                $display_aspect_ratio = $videoMeta->get('display_aspect_ratio');
                $videoInfo = $videoInfo."<div class=\"metaInfo\">Соотношение сторон: </div>"."<div class=\"metaInfoData\">".$display_aspect_ratio."</div><br/>";

                $frame_rate = explode("/", $videoMeta->get('r_frame_rate'));
                $videoInfo = $videoInfo."<div class=\"metaInfo\">Количество кадров в секунду: </div>"."<div class=\"metaInfoData\">".$frame_rate[0]."</div><br/>";

                $duration = date("H:i:s", (int) $videoMeta->get('duration'));
                $videoInfo = $videoInfo."<div class=\"metaInfo\">Длительность: </div>"."<div class=\"metaInfoData\">".$duration."</div><br/>";      

                $bit_rate = $this->filesize_get_bit((int) $videoMeta->get('bit_rate')) . "/сек";
                $videoInfo = $videoInfo."<div class=\"metaInfo\">Битрейт: </div>"."<div class=\"metaInfoData\">".$bit_rate."</div><br/>";

                $this->videoInfo = $videoInfo;
            } else {
                $this->videoInfo = "Неизвестно";
                $errorMessage = $errorMessage."<li>Не удалось определить данные по видеопотоку</li>";
            }


            $audioInfo = "";
            $audioMeta = $ffprobe->streams(Yii::$app->params['uploadPath'] . "/" . $this->userId . "/" . $this->href.".".$this->ext)->audios()->first();    
            if ($audioMeta!=null) {
                $codec_name = $audioMeta->get('codec_long_name');

                $codec_tag_string = $audioMeta->get('codec_tag_string');
                $audioInfo = $audioInfo."<div class=\"metaInfo\">Кодек: </div>"."<div class=\"metaInfoData\">".$codec_name." (".$codec_tag_string.")</div><br/>";  

                $codec_name = $audioMeta->get('codec_name');
                $audioInfo = $audioInfo."<div class=\"metaInfo\">Кодек (сокр.): </div>"."<div class=\"metaInfoData\">".$codec_name."</div><br/>";  

                $profile = $audioMeta->get('profile');
                $audioInfo = $audioInfo."<div class=\"metaInfo\">Профиль: </div>"."<div class=\"metaInfoData\">".$profile."</div><br/>"; 

                $channels = $audioMeta->get('channels');
                $audioInfo = $audioInfo."<div class=\"metaInfo\">Каналы: </div>"."<div class=\"metaInfoData\">".$channels."</div><br/>"; 

                $channel_layout = $audioMeta->get('channel_layout');
                $audioInfo = $audioInfo."<div class=\"metaInfo\">Канал: </div>"."<div class=\"metaInfoData\">".$channel_layout."</div><br/>";        

                $duration = date("H:i:s", (int) $audioMeta->get('duration'));
                $audioInfo = $audioInfo."<div class=\"metaInfo\">Длительность: </div>"."<div class=\"metaInfoData\">".$duration."</div><br/>";       

                $bit_rate = $this->filesize_get_bit((int) $audioMeta->get('bit_rate')) . "/сек";
                $audioInfo = $audioInfo."<div class=\"metaInfo\">Битрейт: </div>"."<div class=\"metaInfoData\">".$bit_rate."</div><br/>";         

                $this->audioInfo = $audioInfo;
                } else {
                $this->audioInfo = "Неизвестно";
                $errorMessage = $errorMessage."<li>Не удалось определить данные по аудиопотоку</li>";
            }            
        } else {

        $ffprobe = FFProbe::create(array(
                    'ffmpeg.binaries' => '/home/prog/FFMPEG/ffmpeg/ffmpeg',
                    'ffprobe.binaries' => '/home/prog/FFMPEG/ffmpeg/ffprobe',
        ));
        
        if (strpos($this->profile_pic,".")!=false) {
            $ext = explode(".",$this->profile_pic)[1];
            $this->ext = $ext;
        } else
        {
            $ext = "";
            $this->ext = $ext;
        }
        
        $this->fileName = $this->str2url($this->profile_pic);
        
        $get1 = Yii::$app->request->get();
        if (array_key_exists('videoBlock',$get1)) {
            $this->videoBlock = 1;
        }
        
        $this->href = $this->generateStr($this->fileName . "elfxf", 1);
        
        $get = Yii::$app->request->get();
        if (array_key_exists('userId',$get)) {
            $userIdMy = $get['userId'];
        } else {
            $userIdMy = Yii::$app->user->identity->id;
        }
        $this->userId = $userIdMy;

        $probe = $ffprobe->format(Yii::$app->params['uploadPath'] . "/" . $userIdMy . "/" . $this->href.".".$ext);
        if ($probe!=null) {
        
        $this->size = $this->filesize_get((int) $probe->get('size'));
          
        $this->duration = date("H:i:s", (int) $probe->get('duration'));
        $this->bitrate = $this->filesize_get_bit((int) $probe->get('bit_rate')) . "/сек";
        $this->longName = $probe->get('format_long_name');
        $tags = $probe->get('tags');
        if (isset($tags['creation_time'])) {
            $this->createDate = date("Y-m-d H:i:s", strtotime($tags['creation_time']));
        }
        } else {
            try {
                $this->size = $this->filesize_get((int)filesize(Yii::$app->params['uploadPath'] . "/" . $userIdMy . "/" . $this->href.".".$ext));
            } catch (ErrorException $e) {
                $this->size = "Неизвестно";
            }
            $this->duration = "Неизвестно";
            $this->bitrate = "Неизвестно";
            $this->longName = "Неизвестно";
 
            $errorMessage = $errorMessage."<li>Не удалось определить основные данные видеофайла</li>";
        }
        
        

        
//        $ffmpeg =  FFMpeg::create(array(
//                'ffmpeg.binaries' => '/home/prog/FFMPEG/ffmpeg/ffmpeg',
//               'ffprobe.binaries' => '/home/prog/FFMPEG/ffmpeg/ffprobe',
//                ));
//        $video = $ffmpeg->open(Yii::$app->params['uploadPath'] . "/" . Yii::$app->user->identity->id . "/" . $this->href.".".$this->ext);
//        $video->filters()->resize(new Coordinate\Dimension(640, 480))->synchronize();
        
       //$video->save(new Format\Video\X264('libmp3lame', 'libx264'), Yii::$app->params['uploadPath'] . "/" . Yii::$app->user->identity->id . "/" .'export-x264.mp4');
       //$video->save(new Format\Video\WebM(), Yii::$app->params['uploadPath'] . "/" . Yii::$app->user->identity->id . "/" .'export-webm.flv');   
        //$video->save(new Format\Video\WMV(), Yii::$app->params['uploadPath'] . "/" . Yii::$app->user->identity->id . "/" .'export-wmv.mp4');
        
        $this->href = $this->generateStr($this->fileName . "elfxf", 1); //удача
        date_default_timezone_set( 'Europe/Moscow' );
        $this->uploadDate = date("Y-m-d G:i:s");

        $videoInfo = "";
        $videoMeta = $ffprobe->streams(Yii::$app->params['uploadPath'] . "/" . $userIdMy . "/" . $this->href.".".$ext)->videos()->first();
        
        if ($videoMeta!=null) {
            $codec_name = $videoMeta->get('codec_long_name');
            $codec_tag_string = $videoMeta->get('codec_tag_string');
            $videoInfo = $videoInfo."<div class=\"metaInfo\">Кодек: </div>"."<div class=\"metaInfoData\">".$codec_name." (".$codec_tag_string.")</div><br/>";

            $codec_name = $videoMeta->get('codec_name');
            $videoInfo = $videoInfo."<div class=\"metaInfo\">Кодек (сокр.): </div>"."<div class=\"metaInfoData\">".$codec_name."</div><br/>";

            $profile = $videoMeta->get('profile');
            $videoInfo = $videoInfo."<div class=\"metaInfo\">Профиль : </div>"."<div class=\"metaInfoData\">".$profile."</div><br/>";

            $width = $videoMeta->get('width');
            $height = $videoMeta->get('height');
            $videoInfo = $videoInfo."<div class=\"metaInfo\">Разрешение: </div>"."<div class=\"metaInfoData\">".$width."х".$height."</div><br/>";

            $display_aspect_ratio = $videoMeta->get('display_aspect_ratio');
            $videoInfo = $videoInfo."<div class=\"metaInfo\">Соотношение сторон: </div>"."<div class=\"metaInfoData\">".$display_aspect_ratio."</div><br/>";

            $frame_rate = explode("/", $videoMeta->get('r_frame_rate'));
            $videoInfo = $videoInfo."<div class=\"metaInfo\">Количество кадров в секунду: </div>"."<div class=\"metaInfoData\">".$frame_rate[0]."</div><br/>";

            $duration = date("H:i:s", (int) $videoMeta->get('duration'));
            $videoInfo = $videoInfo."<div class=\"metaInfo\">Длительность: </div>"."<div class=\"metaInfoData\">".$duration."</div><br/>";      

            $bit_rate = $this->filesize_get_bit((int) $videoMeta->get('bit_rate')) . "/сек";
            $videoInfo = $videoInfo."<div class=\"metaInfo\">Битрейт: </div>"."<div class=\"metaInfoData\">".$bit_rate."</div><br/>";

            $this->videoInfo = $videoInfo;
        } else {
            $this->videoInfo = "Неизвестно";
            $errorMessage = $errorMessage."<li>Не удалось определить данные по видеопотоку</li>";
        }
   
        
        $audioInfo = "";
        $audioMeta = $ffprobe->streams(Yii::$app->params['uploadPath'] . "/" . $userIdMy . "/" . $this->href.".".$ext)->audios()->first();    
        if ($audioMeta!=null) {
            $codec_name = $audioMeta->get('codec_long_name');

            $codec_tag_string = $audioMeta->get('codec_tag_string');
            $audioInfo = $audioInfo."<div class=\"metaInfo\">Кодек: </div>"."<div class=\"metaInfoData\">".$codec_name." (".$codec_tag_string.")</div><br/>";  

            $codec_name = $audioMeta->get('codec_name');
            $audioInfo = $audioInfo."<div class=\"metaInfo\">Кодек (сокр.): </div>"."<div class=\"metaInfoData\">".$codec_name."</div><br/>";  

            $profile = $audioMeta->get('profile');
            $audioInfo = $audioInfo."<div class=\"metaInfo\">Профиль: </div>"."<div class=\"metaInfoData\">".$profile."</div><br/>"; 

            $channels = $audioMeta->get('channels');
            $audioInfo = $audioInfo."<div class=\"metaInfo\">Каналы: </div>"."<div class=\"metaInfoData\">".$channels."</div><br/>"; 

            $channel_layout = $audioMeta->get('channel_layout');
            $audioInfo = $audioInfo."<div class=\"metaInfo\">Канал: </div>"."<div class=\"metaInfoData\">".$channel_layout."</div><br/>";        

            $duration = date("H:i:s", (int) $audioMeta->get('duration'));
            $audioInfo = $audioInfo."<div class=\"metaInfo\">Длительность: </div>"."<div class=\"metaInfoData\">".$duration."</div><br/>";       

            $bit_rate = $this->filesize_get_bit((int) $audioMeta->get('bit_rate')) . "/сек";
            $audioInfo = $audioInfo."<div class=\"metaInfo\">Битрейт: </div>"."<div class=\"metaInfoData\">".$bit_rate."</div><br/>";         

            $this->audioInfo = $audioInfo;
            } else {
            $this->audioInfo = "Неизвестно";
            $errorMessage = $errorMessage."<li>Не удалось определить данные по аудиопотоку</li>";
        }
        } 
        if ($errorMessage != "") {
            //$err = Json::encode(['error'=>"Файл загружен и сохранен, но при его записи возникли следующие ошибки:<br/><ul>".$errorMessage."</ul>Возможно, следует перезагрузить этот файл, следуя ".Html::a('рекомендациям', Url::to(["helpers/view",'id'=>'upload','#'=>'rekommend'],true),['target'=>'_blank'])."<br/>Чтобы увидеть загруженный файл в списке файлов выше, ".Html::a('обновите', Url::to(["site/files"],true))." страницу"]);
        }
        
        parent::save($runValidation);
        
        if ($errorMessage != "") {
            return $errorMessage;
        } else {
            return false;
        }
    }

    public function generateStr($value, $key) {
        $keyHash = md5(Yii::$app->params["key"] . $key, true);
        if ($key == 1) {
            return str_replace("/", "_", base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $keyHash, $value, MCRYPT_MODE_ECB)));
        } else {
            return urlencode(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $keyHash, $value, MCRYPT_MODE_ECB)));
        }
        //return str_replace(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $keyHash, $value, MCRYPT_MODE_ECB)));
    }

    public function getProfilePictureFile() {
        if (strpos($this->profile_pic,".")!=false) {
            $ext = explode(".",$this->profile_pic)[1];
        } else
        {
            $ext = "";
        }
        $get = Yii::$app->request->get();
        if (array_key_exists('userId',$get)) {
            return isset($this->profile_pic) ? Yii::$app->params['uploadPath'] . "/" . $get['userId'] . "/" .  $this->generateStr($this->str2url($this->profile_pic) . "elfxf", 1).".".$ext : null;
        } else {
            return isset($this->profile_pic) ? Yii::$app->params['uploadPath'] . "/" . Yii::$app->user->identity->id . "/" .  $this->generateStr($this->str2url($this->profile_pic) . "elfxf", 1).".".$ext : null;
        }
    }

    public function getProfilePictureUrl($subFolder = "") {
        // return a default image placeholder if your source profile_pic is not found
        $profile_pic = isset($this->profile_pic) ? $this->profile_pic : 'default_user.jpg';
        return Yii::$app->params['uploadUrl'] . '/' . $subFolder . $profile_pic;
    }

    /**
     * Process upload of profile picture
     *
     * @return mixed the uploaded profile picture instance
     */
    public function uploadProfilePicture() {
        // get the uploaded file instance. for multiple file uploads
        // the following data will return an array (you may need to use
        // getInstances method)
        $image = UploadedFile::getInstance($this, 'profile_pic');

        // if no image was uploaded abort the upload
        if (empty($image)) {
            return false;
        }

        // store the source file name
        //$this->filename = $image->name;
        $ext = end((explode(".", $image->name)));

        // generate a unique file name
        //$this->profile_pic = $this->generateStr($image->name.(string)$image->size."elfxf",1).".{$ext}";//удача
        $this->profile_pic = $image->name;
        // the uploaded profile picture instance
        return $image;
    }

    //получение видеофайла по ссылке href. Обязательный параметр $userId - потому что могут быть одинаковые имена файлов=>одинаковые href в пределах базы
    public function getVideoByHref($href,$userId) {
        if ($href !== "") {
            return self::find()->where(['href' => $href,'userId'=>$userId])->one();
        } else{
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
    
    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'userId']);
    }

}
