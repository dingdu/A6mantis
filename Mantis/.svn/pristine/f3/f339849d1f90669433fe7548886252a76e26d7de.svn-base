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
 * This page stores the reported bug
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses error_api.php
 * @uses file_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'constant_inc.php' );
require_api( 'custom_field_api.php' );
require_api( 'error_api.php' );
require_api( 'file_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'utility_api.php' );
db_param_push();
$f_act           = gpc_get_string('act','');

if($f_act =='') {
	$f_id = gpc_get_int('id', 0);
	$dat = [];
	$data['status'] = 1;
	$data['message'] = '成功';

	if (empty($f_id)) {
		$data['status'] = 0;
		$data['message'] = '参数错误';
		exit(json_encode($data));
	}

	$sql = "select  * from {supply_user_work}  where supply_id =" . db_param() . ' limit 1';
	$t_result = db_query($sql, [$f_id]);
	$res = db_fetch_array($t_result);


	$due_date = date('Y-m-d',$res['add_time']);
	$startTime = strtotime(date("{$due_date} 00:00:00"));
	$endTime = strtotime(date("{$due_date} 23:59:59"));
	$op_user_id =$res['user_id'];
	$sql = "select sum(work_hours) as total from {user_work_log}  where add_time>={$startTime} and add_time<={$endTime}" . " and user_id =" . db_param();
	$t_result = db_query($sql, [$op_user_id]);
	$res2 = db_fetch_array($t_result);
	if($res2['total']==''){
		$res2['total'] = 0;
	}
	if (($res['work_hours']+$res2['total'])>8) {
		$data['status'] = 0;
		$data['message'] = '你还差'.(8-$res2['total']).'个工时,不能补工时';
		exit(json_encode($data));
	}

// 选择补充工时的时间  查看当前工时




//  description
	$description = gpc_get_string('description', '');
	$t_user_work_log = array(
		'user_id' => $res['user_id'],
		'project_id' => $res['project_id'],
		'task_bug_id' => $res['task_bug_id'],
		'work_hours' => $res['work_hours'],
		'add_time' => $res['add_time'],
		'description' => $res['description'],
		'reality_work_hours' => $res['reality_work_hours'],
		'work_type' => $res['work_type'],
	);
	$t_query = 'INSERT INTO {user_work_log}
					( user_id, project_id, task_bug_id, work_hours, add_time, description,reality_work_hours,work_type)
				  VALUES
					( ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ')';
	db_query($t_query, $t_user_work_log);
	$f_master_bug_id = db_insert_id(db_get_table('supply_user_work'));
	$f_user_id = auth_get_current_user_id();
	$sql = "update  {supply_user_work} set status =2,handle_id=" . db_param() . "  where supply_id =" . db_param() . ' limit 1';
	$t_result = db_query($sql, [$f_user_id, $f_id]);
	exit(json_encode($data));
}elseif($f_act=='del'){
	$data['status'] = 1;
	$data['message'] = '成功';
	$f_id           = gpc_get_string('id','');
    if($f_id==''){
		$data['status'] = 0;
		$data['message'] = '参数错误';
	}
	$sql = "delete from {supply_user_work} where supply_id = ".db_param();
	db_query($sql, [$f_id]);
	exit(json_encode($data));
}
#记录总工时

//	$t_query = 'select * from  {user_ext} where user_id = ' . db_param();
//	$t_result = db_query($t_query, array($f_user_id));
//	$res = db_fetch_array($t_result);
//	if ($res == '') {
//		$t_query = 'INSERT INTO  {user_ext}
//				 ( user_id, skill, total_work_hours)
//	VALUES
//	( ' . db_param() . ', ' . db_param() . ', ' . db_param() . ')';
//		db_query($t_query, array($f_user_id, 1, $f_work_hours));
//	} else {
//		$t_query = 'UPDATE {user_ext}
//				  SET total_work_hours=total_work_hours+' . db_param() . '
//				  WHERE user_id=' . db_param();
//
//		db_query($t_query, array($f_work_hours, $f_user_id));
//	}





# return the id of the new project







