<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\7\21 0021
 * Time: 15:06
 */


/**
 * Authentication API
 *
 * @package CoreAPI
 * @subpackage AuthenticationAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses crypto_api.php
 * @uses current_user_api.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses ldap_api.php
 * @uses print_api.php
 * @uses session_api.php
 * @uses string_api.php
 * @uses tokens_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_api( 'access_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'crypto_api.php' );
require_api( 'current_user_api.php' );
require_api( 'database_api.php' );
require_api( 'error_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'ldap_api.php' );
require_api( 'print_api.php' );
require_api( 'session_api.php' );
require_api( 'string_api.php' );
require_api( 'tokens_api.php' );
require_api( 'user_api.php' );
require_api( 'out_user_api.php' );
require_api( 'utility_api.php' );

# 浏览器保存的cookie
$g_script_login_out_cookie = null;

# 数据库查询到的cookie
$g_cache_anonymous_out_user_cookie_string = null;

# @global int $g_cache_current_user_id
$g_cache_current_out_user_id = null;

/**
 * Notes:获取客户的id
 * User: dingduming
 * Date: 2018\7\21 0021
 * Time: 15:08
 * @return int|null
 */
function auth_get_current_out_user_id() {


}
