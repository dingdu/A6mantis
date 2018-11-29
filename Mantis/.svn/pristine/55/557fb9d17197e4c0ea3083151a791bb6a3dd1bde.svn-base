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
 * This file implements CSV export functionality within MantisBT
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses columns_api.php
 * @uses constant_inc.php
 * @uses csv_api.php
 * @uses file_api.php
 * @uses filter_api.php
 * @uses helper_api.php
 * @uses print_api.php
 */

# Prevent output of HTML in the content if errors occur
define( 'DISABLE_INLINE_ERROR_REPORTING', true );

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'columns_api.php' );
require_api( 'constant_inc.php' );
require_api( 'csv_by_user_api.php' );
require_api( 'file_api.php' );
require_api( 'filter_api.php' );
require_api( 'helper_api.php' );
require_api( 'print_api.php' );


$t_nl = csv_get_newline();
$t_sep = csv_get_separator();

# Get current filter
$t_filter = filter_get_bug_rows_filter();


# Get columns to be exported
$t_columns = csv_get_columns();
// csv_get_default_filename().
$t_cvs_name =date('Ymd').'.csv';
csv_start( $t_cvs_name);

# export the titles
$t_first_column = true;
ob_start();
$t_titles = array();
echo column_get_title(lang_get('by_project').',');

$t_summary_header_arr = explode( '/', lang_get( 'summary_count_by_dever_header' ) );

foreach ( $t_summary_header_arr as $val ) {
	echo column_get_title( $val.',' );
}
echo $t_nl; // 换行

$t_header = ob_get_clean();

# Fixed for a problem in Excel where it prompts error message "SYLK: File Format Is Not Valid"
# See Microsoft Knowledge Base Article - 323626
# http://support.microsoft.com/default.aspx?scid=kb;en-us;323626&Product=xlw
$t_first_three_chars = mb_substr( $t_header, 0, 3 );
if( strcmp( $t_first_three_chars, 'ID' . $t_sep ) == 0 ) {
	$t_header = str_replace( 'ID' . $t_sep, 'Id' . $t_sep, $t_header );
}

# end of fix
$t_date_to_display = gpc_get_string( 'due_date',date('Y-m'));
$t_keyword = gpc_get_string( 'keyword','');
$t_access_level = gpc_get_string( 'access_level','');

echo $t_header;

$t_end_of_results = false;
$t_offset = 0;

$t_date_to_display = gpc_get_string( 'due_date','');
$t_page = gpc_get_string( 'page','');


