<?php
/**
 * Created by PhpStorm.
 * User: dingduming
 * Date: 2018\7\18 0018
 * Time: 14:54
 */


/**
 * Login page accepts username and posts results to login_password_page.php,
 * which may take the users credential or redirect to a plugin specific page.
 *
 * This page also offers features like anonymous login and signup.
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses database_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'database_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'out_user_api.php' );
require_api( 'utility_api.php' );
require_css( 'login.css' );
//require_css( 'login_out_page.css' );
//require_css( 'bootstrap-3.3.6.css' );

$f_error                 = gpc_get_bool( 'error' );
$f_cookie_error          = gpc_get_bool( 'cookie_error' );
$f_return                = 'view_all_bug_page.php';
$f_username              = gpc_get_string( 'username', '' );
$f_secure_session        = gpc_get_bool( 'secure_session', false );
$f_secure_session_cookie = gpc_get_cookie( config_get_global( 'cookie_prefix' ) . '_secure_session', null );


# 客户的自动登录
//if(out_auth_attempt_login($_COOKIE['MANTIS_COOKIE_USERNAME'], $_COOKIE['MANTIS_COOKIE_PASSWORD'], false)) {
//    gpc_set_cookie( config_get_global( 'cookie_prefix' ) . '_secure_session', '1' );
//    session_set( 'secure_session', 1 );
//    $_SESSION['is_out_user'] = 1;
//    global $g_cache_cookie_valid;
//    $g_cache_cookie_valid = true;
//    $id = out_user_get_id_by_name($_COOKIE['MANTIS_COOKIE_USERNAME']);
//    $cookie_string = out_user_get_field($id, 'cookie_string');
//    out_auth_get_current_user_cookie($_COOKIE['MANTIS_COOKIE_USERNAME']);
////    setcookie('MANTIS_STRING_COOKIE', $cookie_string);
//    $t_cookie_name = config_get_global( 'string_cookie' );
//    $t_cookie = gpc_get_cookie( $t_cookie_name, '' );
//    gpc_set_cookie($t_cookie_name, $t_cookie);
////    print_header_redirect( 'out_view_all_bug_page.php');
//}

# If user is already authenticated and not anonymous
# 自动登录（当前不是客户的情况下）
if(isset($_SESSION['is_out_user'])){
    if( auth_is_user_authenticated() && !current_user_is_anonymous()) {

        # If return URL is specified redirect to it; otherwise use default page
        if( !is_blank( $f_return ) ) {
            print_header_redirect( $f_return, false, false, true );
        } else {
            print_header_redirect( config_get_global( 'default_home_page' ) );
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>客户登录</title>
    <link rel="stylesheet" href="css/login_out_page.css">
    <link rel="stylesheet" href="css/bootstrap-3.3.6.css">
    <style>
        body{
            position: absolute;
            height: 100%;
            width: 100%;
            overflow: hidden;
        }

    </style>
</head>

<body>
<div style="width: 100%;max-height: 100%;height: 100%">
    <div class="w100 " style="height: 10%;padding-left: 10%;padding-top: 10px;margin-bottom: 10px">
        <img style="padding-top: 1rem" class="img-responsive" src="images/login_out_page/logo@2x.png" alt="">
    </div>
    <div class="w100" style="height: 50%;background: #E8F2FC url('images/login_out_page/img@2x.png') no-repeat;background-position: 15%;">
        <div class="container">
            <div class="row" style="margin-top: 3rem">
                <div class="col-xs-offset-6 col-xs-5 col-md-6  text-center" style="background: rgba(255,255,255,.7)">
                    <div  style="color: #5EB0FA;font-size: 2rem;padding-bottom: 1rem;padding-top: 0.5rem">汉全科技工单系统</div>


                    <form action="<?php echo AUTH_PAGE_OUT_USER ?>" method="post" role="form" id="form1" onsubmit="return formcheck();" class="we7-form">
                        <div class="input-group-vertical">
                            <input name="username"  style="width: 60%;margin:0 auto" type="text" class="form-control " placeholder="请输入用户名登录">
                            <input name="password" style="width: 60%;margin:1rem auto" type="password" class="form-control password" placeholder="请输入登录密码">
                            <div style="width: 60%;margin: 0 auto;text-align: left;color: #FF5555">
                                <?php
                                if( $f_error || $f_cookie_error ) {
                                    # Only echo error message if error variable is set
                                    if( $f_error ) {
                                        echo lang_get( 'login_error' );
                                    }
                                    if( $f_cookie_error ) {
                                        echo lang_get( 'login_cookies_disabled' );
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <div style="width: 60%;margin: 0 auto;text-align: left">
<!--                            <input name="remember" type="checkbox" value="1" > 记住密码-->
                            <input id="remember-login" type="checkbox" name="perm_login" class="ace"> 记住密码
                        </div>
                        <div class="login-submit text-center" style="margin-bottom: 2rem">
                            <input style="margin:1rem auto;width: 60%" type="submit" id="submit" name="submit" value="登录" class="btn btn-primary btn-block">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="w100 text-center" style="vertical-align: baseline;font-size: 2rem;bottom: 0;height: 40%;background: #5EB0FA;color: #fff;">
        <div style="height: 5rem;position: absolute;bottom: 0;width: 100%;margin: 0 auto;margin-bottom: 2rem">
            <p>www.A6shop.com</p>
            <p>汉全科技 成为同龄人的骄傲</p>
        </div>
    </div>
</div>
</body>
