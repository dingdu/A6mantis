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

$f_project_id = gpc_get_int( 'project_id', 0 );
$f_user_id =  auth_get_current_user_id();
$f_task_bug_id = gpc_get_int( 'bug_id', 0 );
$f_work_hours =  gpc_get_string( 'work_hours','');
$work_type =  gpc_get_int( 'work_type','');
$act  = gpc_get_string( 'act','');
$startTime = strtotime(date('Ymd00:00:00'));
$endTime = time();

if($act=='') {
	form_security_validate( 'user_work_log' );
	if ($f_project_id == '' || $f_user_id == '' || $f_task_bug_id == '' || $f_work_hours == '') {
		exit(json_encode(['status' => 0, 'msg' => '非法访问']));
	}
//  description
	$description = gpc_get_string('description', '');
	$t_user_work_log = array(
		'user_id' => $f_user_id,
		'project_id' => $f_project_id,
		'task_bug_id' => $f_task_bug_id,
		'work_hours' => $f_work_hours,
		'add_time' => time(),
		'description' => $description
	);

// 检查今天的总工时  最高8个工时
	$op_user_id = auth_get_current_user_id();
	$sql = "select sum(work_hours) as total from {user_work_log}  where add_time>={$startTime} and add_time<={$endTime}" . " and user_id =" . db_param();
	$t_result = db_query($sql, [$op_user_id]);
	$res = db_fetch_array($t_result);
	if (isset($res['total']) && $res['total'] > 0) {
		if ($f_work_hours + $res['total'] > 8) {
			$f_work_hours = 0; // 不能再占用工时
		}
	}
// 重新计算 真实 工时
	$sql = "select skill from {user_ext} where user_id = " . db_param();
	$user_res = db_query($sql, [$f_user_id]);
	$user = db_fetch_array($user_res);
	if (isset($user['skill']) && $user['skill'] > 0) {
		$reality_dev_work_hours = ($f_work_hours * $user['skill']);
	} else {
		$reality_dev_work_hours = $f_work_hours;
	}
	$t_user_work_log['reality_work_hours'] = $reality_dev_work_hours;
	$t_user_work_log['work_type'] = $work_type;
	$t_query = 'INSERT INTO {user_work_log}
					( user_id, project_id, task_bug_id, work_hours, add_time, description,reality_work_hours,work_type)
				  VALUES
					( ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ')';
	db_query($t_query, $t_user_work_log);
	$f_master_bug_id = db_insert_id(db_get_table('user_work_log'));
#记录总工时
	$t_query = 'select * from  {user_ext} where user_id = ' . db_param();
	$t_result = db_query($t_query, array($f_user_id));
	$res = db_fetch_array($t_result);
	if ($res == '') {
		$t_query = 'INSERT INTO  {user_ext}
				 ( user_id, skill, total_work_hours)
	VALUES
	( ' . db_param() . ', ' . db_param() . ', ' . db_param() . ')';
		db_query($t_query, array($f_user_id, 1, $f_work_hours));
	} else {
		$t_query = 'UPDATE {user_ext}
				  SET total_work_hours=total_work_hours+' . db_param() . '
				  WHERE user_id=' . db_param();

		db_query($t_query, array($f_work_hours, $f_user_id));
	}


	form_security_purge('user_work_log');


# return the id of the new project


	$url = '/view.php?id=' . $f_task_bug_id;
	header("Location:$url");
}elseif($act=='check'){

	$data['status'] = 0;
	$op_user_id = auth_get_current_user_id();
	$sql = "select sum(work_hours) as total from {user_work_log}  where add_time>={$startTime} and add_time<={$endTime}" . " and user_id =" . db_param();
	$t_result = db_query($sql, [$op_user_id]);
	$res = db_fetch_array($t_result);
	if (isset($res['total']) && $res['total'] > 0) {
		if ($f_work_hours + $res['total'] > 8) {
			$data['status'] = 1;

		}
	}
	echo json_encode($data);
}
//echo json_encode($data);





