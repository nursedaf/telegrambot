<?php
include 'db.php';

$token = "TOKEN ADDRESS HERE";

/* $bildirim = [
    'text' => 'otobüs geliyo',
    'chat_id' => '1045208840'
]; 
$a = file_get_contents("https://api.telegram.org/bot".$token."/sendMessage?".http_build_query($bildirim));
*/



$alarmlar = $db->query("SELECT * FROM durak WHERE zaman != '00:00:00'", PDO::FETCH_ASSOC);
if ($alarmlar->rowCount()) {
    foreach ($alarmlar as $alarm) {
        
        if (date_format(date_create($alarm["zaman"]), "H:i") == date("H:i")) {
            $durak = $cek['durak'];
            

            $url = "https://ulasim.denizli.bel.tr/SureHesap.ashx?id={$alarm['durak']}&t=akilliDurakListe&beklenenDurak=" . $alarm['durak'];
            $xml = simplexml_load_file($url);

            foreach ($xml as $key => $value) {
                $otobusno = $value->hatno;
                $kalansure = $value->sure;
                if ($otobusno == $alarm["otobus"]) {
                    $mesaj = "$otobusno numaralı otobüsün durağa gelmesine $kalansure dakika var";
                }
            }
            $bildirim = [
                'text' => $mesaj,
                'chat_id' => $alarm["chat_id"]
            ];
            $a = file_get_contents("https://api.telegram.org/bot" . $token . "/sendMessage?" . http_build_query($bildirim));
        }
    }
}



/*
$sor = $db->prepare("SELECT * FROM durak WHERE chat_id='1045208840' AND kayit='" . 'ev' . "'");
$sor->execute();
$cek = $sor->fetch(PDO::FETCH_ASSOC);
if ($sor->rowCount()) {
	$zaman = $cek['zaman'];
	$durak = $cek['durak'];
	$otobus = $cek['otobus'];

    $url = "https://ulasim.denizli.bel.tr/SureHesap.ashx?id={$durak}&t=akilliDurakListe&beklenenDurak=" . $durak;
    $xml = simplexml_load_file($url);

    foreach ($xml as $key => $value) {
        $otobusno = $value->hatno;
        $kalansure = $value->sure;

        if ($otobusno == $otobus) {

            $mesaj = "$otobusno numaralı otobüsün durağa gelmesine $kalansure dakika var";
        }
    }
    $bildirim = [
        'text' => $mesaj,
        'chat_id' => '1045208840'
    ];
    $a = file_get_contents("https://api.telegram.org/bot".$token."/sendMessage?".http_build_query($bildirim));
    

*/



/* $url = "https://ulasim.denizli.bel.tr/SureHesap.ashx?id={$durak}&t=akilliDurakListe&beklenenDurak=" . $durak;
$xml = simplexml_load_file($url);

foreach ($xml->Otobus[0]->attributes() as $k){
	if($k== $zaman){
        $a = file_get_contents("https://api.telegram.org/bot".$token."/sendMessage?".http_build_query($bildirim));
	}
}*/



//   }  



/*
/usr/local/bin/php -q /home/nursedaf/public_html/kontrol.php
*/
