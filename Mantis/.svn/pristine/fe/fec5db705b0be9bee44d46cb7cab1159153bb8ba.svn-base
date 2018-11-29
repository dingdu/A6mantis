<?php
/**
 * Notice: AJAX跨域判断是否是客户登陆信息是否正确
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\7\27 0027
 * Time: 10:59
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api('out_user_api.php');

# 解决跨域问题
//$origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : '';
//
//$allow_origin = array(
//    'http://task.a6shop.net',
//    'http://zxtrunk.hqserver.com',
//    'http://zxtrunk.hqserver.com:8092'
//);
//
//if(in_array($origin, $allow_origin)){
//    header('Access-Control-Allow-Origin:'.$origin);
//}

// 允许所有
header("Access-Control-Allow-Origin: *");

// 判断是否加密过
if(!(isset($_GET['encryption']) && $_GET['encryption'] == 'md5')) {
    $_POST['password'] = md5($_POST['password']);
}

if(isset($_POST['username']) && isset($_POST['password'])) {
    # 找不到用户
    $id = out_user_get_id_by_name($_POST['username']);
    $password = out_user_get_field($id, 'password');
    if(empty($id)) {
        echo 2;
    } else if($_POST['password'] != $password) {
        echo 3;
    } else {
        echo 1;
    }
} else {
    echo 2;
}
