<?php
/**
 * Created by PhpStorm.
 * User: NSC
 * Date: 8/14/2018
 * Time: 2:07 PM
 */
function sendMessage($to,$message){
    $message = urlencode($message);
    $sender= urlencode("TrailApp");
    $mobile = $to;
    $url = 'http://www.multitexter.com/tools/geturl/Sms.php?username=nusktecsoft@gmail.com&password=NUSKTECsoft&sender='.$sender.'&message='.$message .'&flash=0&recipients='. $mobile;
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $resp = curl_exec($ch);
    curl_close($ch);
    //echo $resp;
    if($resp=="100"){
        return true;
    }
    return true;
}