summary_print_by_projects($t_nl,0,$t_date_to_display);
function summary_helper_get_bugratio( $p_bugs_open, $p_bugs_resolved, $p_bugs_closed, $p_bugs_total_count) {
	$t_bugs_total = $p_bugs_open + $p_bugs_resolved + $p_bugs_closed;
	$t_bugs_resolved_ratio = ( $p_bugs_resolved + $p_bugs_closed ) / ( $t_bugs_total == 0 ? 1 : $t_bugs_total );
	$t_bugs_ratio = $t_bugs_total / ( $p_bugs_total_count == 0 ? 1 : $p_bugs_total_count );
	$t_bugs_resolved_ratio = sprintf( "%.1f%%", $t_bugs_resolved_ratio * 100 );
	$t_bugs_ratio = sprintf( "%.1f%%", $t_bugs_ratio * 100 );
	return array($t_bugs_resolved_ratio, $t_bugs_ratio);
}
function summary_print_by_projects( $t_nl,$type=0,$Time='',$t_keyword='',array $p_projects = array(), $p_level = 0, array $p_cache = null ) {
	$t_project_id = helper_get_current_project();

	if( empty( $p_projects ) ) {
		if( ALL_PROJECTS == $t_project_id ) {
			$p_projects = current_user_get_accessible_projects();
		} else {
			$p_projects = array(
				$t_project_id,
			);
		}
	}

	# Retrieve statistics one time to improve performance.
	if( null === $p_cache ) {
		$t_query = 'SELECT project_id, status, COUNT( status ) AS bugcount
					FROM {bug}
					GROUP BY project_id, status';

		$t_result = db_query( $t_query );
		$p_cache = array();
		$t_bugs_total_count = 0;

		while( $t_row = db_fetch_array( $t_result ) ) {
			$t_project_id = $t_row['project_id'];
			$t_status = $t_row['status'];
			$t_bugcount = $t_row['bugcount'];
			$t_bugs_total_count += $t_bugcount;
			//summary_helper_build_bugcount( $p_cache, $t_project_id, $t_status, $t_bugcount );
		}
		$p_cache["_bugs_total_count_"] = $t_bugs_total_count;
	}

	$t_bugs_total_count = $p_cache["_bugs_total_count_"];
	foreach( $p_projects as $t_project ) {

		$t_name = str_repeat( '&raquo; ', $p_level ) . project_get_name( $t_project );

		$t_pdata = isset( $p_cache[$t_project] ) ? $p_cache[$t_project] : array( 'open' => 0, 'resolved' => 0, 'closed' => 0 );

		$t_bugs_open = isset( $t_pdata['open'] ) ? $t_pdata['open'] : 0;
;
		$t_bugs_resolved = isset( $t_pdata['resolved'] ) ? $t_pdata['resolved'] : 0;

		$t_bugs_closed = isset( $t_pdata['closed'] ) ? $t_pdata['closed'] : 0;

		$t_bugs_total = $t_bugs_open + $t_bugs_resolved + $t_bugs_closed;

		// 查找项目附属表
		$t_query1 = 'SELECT *
					FROM {project_ext}
					where project_id =  '.db_param();

		$t_result1 = db_query( $t_query1,[$t_project] );
		$project_ext =  db_fetch_array($t_result1);
		$p_dev_work_hours = $project_ext['dev_work_hours'];

		$p_total_work_hours = ($project_ext['dev_work_hours']+$project_ext['req_work_hours']+$project_ext['test_work_hours'])*8;

		$p_reality_total_work_hours = ($project_ext['reality_dev_work_hours']+$project_ext['reality_test_work_hours']+$project_ext['reality_req_work_hours'])*8;

		if ($type == 1){
			$op_user_id = auth_get_current_user_id();
			$where = '';

			if($Time!=''){
				$Time = str_replace('-','',$Time);
				$startTime = strtotime(date("{$Time}d 00:00:00"));
				$endTime = strtotime(date("{$Time}d 23:59:59"));
				$where .= " add_time>={$startTime} and add_time<={$endTime}";

			}

			$sql = "select sum(work_hours) as total,sum(reality_work_hours) as standard from {user_work_log}  where  ".$where." and project_id=" . db_param() . " and user_id = " . db_param();

			$t_result = db_query($sql, [$t_project,$op_user_id]);

			$res = db_fetch_array($t_result);
			$p_ware_hours = $res['total'];
			$p_standard = $res['standard'];// 标准工时
		}else{
			$where = ' 1 ';
			if($Time!='') {
				$Time = str_replace('-', '', $Time);
				$startTime = strtotime(date("{$Time}d 00:00:00"));
				$endTime = strtotime(date("{$Time}d 23:59:59"));
				$where .= " add_time>={$startTime} and add_time<={$endTime} ";
			}

			$sql = "select sum(work_hours) as total,sum(reality_work_hours) as standard from {user_work_log}  where ".$where." and project_id = " . db_param();
			$t_result = db_query($sql, [$t_project]);
			$res = db_fetch_array($t_result);
			$p_ware_hours = $res['total'];
			$p_standard = $res['standard'];// 标准工时
		}

		$p_ware_hours2 = $project_ext['current_take_hours'];
		if($p_ware_hours==0){
			$p_ware_hours = 0;
		}
		if($p_ware_hours2==0){
			$p_ware_hours2 = 0;
		}
		$t_bugs_ratio = summary_helper_get_bugratio( $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total_count);

		echo csv_escape_string(string_display_line( $t_name )).',';
		echo csv_escape_string($t_bugs_open).',';
		echo csv_escape_string($t_bugs_resolved).',';
		echo csv_escape_string($t_bugs_closed).',';
		echo csv_escape_string($t_bugs_total).',';
		echo csv_escape_string($t_bugs_ratio[0]).',';
		echo csv_escape_string($t_bugs_ratio[1]).',';
		echo csv_escape_string($p_total_work_hours).',';
		echo csv_escape_string($p_reality_total_work_hours).',';
		echo csv_escape_string($p_ware_hours).',';
		echo csv_escape_string($p_standard).',';
		echo $t_nl;
	}
}
