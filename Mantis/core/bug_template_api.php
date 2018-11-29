<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\8\4 0004
 * Time: 14:23
 */


//require_once('../core.php');

require_api( 'access_api.php' );
require_api( 'antispam_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'bugnote_api.php' );
require_api( 'bug_revision_api.php' );
require_api( 'category_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'custom_field_api.php' );
require_api( 'database_api.php' );
require_api( 'date_api.php' );
require_api( 'email_api.php' );
require_api( 'error_api.php' );
require_api( 'event_api.php' );
require_api( 'file_api.php' );
require_api( 'helper_api.php' );
require_api( 'history_api.php' );
require_api( 'lang_api.php' );
require_api( 'mention_api.php' );
require_api( 'relationship_api.php' );
require_api( 'sponsorship_api.php' );
require_api( 'tag_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );

use Mantis\Exceptions\ClientException;

class BugTemplate {
    private $id;                // 问题模版id
    private $summary;           // 问题简介
    private $description;       // 问题详情
    private $temp_category_id;  // 问题分类id

    /**
     * Notes: set方法用于赋值并加以处理
     * User: dingduming
     * Date: 2018\8\2 0002
     * @param $p_name
     * @param $p_value
     */
    public function  __set($p_name, $p_value) {
        switch ($p_name) {
            case 'name':
                // 避免中文
                $p_value = db_mysql_fix_utf8( $p_value );
                break;
            case 'description':
                // 避免中文
                $p_value = db_mysql_fix_utf8( $p_value );
                break;
        }
        $this->$p_name = $p_value;
    }
    public function __get( $p_name ) {
        return $this->{$p_name};
    }

    /**
     * Notes: 根据当前模版创建问题
     * User: dingduming
     * Date: 2018\8\4 0004
     * @param $project_id   项目id
     * @param $owner_id     项目负责人id
     */
    public function createBugByTemplate($project_id, $owner_id) {
        $data = array('payload'=>array('issue'=>array()));
        $issue = &$data['payload']['issue'];
        /**
         * 填充数据
         */
        $issue['project'] = ['id'=>$project_id];
        $issue['reporter'] = ['id'=>auth_get_current_user_id()];
        $issue['summary'] = $this->summary;
        $issue['description'] = $this->description;
        $issue['evaluate_time'] = '5';
        $issue['expected_endtime'] = false;
        $issue['expected_starttime'] = false;
        $issue['handler'] = ['id' => $owner_id];           // 问题负责人默认交由项目负责人
        $issue['view_state'] = ['id' => 10];
        $issue['category'] = ['id' => 1];
        $issue['reproducibility'] = ['id' => 70];
        $issue['severity'] = ['id' => 50];
        $issue['priority'] = ['id' => 30];
        $issue['steps_to_reproduce'] = '';
        $issue['additional_information'] = '';
        // 创建问题
        $t_command = new IssueAddCommand( $data );
        $t_result = $t_command->execute();
    }

    /**
     * Notes: 通过id查询数据库来填充其他数据
     * User: dingduming
     * Date: 2018\8\2 0002
     */
    public function fillBugTemplateById() {
        $t_sql = 'SELECT * FROM {bug_template} WHERE id='.$this->id;
        $t_rs = db_query($t_sql);
        $t_row = db_fetch_array($t_rs);
        if(empty($t_row))
            return false;
        $this->summary = $t_row['summary'];
        $this->description = $t_row['description'];
        $this->temp_category_id = $t_row['temp_category_id'];
    }

    /**
     * Notes: 创建问题模版
     * User: dingduming
     * Date: 2018\8\4 0004
     */
    public function createBugTemplate() {
        $t_sql = 'INSERT INTO {bug_template} (summary,description,temp_category_id) VALUES ("'
            . $this->summary . '","' . $this->description . '",' . $this->temp_category_id . ')';
        return db_query($t_sql);
    }

    /**
     * Notes: 修改问题模版
     * User: dingduming
     * Date: 2018\8\2 0002
     */
    public function udpateBugTemplate() {
        $t_sql = 'UPDATE {bug_template} SET summary="' . $this->summary . '",
            description = "'. $this->description .'", temp_category_id='. $this->temp_category_id
            .' WHERE id='.$this->id;
        $t_rs = db_query($t_sql);
    }
}

/**
 * Notes: 根据id获取模版信息
 * User: dingduming
 * Date: 2018\8\4 0004
 * @param $template_id
 */
function getTemplateById($template_id){
    $t_sql = 'SELECT * FROM {bug_template} WHERE id='.$template_id.' LIMIT 1';
    $t_rs = db_query($t_sql);
    return db_fetch_array($t_rs);
}

/**
 * Notes: 获取模版
 * User: dingduming
 * Date: 2018\8\4 0004
 */
function getTemplates() {
    $t_sql = 'SELECT * FROM {bug_template} ';
    $t_rs = db_query($t_sql);
    $template_list = array();
    while($row = db_fetch_array($t_rs)) {
        $template_list[] = $row;
    }
    return $template_list;
}

/**
 * Notes: 根据模版id数组来创建一堆问题
 * User: dingduming
 * Date: 2018\8\4 0004
 * @param $templates
 */
function createBugsByTemplates($template_ids, $project_id, $owner_id) {
    $bt = new BugTemplate();
    foreach ($template_ids as $id) {
        $bt->id = $id;
        $bt->fillBugTemplateById();
        // 根据模版创建问题
        $bt->createBugByTemplate($project_id, $owner_id);
    }
}

/**
 * Notes: 获取所有模版分类
 * User: dingduming
 * Date: 2018\8\4 0004
 * @return bool
 */
function getAllBugTemplateCategory() {
    $t_sql = 'SELECT * FROM {bug_template_category}';
    $t_rs = db_query($t_sql);
    $cate_list = array();
    while($row = db_fetch_array($t_rs)) {
        $cate_list[] = $row;
    }
    return $cate_list;
}

/**
 * Notes: 根据问题模版分类获取对应问题模版
 * User: dingduming
 * Date: 2018\8\4 0004
 */
function getBugTemplatesByCategory($temp_category_id) {
    $t_sql = 'SELECT * FROM {bug_template} WHERE temp_category_id='. $temp_category_id;
    $t_rs = db_query($t_sql);
    $template_list = array();
    while($row = db_fetch_array($t_rs)) {
        $template_list[] = $row;
    }
    return $template_list;
}

/**
 * Notes: 判断对应名称的模版分类是否存在
 * User: dingduming
 * Date: 2018\8\4 0004
 * @param $name
 */
function checkBugTemplateCategoryExist($name) {
    $t_sql = 'SELECT 1 FROM {bug_template_category} WHERE name="'.$name.'" LIMIT 1';
    $t_rs = db_query($t_sql);
    if(db_fetch_array($t_rs)) {
        return true;
    }
    return false;
}

/**
 * Notes: 创建问题模版分类
 * User: dingduming
 * Date: 2018\8\4 0004
 * @param $name
 */
function createBugTemplateCategory($name) {
    // 先判断是否有同名分类
    if(checkBugTemplateCategoryExist($name)) {
        return false;
    }
    $t_sql = 'INSERT INTO {bug_template_category} (name) VALUES ("' . $name . '")';
    return db_query($t_sql);
}

/**
 * Notes: 删除指定模版
 * User: dingduming
 * Date: 2018\8\4 0004
 * @param $id
 */
function delBugTemplate($id) {
    $t_sql = 'DELETE FROM {bug_template} WHERE id='.$id;
    return db_query($t_sql);
}



/**
 * Notes: 删除指定模版分类
 * User: dingduming
 * Date: 2018\8\4 0004
 * @param $id
 */
function delBugTemplateCategory($id) {
    $t_sql = 'DELETE FROM {bug_template_category} WHERE id='.$id;
    return db_query($t_sql);
}



/**
 * Notes: 修改问题模版分类（修改名称）
 * User: dingduming
 * Date: 2018\8\4 0004
 * @param $id
 * @param $name
 */
function updateBugTemplateCategory($id, $name) {
    $t_sql = 'UPDATE {bug_template_category} SET name="' . $name . '" WHERE id='.$id;
    return db_query($t_sql);
}

$bt = new BugTemplate();
$bt->id = 1;
$bt->summary = '模版1';
$bt->description = '模版';
$bt->temp_category_id = 1;

// 测试创建问题模版
//$bt->createBugTemplate();
// 测试修改问题模版
//$bt->udpateBugTemplate();

//
//$bt->createBugByTemplate(101, 1);

// 测试创建问题模版分类类型
//createBugTemplateCategory('大版本基础版');
// 测试修改问题模版分类类型
//updateBugTemplateCategory(2, '大版本1');

// 获取分类列表
//var_dump(getAllBugTemplateCategory());
// 测试删除模版分类
//delBugTemplateCategory(2);

// 测试根据分类获取问题模型
//var_dump(getBugTemplatesByCategory(2));