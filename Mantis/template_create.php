<?php
/**
 * Notice: 创建模版
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\8\4 0004
 * Time: 17:25
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );
require_api( 'bug_template_api.php' );

form_security_validate( 'template_create' );

$f_temp_category_id = gpc_get_string('temp_category_id');
$f_template_summary = gpc_get_string( 'summary' );
$f_template_description = gpc_get_string( 'description' );

$bt = new BugTemplate();
$bt->temp_category_id = $f_temp_category_id;
$bt->summary = $f_template_summary;
$bt->description = $f_template_description;

$bt->createBugTemplate();

form_security_purge( 'template_create' );
print_successful_redirect( 'manage_template_page.php' );
