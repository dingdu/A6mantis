<?php
/**
 * 删除模版
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'config_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'bug_template_api.php' );

access_ensure_global_level( config_get( 'tag_edit_threshold' ) );

$f_template_id = gpc_get_int( 'template_id' );
$t_template_row = getTemplateById($f_template_id);
// 判断id是否存在
if(empty($t_template_row)) {
    print_error_page('问题模版不存在');
}

delBugTemplate($f_template_id);

print_successful_redirect( 'manage_template_page.php' );
