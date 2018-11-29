<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Check login then redirect to main_page.php or to login_page.php
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
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses session_api.php
 * @uses string_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );
require_api( 'session_api.php' );
require_api( 'string_api.php' );

# 解决跨域问题
$origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : '';

$allow_origin = array(
    'http://task.a6shop.net',
    'http://zxtrunk.hqserver.com',
    'http://zxtrunk.hqserver.com:8092'
);

if(in_array($origin, $allow_origin)){
    header('Access-Control-Allow-Origin:'.$origin);
}

$f_username		= gpc_get_string( 'username', '' );
$f_password		= gpc_get_string( 'password', '' );
$t_return		= string_url( string_sanitize_url( gpc_get_string( 'return', config_get_global( 'default_home_page' ) ) ) );
$f_from			= gpc_get_string( 'from', '' );
$f_secure_session = gpc_get_bool( 'secure_session', false );
$f_reauthenticate = gpc_get_bool( 'reauthenticate', false );
$f_install = gpc_get_bool( 'install' );

# If upgrade required, always redirect to install page.
if( $f_install ) {
	$t_return = 'admin/install.php';
}

$f_username = auth_prepare_username( $f_username );
$f_password = auth_prepare_password( $f_password );

$t_user_id = auth_get_user_id_from_login_name( $f_username );
$t_allow_perm_login = auth_allow_perm_login( $t_user_id, $f_username );
$f_perm_login	= $t_allow_perm_login && gpc_get_bool( 'perm_login' );

gpc_set_cookie( config_get_global( 'cookie_prefix' ) . '_secure_session', $f_secure_session ? '1' : '0' );

# 判断是否以及md5加密过了（ajax动态获取时需要分别是否加密过）
if(!(isset($_GET['encryption']) && $_GET['encryption'] == 'md5')){
    $f_password = md5($f_password);
}

//var_dump($_GET,$_POST);

if( out_auth_attempt_login( $f_username, $f_password, $f_perm_login ) ) {
    # 先清除数据
    # clear cached userid
    user_clear_cache( $g_cache_current_user_id );
    current_user_set( null );
    $g_cache_cookie_valid = null;

    # clear cookies, if they were set
    if( auth_clear_cookies() ) {
        helper_clear_pref_cookies();
    }

    # 信息正确并且点击记住密码
//    if(isset($_POST['remember']) && !empty($_POST['remember'])) {
//        # 设置cookie保存 账号和密码 一周
//        setcookie('MANTIS_COOKIE_USERNAME', $f_username, time() + 24*3600*7);
//        setcookie('MANTIS_COOKIE_PASSWORD', md5($f_password), time() + 24*3600*7);
//    }



    global $g_cache_cookie_valid;
    $g_cache_cookie_valid = true;
	session_set( 'secure_session', $f_secure_session );

    if( $f_username == 'administrator' && $f_password == 'root' && ( is_blank( $t_return ) || $t_return == 'index.php' ) ) {
		$t_return = 'account_page.php';
	}
	$_SESSION['is_out_user'] = 1;
    $cookievalue = out_auth_get_current_user_cookie($f_username, true, $f_perm_login);

//    gpc_set_cookie('');
    // 需要设置cookie和 authentication中的
//    var_dump($cookievalue);die;
    $t_redirect_url = 'view_all_bug_page.php?' . $t_return;
//    $t_redirect_url = 'login_cookie_test.php?return=' . $t_return;
} else {
    /**
     * query_text：
     *      error   ：   是否有错误信息:1
     *      username：   用户尝试登陆的账号名
     *      return  ：   返回的用于显示的错误信息
     */
	$t_query_args = array(
		'error' => 1,
		'username' => $f_username,
		'return' => $t_return,
	);

	if( $f_reauthenticate ) {
		$t_query_args['reauthenticate'] = 1;
	}

	if( $f_secure_session ) {
		$t_query_args['secure_session'] = 1;
	}

	if( $t_allow_perm_login && $f_perm_login ) {
		$t_query_args['perm_login'] = 1;
	}

	$t_query_text = http_build_query( $t_query_args, '', '&' );

//	$t_redirect_url = auth_login_page( $t_query_text );

    // 这里直接定死错误跳转页面
    $t_redirect_url = 'feedback.php?'. $t_query_text;
        if( HTTP_AUTH == config_get_global( 'login_method' ) ) {
            auth_http_prompt();
            exit;
	}
}

if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){
    // ajax 请求的处理方式
}else{
    // 正常请求的处理方式
    print_header_redirect( $t_redirect_url );

};
