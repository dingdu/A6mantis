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
 * User Page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses icon_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'icon_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'utility_api.php' );



# 如果是客户则不能访问该页面
if(isset($_SESSION['is_out_user'])) {
//    print_header_redirect( 'view_all_bug_page.php' );
    print_error_page(error_string( ERROR_ACCESS_DENIED ));
}


$t_cookie_name = config_get( 'manage_users_cookie' );
$t_lock_image = '<i class="fa fa-lock fa-lg" title="' . lang_get( 'protected' ) . '" />';

$f_save          = gpc_get_bool( 'save' );
$f_filter        = mb_strtoupper( gpc_get_string( 'filter', config_get( 'default_manage_user_prefix' ) ) );
$f_page_number   = gpc_get_int( 'page_number', 1 );

if( !$f_save && !is_blank( gpc_get_cookie( $t_cookie_name, '' ) ) ) {
	$t_manage_arr = explode( ':', gpc_get_cookie( $t_cookie_name ) );

	# Hide Inactive
	$f_hide_inactive = (bool)$t_manage_arr[0];

	# Sort field
	if ( isset( $t_manage_arr[1] ) ) {
		$f_sort = $t_manage_arr[1];
	} else {
		$f_sort = 'supply_id';
	}

	# Sort order
	if ( isset( $t_manage_arr[2] ) ) {
		$f_dir = $t_manage_arr[2];
	} else {
		$f_dir = 'DESC';
	}

	# Show Disabled
	if ( isset( $t_manage_arr[3] ) ) {
		$f_show_disabled = $t_manage_arr[3];
	}
} else {
	$f_sort          = gpc_get_string( 'sort', 'supply_id' );
	$f_dir           = gpc_get_string( 'dir', 'ASC' );
	$f_hide_inactive = gpc_get_bool( 'hideinactive' );
	$f_show_disabled = gpc_get_bool( 'showdisabled' );
}

# Clean up the form variables
if( !db_field_exists( $f_sort, db_get_table( 'supply_user_work' ) ) ) {
	$c_sort = 'supply_id';
} else {
	$c_sort = addslashes( $f_sort );
}

$c_dir = ( $f_dir == 'ASC' ) ? 'ASC' : 'DESC';

# OFF = show inactive users, anything else = hide them
$c_hide_inactive = ( $f_hide_inactive == OFF ) ? OFF : ON;
$t_hide_inactive_filter = '&amp;hideinactive=' . $c_hide_inactive;

# OFF = hide disabled users, anything else = show them
$c_show_disabled = ( $f_show_disabled == OFF ) ? OFF : ON;
$t_show_disabled_filter = '&amp;showdisabled=' . $c_show_disabled;

# set cookie values for hide inactive, sort by, dir and show disabled
if( $f_save ) {
	$t_manage_string = $c_hide_inactive.':'.$c_sort.':'.$c_dir.':'.$c_show_disabled;
	gpc_set_cookie( $t_cookie_name, $t_manage_string, true );
}

layout_page_header( lang_get( 'supply_link' ) );


layout_page_begin( __FILE__ );


# New Accounts Form BEGIN

$t_days_old = 7 * SECONDS_PER_DAY;


$t_query = 'SELECT COUNT(*) AS new_user_count FROM {user}
	WHERE ' . db_helper_compare_time( db_param(), '<=', 'date_created', $t_days_old );
$t_result = db_query( $t_query, array( db_now() ) );
$t_row = db_fetch_array( $t_result );
$t_new_user_count = $t_row['new_user_count'];



# Never Logged In Form BEGIN

//$t_query = 'SELECT COUNT(*) AS unused_user_count FROM {supply_user_work}
//	WHERE ( status = 1 )';
//$t_result = db_query( $t_query );
//$t_row = db_fetch_array( $t_result );
//$t_unused_user_count = $t_row['unused_user_count'];



# Manage Form BEGIN

$t_prefix_array = array();

$t_prefix_array['ALL'] = lang_get( 'show_all_users' );

for( $i = 'A'; $i != 'AA'; $i++ ) {
	$t_prefix_array[$i] = $i;
}

for( $i = 0; $i <= 9; $i++ ) {
	$t_prefix_array[(string)$i] = (string)$i;
}
$t_prefix_array['UNUSED'] = lang_get( 'users_unused' );
$t_prefix_array['NEW'] = lang_get( 'users_new' );

$f_user_id =  auth_get_current_user_id();

$t_where_params = array();
if( $f_filter === 'ALL' ) {
	$t_where = '(1 = 1) and user_id = '.$f_user_id;
} else if( $f_filter === 'UNUSED' ) {
	$t_where = '(login_count = 0) AND ( date_created = last_visit )';
} else if( $f_filter === 'NEW' ) {
	$t_where = db_helper_compare_time( db_param(), '<=', 'date_created', $t_days_old );
	$t_where_params[] = db_now();
} else {
	$t_where_params[] = $f_filter . '%';
	$t_where = db_helper_like( 'UPPER(username)' );
}

$p_per_page = 10;

$t_offset = ( ( $f_page_number - 1 ) * $p_per_page );

