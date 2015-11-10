<?php
use yii\helpers\Html;

$this->title="Создание объявления";
$key = md5("key", true);
$input2 = "vorobyev.it@gmail.com";
echo "key: ".$key."<br/>";

/* encode */
$encrypted_data = urlencode(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $input2, MCRYPT_MODE_ECB)));
echo $encrypted_data."<br/>";
/* decode */
$decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_256,$key, base64_decode(urldecode($encrypted_data)),MCRYPT_MODE_ECB);
echo $decrypted."<br/>";
?>
<div class="site-contact">
    <h1><?= Html::encode($this->title) ?></h1>
    
</div>
