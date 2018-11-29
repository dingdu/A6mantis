<?php
/**
 * Notice: 项目规定时间内完成的奖金
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\8\9 0009
 * Time: 15:04
 */

//require_once('../core.php');

/**
 * Notes: 根据项目的ID获取对应设置的项目奖金
 * User: dingduming
 * Date: 2018\8\9 0009
 * @param $project_id
 */
function get_bonus_list_by_project_id($project_id) {
    if(empty($project_id)) {
        return array();
    }
    $t_sql = 'SELECT * FROM {project_bonus} WHERE project_id='.$project_id.' ORDER BY deadline ASC';
    $t_rs = db_query($t_sql);
    $bonus_list = array();
    while($t_row = db_fetch_array($t_rs)) {
        $t_row['deadline'] = date('Y-m-d', $t_row['deadline']);
        $bonus_list[] = $t_row;
    }
    return $bonus_list;
}

/**
 * Notes: 创建对应项目的奖金
 * User: dingduming
 * Date: 2018\8\9 0009
 * @param $project_id
 * @param $deadline
 * @param $bonus
 */
function create_bonus_to_project($project_id, $deadline, $bonus) {
    $t_sql = 'INSERT INTO {project_bonus} (project_id,deadline,bonus) VALUES ('.
        $project_id . ',' . strtotime($deadline). ',' . $bonus . ')';
    $t_rs = db_query($t_sql);
}


/**
 * Notes: 修改奖金参数（项目id不可更改）
 * User: dingduming
 * Date: 2018\8\9 0009
 * @param $id
 * @param $deadline
 * @param $bonus
 */
function update_bonus_to_project($id, $deadline, $bonus) {
    $t_sql = 'UPDATE {project_bonus} SET deadline=' . strtotime($deadline) . ',bonus='. $bonus . ' WHERE id='.$id;
    $t_rs = db_query($t_sql);
    var_dump($t_rs);
}

/**
 * Notes: 删除对应的奖金
 * User: dingduming
 * Date: 2018\8\9 0009
 * @param $id
 */
function delete_bonus_to_project($id) {
    $t_sql = 'DELETE FROM {project_bonus} WHERE id='. $id;
    $t_rs = db_query($t_sql);
}

//create_bonus_to_project(1, '2018-9-29', 0);
//var_dump(get_bonus_list_by_project_id(1));
//update_bonus_to_project(1, '2018-9-8', 10000.03);
//delete_bonus_to_project(1);