<?php

namespace app\models;
use Yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

class File Extends ActiveRecord {
    public $profile_pic;
    
    public static function tableName()
    {
        return "Files";
    }
    
    public function filesize_get($filesize) 
    { 
  
       // Если размер переданного в функцию файла больше 1кб 
        if($filesize > 1024) 
        { 
           $filesize = ($filesize/1024); 
           // если размер файла больше одного килобайта 
           // пересчитываем в мегабайтах 
           if($filesize > 1024) 
           { 
                $filesize = ($filesize/1024); 
               // если размер файла больше одного мегабайта 
               // пересчитываем в гигабайтах 
               if($filesize > 1024) 
               { 
                   $filesize = ($filesize/1024); 
                   $filesize = round($filesize, 1); 
                   return $filesize." GB";    

               } 
               else 
               { 
                   $filesize = round($filesize, 1); 
                   return $filesize." MB";    
               }   

           } 
           else 
           { 
               $filesize = round($filesize, 1); 
               return $filesize." KB";    
           } 

       } 
       else 
       { 
           $filesize = round($filesize, 1); 
           return $filesize." Bytes";    
       } 

    } 
    
    public function save($runValidation = false, $attributeNames = NULL, $name=NULL, $size=NULL)//тут ошибки
    { 
        if ($attributeNames!=="standart"){
        if (gettype($attributeNames)=="array"){
            $this->device =(isset($attributeNames["Model"])) ? $attributeNames["Model"]:"";
            $this->size =(isset($attributeNames["FileSize"])) ? $this->filesize_get((int)$attributeNames["FileSize"]):"";
            $this->time =(isset($attributeNames["DateTime"])) ? date("Y-m-d H:i:s", strtotime($attributeNames['DateTime'])):"";
            //$this->timeBegin = date ("Y-m-d H:i:s", filemtime($attributeNames["path"]));
            $this->timeBegin =(isset($attributeNames["FileDateTime"])) ? date ("Y-m-d H:i:s",$attributeNames["FileDateTime"]):"";
            
        } else {
            $this->device = "";
            $this->size = ""; 
            $this->time="";
            $this->timeBegin="";
        }
        $this->name=$name;
        $this->href=$this->generateStr($this->name.(string)$size."cxfcnmt",2);//счастье
        $this->access=0;
        $this->uploadTime=date("Y-m-d H:i:s");
        return parent::save($runValidation);
        } else {
           return parent::save($runValidation); 
        }
    }
   
    private function generateStr($value,$key) 
    {
        $keyHash = md5(Yii::$app->params["key".$key], true);
        if ($key==1) {
            return str_replace("/","_",base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $keyHash, $value, MCRYPT_MODE_ECB)));
        } else {
            return urlencode(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $keyHash, $value, MCRYPT_MODE_ECB)));
        }
        //return str_replace(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $keyHash, $value, MCRYPT_MODE_ECB)));
    }
    
    public function getProfilePictureFile()
    {
        return isset($this->profile_pic) ? Yii::$app->params['uploadPath'] ."/". $this->profile_pic : null;
    }

    public function getProfilePictureUrl($subFolder="")
    {
        // return a default image placeholder if your source profile_pic is not found
        $profile_pic = isset($this->profile_pic) ? $this->profile_pic : 'default_user.jpg';
        return Yii::$app->params['uploadUrl'] . '/'.$subFolder . $profile_pic;
    }

    /**
    * Process upload of profile picture
    *
    * @return mixed the uploaded profile picture instance
    */
    public function uploadProfilePicture() 
    {
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
        $this->profile_pic = $this->generateStr($image->name.(string)$image->size."elfxf",1).".{$ext}";//удача

        // the uploaded profile picture instance
        return $image;
    }    
    
    public function getImage($href) 
    {
        if ($href!==""){
            return self::find()->where(['href'=>$href])->limit(5)->all();
        } else {
            return self::find(); 
        }
    }
}
