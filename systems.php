<?php
//error_reporting(0);
/**
 * Created by PhpStorm.
 * User: NSC
 * Date: 8/14/2018
 * Time: 3:42 AM
 */
date_default_timezone_set("Africa/Lagos");

autoLoad();

//Auto LoadFunction
function autoLoad()
{
    global $IMAGE_PATH;
    /**Create a folder for keeping images
     * */
    if (!file_exists($IMAGE_PATH)) {
        mkdir($IMAGE_PATH);
    }
}

//Format phone number
function fNumber($phone){
    $phone_number = preg_replace('/^0/','234',$phone);
    return str_replace("+","",$phone_number);
}

//User TVN
function getUserTVN($name)
{
    return randLetters(2).rand(1111, 9999).rand(2222, 8888);
}

//RandomLetters
function randLetters($range_in_number){
    $CHAR = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $output = null;
    $n = (int) $range_in_number;
    if($n>26){
        $n = 26;
    }
    if($n<1){
        $n = 1;
    }
    for ($i = 0; $i<$n; $i++){
        $output.=$CHAR[rand(0,25)];
    }
    return $output;
}

?>