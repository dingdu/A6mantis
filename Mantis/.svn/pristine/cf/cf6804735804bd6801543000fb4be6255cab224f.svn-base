<?php
/**
 * Created by PhpStorm.
 * Notes：处理提交“评论”和“评价”的功能
 * User: Administrator
 * Date: 2018\7\20 0020
 * Time: 9:27
 */

require_once( 'core.php' );
require_api( 'print_api.php' );
require_api( 'user_api.php' );
require_api( 'bug_api.php' );
require_api( 'out_user_api.php' );
require_api( 'gpc_api.php' );


# bug_id
if(isset($_POST['bug_id']) && !empty($_POST['bug_id'])) {
    $bug_id = $_POST['bug_id'];
} else {
    # 显示错误报告
    print_error_page('工单ID不能为空');
}

$userid = auth_get_current_user_id();
# 先判断是否是外部数据
if(isset($_SESSION['is_out_user'])) {
    $is_out_user = true;
    $username = out_user_get_field($userid, 'realname');
} else {
    $is_out_user = true;
    # 工程号补0至5位
    $username = '工程师'.str_pad(auth_get_current_user_id(),
            5,"0",STR_PAD_LEFT).'号';
}

if(!isset($username)) {
    $username = '***************';
}

# 默认评价是评论
$is_pingjia = false;

# --====================== 评价 ================================
# 评价星级
$star = '';
# 要判断是否是评价
if(isset($_POST['is_pingjia']) && !empty($_POST['is_pingjia'])) {
    if(isset($_POST['star']) && !empty($_POST['star'])) {
        $star = $_POST['star'];
        $is_pingjia = true;
    } else {
        # 显示错误报告
        print_error_page('评价等级不能为空');
    }


}

# 评价内容
$pingjia = '';
if(isset($_POST['pingjia']) && !empty($_POST['pingjia'])) {
    $pingjia = $_POST['pingjia'];
} else if($is_pingjia){
    # 显示错误报告
//    print_error_page('评价内容不能为空');
}
# 是否解决
$isSolve = 0;
if(isset($_POST['isSolve'])) {
    $isSolve = $_POST['isSolve'];
} else if($is_pingjia){
    # 显示错误报告
    print_error_page('是否解决必须回答');
}




# --===================== 反馈 ===========================
# 评论内容
$content = '';
if(isset($_POST['content']) && !empty($_POST['content'])) {
    $content = $_POST['content'];
} else if( !$is_pingjia ){
    # 显示错误报告
    print_error_page('评论内容不能为空');
}

# I. 判断这个bug是否是本人提交的
$reporter_id = bug_get_field($bug_id, 'reporter_id');
$category_id = bug_get_field($bug_id, 'category_id');
if( $category_id != 15 || $reporter_id != auth_get_current_user_id() ) {
    print_error_page('只能反馈自己提交的问题');
}

# --===================== 封装数据 ============================
$data = array(
    'is_out_user' => $is_out_user,                  // 是否是外部用户
    'is_pingjia' => $is_pingjia,                    // 是否是评价
    'user_name' => $username,                       // 用户名称：外部是名称，内部是员工号
    'content' => $content,                          // 评论内容
    'time' => date('Y-m-d h:i:s', time()),   // 时间
    'star' => $star,                                // 星级
    'pingjia' => $pingjia,                          // 评价
    'isSolve' => $isSolve,                          // 是否解决
);


# II. 组装$t_data
$t_data = array(
    'query' =>
        array(
            'issue_id' => intval($bug_id),      # bug_id
        ),
    'payload' =>
        array(
            'text' => serialize($data),         # 序列化数据
            'view_state' =>
                array(
                    'id' => 10,                 # 默认就是10
                ),
            'time_tracking' =>
                array(
                    'duration' => '0:00',       # 默认就是0
                ),
            'files' =>
                array(),
        ),
);

# III. 调用IssueNoteAddCommand组件插入注释记录
$t_command = new IssueNoteAddCommand($t_data);
$t_command->execute();

// 如果是未解决则改变问题的状态为已受理
if($isSolve == 0) {
    bug_set_field($bug_id, 'status', 50);
}

print_successful_redirect('view_bug_detail_page.php?bug_id='.$bug_id);
