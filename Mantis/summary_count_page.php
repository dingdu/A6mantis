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
 * Display summary page of Statistics
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
 * @uses lang_api.php
 * @uses print_api.php
 * @uses summary_api.php
 * @uses user_api.php
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
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'summary_api.php' );
require_api( 'user_api.php' );

$f_project_id = gpc_get_int( 'project_id', helper_get_current_project() );

# Override the current page to make sure we get the appropriate project-specific configuration
$g_project_override = $f_project_id;

access_ensure_project_level( config_get( 'view_summary_threshold' ) );

$t_time_stats = summary_helper_get_time_stats( $f_project_id );

$t_summary_header_arr = explode( '/', lang_get( 'summary_count_user_header' ) );
$t_summary_header = '';
$daynow = date('d');
foreach ( $t_summary_header_arr as $t_summary_header_name ) {
    if($daynow == $t_summary_header_name) $t_summary_header .= '<th class="align-right today">';
	else $t_summary_header .= '<th class="align-right">';
	$t_summary_header .= $t_summary_header_name;
	$t_summary_header .= '</th>';
}

layout_page_header( lang_get( 'summary_link' ) );

layout_page_begin( 'summary_count_page.php' );


print_summary_menu( 'summary_page.php' );
print_summary_submenu();

$t_date_to_display = gpc_get_string( 'due_date',date('Y-m'));
$t_keyword = gpc_get_string( 'keyword','');
$t_access_level = gpc_get_string( 'access_level','');
?>
<style>
    th.today,td.today{
        background-color: #cfeafd;
    }
    td.notice{
        background-color: #ffdcc9;
    }
</style>
<div class="col-md-12 col-xs-12">
<div class="space-10"></div>

<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
	<h4 class="widget-title lighter">
		<i class="ace-icon fa fa-bar-chart-o"></i>
		<?php echo lang_get('by_user_day_work_hour_count') ?>
	</h4>
</div>

<div class="widget-body">

<div class="widget-main no-padding">
    <form>
	<div class="col-md-6 col-xs-12  pull-right">
		 <div class="form-inline">

             <select id="user-access-level" name="access_level" class="input-sm">
                 <option value="" selected="selected"></option>
                 <?php print_project_access_levels_option_list() ?>
             </select>

			 <div class="input-group">
			 <span class="input-group-addon">时间</span>
			 <?php echo '<input ' . helper_get_tab_index() . ' type="text" id="due_date1" name="due_date" class="form-control fc-clear datetimepicker input-sm" ' .
				 'data-picker-locale="' . lang_get_current_datetime_locale() .
				 '" data-picker-format="' . config_get( 'datetime_picker_format_month' ) . '" ' .
				 'size="20" maxlength="16" value="' . $t_date_to_display . '" />' ?>
			 <i class="fa fa-calendar fa-xlg datetimepicker"></i>
			</div>
			<input type="text" name="keyword" class="form-control" placeholder="搜索用户名"  value="<?php echo $t_keyword?>"/>
			<button type="submit" class="btn btn-primary btn-xs">搜 索</button>
			 <a class="btn btn-primary btn-white btn-round btn-sm" href="/user_csv_export.php?due_date?<?php echo $t_date_to_display?>&keyword=<?php echo $t_keyword?>&access_level=<?php echo $t_access_level?>">导出为Excel</a>
		</div>

	</form>
</div>

<!-- LEFT COLUMN -->
<div class="col-md-12 col-xs-12">

	<!-- BY PROJECT -->
	<div class="space-10"></div>
	<div class="widget-box table-responsive">
		<table class="table table-hover table-bordered table-condensed table-striped">
		<thead>
			<tr>
				<th class="width-10"><?php echo lang_get( 'by_users' ) ?></th>
				<th class="width-10"><?php echo lang_get( 'user_level' ) ?></th>
				<?php echo $t_summary_header ?>
				<th class="width-10"><?php echo lang_get( 'by_count' ) ?></th>
			</tr>
		</thead>
		<?php summary_print_by_user_ware_hour($t_date_to_display,$t_keyword,$t_access_level); ?>
	</table>
	</div>



</div>


</div>
</div>
<div class="clearfix"></div>
<div class="space-10"></div>
</div>
</div>

<script>
	$("#due_date1").datetimepicker({
		format: 'yyyy-mm-dd'
	});
</script>

<?php
layout_page_end();
