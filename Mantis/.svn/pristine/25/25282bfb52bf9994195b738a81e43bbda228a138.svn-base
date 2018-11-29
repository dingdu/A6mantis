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


auth_ensure_user_authenticated();

helper_begin_long_process();

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
$t_summary_header_arr = explode( '/', lang_get( 'summary_count_user_header' ) );
echo column_get_title(lang_get('by_users').',');
echo column_get_title(lang_get('user_level').',');
foreach ( $t_summary_header_arr as $val ) {
	echo column_get_title( $val.',' );
}
echo column_get_title(lang_get('by_count').',');
echo $t_nl;

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

summary_print_by_user_ware_hour($t_nl,$t_date_to_display,$t_keyword,$t_access_level);

function summary_print_by_user_ware_hour($t_nl,$time='',$keyword='',$t_access_level='') {

	$t_summary_header_arr = explode( '/', lang_get( 'summary_count_user_header' ) );

	$where = '';
	if($keyword!=''){
		$where  .= " and username like '%{$keyword}%'";
	}
	if($t_access_level!=''){
		$where  .= " and access_level = {$t_access_level} ";
	}
	$sql = "select id,username,realname,access_level from {user}  where  enabled = 1".$where;

	$t_result = db_query($sql);
	$g_access_levels = MantisEnum::getAssocArrayIndexedByValues( config_get( 'access_levels_enum_string' ) );

	$data = [];
	$i=0;
	while($t_user_list = db_fetch_array($t_result)){
		$user_id = $t_user_list['id'];
		$data[$i]['username'] = $t_user_list['username'];
		$data[$i]['realname'] = $t_user_list['realname'];
		$data[$i]['levelname'] =MantisEnum::getLabel( lang_get( 'access_levels_enum_string' ), $t_user_list['access_level'] ) ;
		$data[$i]['count'] = 0;
		echo csv_escape_string($data[$i]['username'].'('.$t_user_list['realname'].')').',';
		echo csv_escape_string($data[$i]['levelname']).',';
		foreach($t_summary_header_arr as $k=>$val){
			$val = str_pad($val,2, "0", STR_PAD_LEFT);
			if($time!=''){
				$time = str_replace('-','',$time);
				$startTime =  strtotime(date($time.$val." 00:00:00"));
				$endTime = strtotime(date($time.$val." 23:59:59"));

			}else{
				$startTime =  strtotime(date("Ym{$val} 00:00:00"));
				$endTime = strtotime(date("Ym{$val} His"));
			}
			$sql2 = "select sum(work_hours) as total from {user_work_log}  where add_time>={$startTime} and add_time<={$endTime}  and user_id = " . db_param();
			$t_result2 = db_query($sql2, [$user_id]);
			$res2 = db_fetch_array($t_result2);
			$p_ware_hours = $res2['total'];
			$p_ware_hours = sprintf('%.1f', $p_ware_hours);
			if($p_ware_hours==''){
				$p_ware_hours =0;
			}
			$data[$i]['count'] +=$p_ware_hours;
			$data[$i]['ware_hour_data'][$k] =$p_ware_hours;
			echo csv_escape_string($p_ware_hours).',';
		}
		echo csv_escape_string($data[$i]['count']).',';
		echo $t_nl;
		$i++;
	}

}

