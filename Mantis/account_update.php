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
 * This page updates a user's information
 * If an account is protected then changes are forbidden
 * The page gets redirected back to account_page.php
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
 * @uses email_api.php
 * @uses form_api.php
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
require_api( 'email_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'out_user_api.php' );
require_api( 'utility_api.php' );

form_security_validate( 'account_update' );

# If token is set, it's a password reset request from verify.php, and if
# not we need to reauthenticate the user
$t_verify_user_id = gpc_get( 'verify_user_id', false );
$t_account_verification = $t_verify_user_id ? token_get_value( TOKEN_ACCOUNT_VERIFY, $t_verify_user_id ) : false;
if( !$t_account_verification ) {
	auth_reauthenticate();
	$t_user_id = auth_get_current_user_id();
} else {
	# set a temporary cookie so the login information is passed between pages.
	auth_set_cookies( $t_verify_user_id, false );
	# fake login so the user can set their password
	auth_attempt_script_login( user_get_username( $t_verify_user_id ) );
	$t_user_id = $t_verify_user_id;
}

auth_ensure_user_authenticated();
current_user_ensure_unprotected();

$f_email           	= gpc_get_string( 'email', '' );
$f_realname        	= gpc_get_string( 'realname', '' );
$f_password_current = gpc_get_string( 'password_current', '' );
$f_password        	= gpc_get_string( 'password', '' );
$f_password_confirm	= gpc_get_string( 'password_confirm', '' );

$t_redirect_url = 'index.php';
if(isset($_SESSION['is_out_user'])) {
    $t_redirect_url = 'feedback.php';
}
$t_update_email = false;
$t_update_password = false;
$t_update_realname = false;

// 这里使用可变函数来动态判断是调用out_user_*的方法还是user_*的方法
$is_out = '';
if(isset($_SESSION['is_out_user'])) {
    $is_out = 'out_';
}

# Do not allow blank passwords in account verification/reset
if( $t_account_verification && is_blank( $f_password ) ) {
	# log out of the temporary login used by verification
	auth_clear_cookies();
	auth_logout();
	error_parameters( lang_get( 'password' ) );
	if(isset($_SESSION['is_out_user'])) {

	    print_error_page('密码不能为空');
    }
	trigger_error( ERROR_EMPTY_FIELD, ERROR );
}

$t_ldap = ( LDAP == config_get_global( 'login_method' ) );

# Update email (but only if LDAP isn't being used)
# Do not update email for a user verification
if( !( $t_ldap && config_get( 'use_ldap_email' ) )
	&& !$t_account_verification ) {
    $func = $is_out.'user_get_email';
	if( !is_blank( $f_email ) && $f_email != $func( $t_user_id ) ) {
		$t_update_email = true;
	}
}

# Update real name (but only if LDAP isn't being used)
if( !( $t_ldap && config_get( 'use_ldap_realname' ) ) ) {
	# strip extra spaces from real name
	$t_realname = string_normalize( $f_realname );
    $func = $is_out.'user_get_field';
    if( $t_realname != $func( $t_user_id, 'realname' ) ) {
		$t_update_realname = true;
	}
}

# Update password if the two match and are not empty
# 判断两次密码是否一致
if( !is_blank( $f_password ) ) {
	if( $f_password != $f_password_confirm ) {
		if( $t_account_verification ) {
			# log out of the temporary login used by verification
			auth_clear_cookies();
			auth_logout();
		}
        if(isset($_SESSION['is_out_user'])){
            print_error_page('新密码和确认密码不一致');
        }
		trigger_error( ERROR_USER_CREATE_PASSWORD_MISMATCH, ERROR );
	} else {
		if( !$t_account_verification && !auth_does_password_match( $t_user_id, $f_password_current ) ) {
            if(isset($_SESSION['is_out_user'])){
                print_error_page('当前密码错误');
            }
			trigger_error( ERROR_USER_CURRENT_PASSWORD_MISMATCH, ERROR );
		}

		if( !auth_does_password_match( $t_user_id, $f_password ) ) {
			$t_update_password = true;
		}
	}
}

layout_page_header( null, $t_redirect_url );

layout_page_begin();

$t_message = '';

if( $t_update_email ) {
    $func = $is_out.'user_set_email';
    $func( $t_user_id, $f_email );
	$t_message .= lang_get( 'email_updated' );
}

if( $t_update_password ) {
    $func = $is_out.'user_set_password';
    $func( $t_user_id, $f_password );
	$t_message = is_blank( $t_message ) ? '' : $t_message . '<br />';
	$t_message .= lang_get( 'password_updated' );

	# Clear the verification token
	if( $t_account_verification ) {
		token_delete( TOKEN_ACCOUNT_VERIFY, $t_user_id );
	}
    $t_cookie_name = config_get_global( 'string_cookie' );
    $t_cookie = gpc_get_cookie( $t_cookie_name, '' );
	// 清空cookie_string
    gpc_set_cookie($t_cookie_name, $t_cookie, time()-3600);
}

# 如果是客户的话不可修改真实姓名
if( !isset($_SESSION['is_out_user'])) {
    if( $t_update_realname && !empty($is_out)) {
        $func = $is_out.'user_set_realname';
        $func( $t_user_id, $t_realname );
        $t_message = is_blank( $t_message ) ? '' : $t_message . '<br />';
        $t_message .= lang_get( 'realname_updated' );
    }
}

form_security_purge( 'account_update' );
html_operation_successful( $t_redirect_url, $t_message );

layout_page_end();