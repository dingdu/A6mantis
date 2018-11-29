<?php

/**
 * 模版修改
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );
require_api( 'tag_api.php' );
require_api( 'user_api.php' );
require_api( 'bug_template_api.php' );

form_security_validate( 'template_update' );

$f_template_id = gpc_get_int( 'template_id' );
$t_template_row = getTemplateById($f_template_id);

// 判断id是否存在
if(empty($t_template_row)) {
    print_error_page('问题模版不存在');
}

if( !( access_has_global_level( config_get( 'tag_edit_threshold' ) )
	|| ( auth_get_current_user_id() == $t_tag_row['user_id'] )
		&& access_has_global_level( config_get( 'tag_edit_own_threshold' ) ) ) ) {
	access_denied();
}

$bt = new BugTemplate();

$bt->id = $f_template_id;
$bt->temp_category_id = gpc_get_string( 'temp_category_id', $t_template_row['temp_category_id'] );
$bt->summary = gpc_get_string( 'summary', $t_template_row['summary'] );
$bt->description = gpc_get_string( 'description', $t_template_row['description'] );

$bt->udpateBugTemplate();

form_security_purge( 'template_update' );

$t_url = 'template_update_page.php?template_id='.$f_template_id;
print_successful_redirect( $t_url );
