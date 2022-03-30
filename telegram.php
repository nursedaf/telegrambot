<?php
include 'db.php';

//duraktan geçen tüm otobüsleri göster
if (stristr($data->text, 'durak') !== false) {
    $id = $data->text;
    $arr = explode(" ", $id);
    $durak = $arr[1];
    $url = "https://ulasim.denizli.bel.tr/SureHesap.ashx?id={$durak}&t=akilliDurakListe&beklenenDurak=" . $durak;
    $xml = simplexml_load_file($url);
    if ($durak = $arr[1]) {

        foreach ($xml as $key => $value) {
            if (!empty($xml->Otobus[0])) {
                $otobusno = $value->hatno;
                $kalansure = $value->sure;

                $telegram->sendMessage("$otobusno numaralı otobüsün durağa gelmesine $kalansure dakika var. ");
            } else {
                $telegram->sendMessage("Böyle bir durak bulunamadı.");
            }
        }
    } else {
        $telegram->sendMessage("Duraktan geçen tüm otobüsleri görmek için->
durak duraknumarası
Örnek: durak 775");
    }
}

//otobüsün güzergahını göster
else if (is_numeric($data->text)) {
    $no = $data->text;
    $json = json_decode(file_get_contents('https://ulasim.denizli.bel.tr/jsonotobusduraklar.ashx'));
    $otobus = $json->otobus;
    foreach ($otobus as $key) {
        $hat = $key->HatNo;
        $duraklar =  $key->onemli_duraklar;
        if ($hat == $no) {
            $telegram->sendMessage("$hat numaranın Güzergahı:
$duraklar");
        }
    }

//boşluk içeren komutlar:
} else if (stristr($data->text, ' ') !== false) {
    $id = $data->text;
    $arr = explode(" ", $id);


    if ($arr[0] == 'kaydet') {  //favori otobüs durak kaydetme

        $id = $data->text;
        $arr = explode(" ", $id);
        $otobusno = $arr[1];
        $durakno = $arr[2];
        $kayit = $arr[3];

        if (is_numeric($otobusno) && is_numeric($durakno) && $otobusno != "" && $durakno != "") {
            $kaydet = $db->prepare("INSERT INTO durak SET 
                    chat_id = :a,
                    otobus = :b,
                    durak = :c,
                    kayit = :d
                ");

            $insert = $kaydet->execute(array(
                'a' => $data->chat->id,
                'b' => $otobusno,
                'c' => $durakno,
                'd' => $kayit
            ));
        }
        if ($insert) {
            $telegram->sendMessage("Otobüs numarası: $otobusno Durak numarası: $durakno Kayıt Şekli: $kayit");
        }
    } elseif ($arr[0] == 'sil') {     //favori silme
        $id = $data->text;
        $arr = explode(" ", $id);
        $chat = $data->chat->id;
        $sil = $db->prepare("DELETE FROM durak WHERE chat_id=" . $chat . " AND kayit='" . $arr[1] . "'");
        $delete = $sil->execute(array(
            'chat' => $chat_id
        ));
        if ($delete) {
            $telegram->sendMessage("Favori durağın silindi.");
        } 
    } elseif ($arr[0] == 'otobus') { //otobüs güzergahı
        $no = $arr[1];
        $json = json_decode(file_get_contents('https://ulasim.denizli.bel.tr/jsonotobusduraklar.ashx'));
        $otobus = $json->otobus;
        foreach ($otobus as $key) {
            $hat = $key->HatNo;
            $duraklar =  $key->onemli_duraklar;
            if ($hat == $no) {
                $telegram->sendMessage("$hat numaranın Güzergahı:
$duraklar");
            }
        }
    } elseif ($arr[0] == 'alarm') { //alarm oluşturma
        $id = $data->text;
        $arr = explode(" ", $id);
        $kayit = $arr[1];
        $zaman = $arr[2];

        if ($kayit != "" && $zaman != "") {
            $kaydet = $db->prepare("UPDATE durak SET zaman = :zaman WHERE chat_id=" . $data->chat->id . " AND kayit='" . $arr[1] . "'");
            $insert = $kaydet->execute(array(
                'zaman' => $zaman,
            ));
            if ($insert) {
                if ($arr[2] == 'sil') {  
                    $telegram->sendMessage("Alarm silindi. Bildirim gelemeyecek.");
                } elseif($arr[1] != 'sil') {
                    $telegram->sendMessage("Alarm saati $zaman olarak ayarlandı.");
                }
                
            } else {
                $telegram->sendMessage("Alarm oluşturulamadı");
            }
        }


    } else { //sadece otobüs ve durak numarası girildiğinde anlık otobüs bilgisi ver
        $otobus = $arr[0];
        $durak = $arr[1];
        durak($durak, $otobus, $telegram);
    }
}

//favoriler 
elseif (stristr($data->text, 'favori') !== false && stristr($data->text, 'sil') !== true) {
    //favorileri listeleme
    $sor = $db->prepare("SELECT * FROM durak WHERE chat_id='" . $data->chat->id . "'");
    $sor->execute();
    while ($cek = $sor->fetch(PDO::FETCH_ASSOC)) {
        $telegram->sendMessage($cek['kayit']);
    }
}

//kayıt adı yazıldığında durağa kaç dk bilgisi
else {
    $sor = $db->prepare("SELECT * FROM durak WHERE chat_id='" . $data->chat->id . "' AND kayit='" . $data->text . "'");
    $sor->execute();
    $cek = $sor->fetch(PDO::FETCH_ASSOC);
    if ($sor->rowCount()) {
        $otobus = $cek['otobus'];
        $durak = $cek['durak'];
        durak($durak, $otobus, $telegram);
    } elseif (!stristr($data->text, 'kaydet') && !stristr($data->text, 'otobus')  && !stristr($data->text, 'alarm') && !stristr($data->text, 'start') && !stristr($data->text, 'yardim') && !stristr($data->text, 'yardım')  && !stristr($data->text, 'tşk')){
        $telegram->sendMessage("Böyle bir adres kaydetmedin. Kaydetmek için-> kaydet otobüsno durakno kayıtismi şeklinde gir. 
Tüm komutları görmek için yardim yaz.");
    }
}

function durak($b, $c, $sinif)
{
    $url = "https://ulasim.denizli.bel.tr/SureHesap.ashx?id={$b}&t=akilliDurakListe&beklenenDurak=" . $b;
    $xml = simplexml_load_file($url);

    if (empty($xml->Otobus[0])) {
        $sinif->sendMessage("Geçersiz durak girdin.");
        return;
    } else {

        $gecmio = false;

        foreach ($xml as $key => $value) {
            $otobusno = $value->hatno;
            $kalansure = $value->sure;

            if ($otobusno == $c) {

                $sinif->sendMessage("$otobusno numaralı otobüsün durağa gelmesine $kalansure dakika var. ");
                $gecmio = false;
                return;
            } else {
                $gecmio = true;
            }
        }
        if ($gecmio) {
            $sinif->sendMessage("$c numaralı otobüs bu duraktan geçmiyor.");
        }
    }
}
