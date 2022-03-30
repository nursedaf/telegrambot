<?php
date_default_timezone_set('Europe/Istanbul');
try {

    $db = new PDO("mysql:host=localhost;dbname=DATABASE_NAME;charset=utf8", 'USERNAME', 'PASSWORD');
    // "Veritabanı bağlantısı başarılı";
} catch (PDOException $e) {
    echo $e->getMessage();
}

class TelegramBot
{
    const API_URL = 'https://api.telegram.org/bot';
    public $token;
    public $chatId;

    public function setToken($token)
    {
        $this->token = $token;
    }
    public function getData()
    {
        $data = json_decode(file_get_contents('php://input'));
        $this->chatId = $data->message->chat->id;
        return $data->message;
    }
    public function sendMessage($message)
    {
        return $this->request('sendMessage', [
            'chat_id' => $this->chatId,
            'text' => $message
        ]);
    }
    public function request($method, $posts)
    {
        $ch = curl_init();

        $url = self::API_URL . $this->token . '/' . $method;

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($posts));

        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }
    public function setWebhook($url)
    {
        return $this->request('setWebhook', [
            'url' => $url
        ]);
    }
}
$telegram = new TelegramBot();
$telegram->setToken('TOKEN ADDRESS HERE');
//echo $telegram->setWebhook('https://nursedaf.requestcatcher.com/');
$telegram->setWebhook('https://WEBHOOK PAGE ADDRESS');

$data = $telegram->getData();

if ($data->text == '/start') {
    $telegram->sendMessage(' Merhaba bu bot ile neler yapabileceğine göz at!

Otobüsün durağa gelmesine kaç dakika kaldığını görmek için:
Otobüs ve Durak numarasını boşluk bırakarak gir-> 250 775
-----------------------------------------------------------------------
Duraktan geçen otobüsleri  görmek için:
Durak numarasını gir-> durak 775
-----------------------------------------------------------------------
Otobüs güzergahını görmek için:
otobüs numarası-> 250
-----------------------------------------------------------------------
Her sabah evden çıkmadan otobüsünün gelmesine kaç dakika kaldığını sana mesaj atabilir.  
Daha fazlası için yardım yazman yeterli :)

İyi yolculuklar!');
}
if ($data->text == 'tşk') {
    $telegram->sendMessage('iyi yolculuklar! ');
}
if (stristr($data->text, '/otobus')) {
    $telegram->sendMessage('Otobüs ve Durak numarasını boşluk bırakarak gir->
Örnek:250 775');
}
if ($data->text == '/kaydet') {
    $telegram->sendMessage("Sık kullandığın durak ve otobüsü kaydetmek için:
    kaydet otobüsno durakno kayıtadı olarak gir-> kaydet 250 775 ev");
}
if ($data->text == '/alarm') {
    $telegram->sendMessage("Favorine eklediğin durağa otobüsünün gelmesine kaç dakika kaldığını her gün bildirim olarak almak için:
    alarm kayıtadı zaman-> alarm ev 09:00
Oluşturduğun bildirimi silmek için:
    alarm kayıtadı sil -> alarm ev sil");
}
if (stristr($data->text, '/yardim') || stristr($data->text, 'yardım') ) {
    $telegram->sendMessage('
Otobüsün durağa gelmesine kaç dakika kaldığını görmek için:
Otobüs ve Durak numarasını boşluk bırakarak gir-> 250 775
-----------------------------------------------------------------------
Duraktan geçen otobüsleri  görmek için:
Durak numarasını gir-> durak 775
-----------------------------------------------------------------------
Otobüs güzergahını görmek için:
otobüs numarası-> 250
-----------------------------------------------------------------------
Sık kullandığın durak ve otobüsü kaydetmek için:
kaydet otobüsno durakno kayıtadı olarak gir-> kaydet 250 775 ev
-----------------------------------------------------------------------
Favori durağına otobüsünün gelmesine kaç dakika kaldığını görmek için:
kayıt şeklini girmen yeterli-> ev
-----------------------------------------------------------------------
Kayıtlı bir favorini silmek için: 
favori sil kayıtadı->sil ev 
-----------------------------------------------------------------------
Favori adreslerini görmek için-> favori
-----------------------------------------------------------------------
Favorine eklediğin durağa otobüsünün gelmesine kaç dakika kaldığını her gün bildirim olarak almak için:
alarm kayıtadı zaman-> alarm ev 09:00
-----------------------------------------------------------------------
Oluşturduğun bildirimi silmek için:
alarm kayıtadı sil -> alarm ev sil
-----------------------------------------------------------------------
Favori durağını silmek için:
favori sil kayıtadı -> sil ev
');
}