$t_total_user_count = 0;

# Get the user data in $c_sort order
$t_result = '';

$t_query = 'SELECT count(*) as user_count FROM {supply_user_work} WHERE ' . $t_where;
$t_result = db_query( $t_query, $t_where_params );
$t_row = db_fetch_array( $t_result );
$t_total_user_count = $t_row['user_count'];

$t_page_count = ceil( $t_total_user_count / $p_per_page );
if( $t_page_count < 1 ) {
	$t_page_count = 1;
}

# Make sure $p_page_number isn't past the last page.
if( $f_page_number > $t_page_count ) {
	$f_page_number = $t_page_count;
}

# Make sure $p_page_number isn't before the first page
if( $f_page_number < 1 ) {
	$f_page_number = 1;
}
$f_project_id = gpc_get_int( 'project_id', helper_get_current_project() );

if($f_project_id!=''){
	$t_where.= ' and project_id = '.$f_project_id;
}

$t_query = 'SELECT * FROM {supply_user_work} WHERE ' . $t_where . ' ORDER BY ' . $c_sort . ' ' . $c_dir;
$t_result = db_query( $t_query, $t_where_params, $p_per_page, $t_offset );

$t_users = array();
while( $t_row = db_fetch_array( $t_result ) ) {
	//task_bug_id  project_id
	$t_query2 = 'SELECT name FROM {project} WHERE  id = '.db_param().' limit 1';
	$t_result2 = db_query( $t_query2,[$t_row['project_id']]);
	$t_results2 = db_fetch_array( $t_result2 );

	$t_query3 = 'SELECT summary FROM {bug} WHERE  id = '.db_param().' limit 1';
	$t_result3 = db_query( $t_query3,[$t_row['task_bug_id']]);
	$t_results3 = db_fetch_array( $t_result3 );

	$t_handle = 'SELECT realname FROM {user} WHERE  id = '.db_param().' limit 1';
	$t_handle_s = db_query( $t_handle,[$t_row['user_id']]);
	$t_handle_s = db_fetch_array( $t_handle_s );

	$t_row['bug_name'] = $t_results3['summary'];
	$t_row['handle_name'] = $t_handle_s['realname'];
	$t_row['project_name'] = $t_results2['name'];
	$t_users[] = $t_row;
}
//var_dump($t_users);die;

$t_user_count = count( $t_users );
?>
<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
<h4 class="widget-title lighter">
	<i class="ace-icon fa fa-users"></i>
	<?php echo lang_get('supply_link') ?>
	<span class="badge"><?php echo $t_total_user_count ?></span>
</h4>
</div>

<div class="widget-body">


<div class="widget-main no-padding">
	<div class="table-responsive">
		<table class="table table-striped table-bordered table-condensed table-hover">
		<thead>
			<tr>
<?php
	# Print column headers with sort links
	$t_columns = array(
		'project_name', 'bug_name', 'work_hours','reality_work_hours','supply_time',
		  'status'
	);

	foreach( $t_columns as $t_col ) {
		echo "\t<th>";
		print_manage_user_sort_link( 'manage_user_page.php',
			lang_get( $t_col ),
			$t_col,
			$c_dir, $c_sort, $c_hide_inactive, $f_filter, $c_show_disabled );
		print_sort_icon( $c_dir, $c_sort, $t_col );
		echo "</th>\n";
	}
?>
			</tr>
		</thead>

		<tbody>
<?php
	$t_date_format = config_get( 'normal_date_format' );
	$t_access_level = array();
	for( $i=0; $i<$t_user_count; $i++ ) {
		# prefix user data with u_
		$t_user = $t_users[$i];

		extract( $t_user, EXTR_PREFIX_ALL, 'u' );

		$u_date_created  = date( $t_date_format, $u_date_created );
		$u_last_visit    = date( $t_date_format, $u_last_visit );

		if( !isset( $t_access_level[$u_access_level] ) ) {
			$t_access_level[$u_access_level] = get_enum_element( 'access_levels', $u_access_level );
		} ?>
			<tr>
				<td><?php echo $t_user['project_name'] ?>
				</td>
				<td><?php echo $t_user['bug_name'] ?></td>
				<td><?php echo  $t_user['work_hours'] ?></td>
				<td><?php echo  $t_user['reality_work_hours'] ?></td>
				<td><?php echo  date('Y-m-d',$t_user['add_time']) ?></td>
				<td><?php if($t_user['status']==1) { echo  '申请中';}else{ echo '已通过'; } ?></td>
			</tr>
<?php
	}  # end for
?>
		</tbody>
	</table>
</div>
</div>

<div class="widget-toolbox padding-8 clearfix">

	<div class="btn-toolbar pull-right">
		<?php

		# @todo hack - pass in the hide inactive filter via cheating the actual filter value
		print_page_links( 'supply_page.php', 1, $t_page_count, (int)$f_page_number, $f_filter . $t_hide_inactive_filter . $t_show_disabled_filter . "&amp;sort=$c_sort&amp;dir=$c_dir");
		?>
	</div>
</div>
</div>
</div>
<?php
layout_page_end();
