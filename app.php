<?php
//Call for functions.....................

//Define Const
const TABLE_PROFILE = 'zeta_profile';

require("mysqli.class.php");

$config['db_host'] = "localhost";
$config['db_username'] = "root";
$config['db_password'] = "";
$config['db_name'] = "zetatrailapp";

$db = new Db();

$IMAGE_PATH = "trail_images";
const IMAGE_PATH = "trail_images";

require('systems.php');
require('sms/qsms.php');

if (empty($_POST['action'])) {
    echo "";
    exit(0);
}


//Define route part
$route = $_POST['action'];
switch ($route) {
    case 'register':
        register();
        break;
    case 'sign-in':
        login();
        break;
    case 'verify-acc':
        verified_acc();
        break;
    case 'fetch-acc':
        fetchAccount();
        break;
    case 'update-acc':
        updateAccount();
        break;
    case 'update-log':
        updateLog();
        break;
    default:
        return null;
}

//Update Log
function updateLog()
{
    $tvn = $_POST['tvn'];
    $data = $_POST['data'];
    global $db;
    if (!empty($tvn) && !empty($data)) {
        $upd = $db->update(TABLE_PROFILE, array("zeta_call_logs" => $data), array("zeta_tvn" => $tvn));
        if ($upd) {
            echo "1";
            return;
        }
    }
}

//Update Account
function updateAccount()
{
    global $db;
    $tvn = $_POST['tvn'];
    $pic = $_POST['pic'];
    $add = $_POST['address'];
    if (!empty($add)) {
        $db->update(TABLE_PROFILE, array("zeta_address" => $add), array("zeta_tvn" => $tvn));
    }
    if (!empty($pic)) {
        $old_name = $tvn . '_' . date('m-Y') . '_OLD';
        if (rename(IMAGE_PATH . '/' . $tvn, IMAGE_PATH . '/' . $old_name)) {
            file_put_contents(IMAGE_PATH . '/' . $tvn, base64_decode($pic));
        } else {
            return;
        }
    }
    echo "1";
}

//Fetch data
function fetchAccount()
{
    $TVN = $_POST['tvn'];
    if (empty($TVN)) {
        return;
    }
    global $db;
    $rd = $db->row("select * from " . TABLE_PROFILE . " where zeta_tvn='$TVN' and zeta_status='1'");
    if (!empty($rd->zeta_tvn)) {
        echo json_encode($rd);
    } else {
        echo "Error";
    }
}

//Verify account
function verified_acc()
{
    $PHONE = $_POST['phone'];
    $PHONE = fNumber($PHONE);
    if (is_numeric($PHONE)) {
        global $db;
        $upd = $db->update(TABLE_PROFILE, array("zeta_status" => "1"), array("zeta_phone" => $PHONE));
        if ($upd) {
            echo "1";
            return;
        }
        echo "0";
    }
}

//First login
function login()
{
    /**Sign In With One Time Verification
     * */
    $PHONE = $_POST['phone'];
    $PHONE = fNumber($PHONE);
    $code = rand(12134, 99999);
    file_put_contents("code.txt", $code);
    $textMessage = "Your TrailApp PIN is " . $code . " \nDo not share with anyone\n" . date("h:i:s a");
    if (is_numeric($PHONE)) {
        global $db;
        $rd = $db->row("select * from " . TABLE_PROFILE . " where `zeta_phone`='$PHONE'");
        if (!empty($rd->zeta_phone)) {
            if (sendMessage($PHONE, $textMessage)) {
                echo json_encode(array("status" => "1", "tvn" => $rd->zeta_tvn, "phone" => $PHONE, "code" => $code));
                return;
            } else {
                echo json_encode(array("status" => "3"));
            }
        } else {
            echo json_encode(array("status" => "2"));
        }
        return;
    }
    echo json_encode(array("status" => "0"));
}


//Start function calling below
function register()
{
    /**Sample Column
     * zeta_id   zeta_name   zeta_phone   zeta_address   zeta_tvn   zeta_status
     *
     * zeta_ref_one  zeta_ref_one_name   zeta_ref_two  zeta_ref_two_name
     *
     * */
    $NAME = $_POST['full-name'];
    $PHONE = $_POST['phone'];
    $ADDR = $_POST['address'];
    $tvn = getUserTVN(strtoupper($NAME));
    $DATE = date('d-m-Y h:m:i');
    $REF_1 = $_POST['ref-phone-1'];
    $REF_2 = $_POST['ref-phone-2'];
    $REF_1N = $_POST['ref-name-1'];
    $REF_2N = $_POST['ref-name-2'];
    //file_put_contents('trail_Images/' . $tvn . ".jpg", base64_decode($_POST['image']));

    $arr = array(
        'zeta_name' => strtoupper($NAME),
        'zeta_phone' => fNumber($PHONE),
        'zeta_address' => $ADDR,
        'zeta_tvn' => $tvn,
        'zeta_date' => $DATE,
        'zeta_ref_one' => fNumber($REF_1),
        'zeta_ref_one_name' => $REF_1N,
        'zeta_ref_two' => fNumber($REF_2),
        'zeta_ref_two_name' => $REF_2N
    );
    global $db;
    $resp = $db->insert(TABLE_PROFILE, $arr);
    if ($resp['status'] == "1") {
        file_put_contents(IMAGE_PATH . '/' . $tvn . "", base64_decode($_POST['image']));
        $push = array("status" => "1", "tvn" => $tvn);
        echo json_encode($push);
    }
    if ($resp['status'] == "2") {
        echo "2";
    }
}

//Update Call Logs
function updateCallLog($tvn)
{

}

?>