<?php
/**
 * Created by PhpStorm.
 * User: 丁度铭
 * Date: 2018\7\17 0017
 * Time: 16:25
 * function ： 关闭和加急问题
 */

require_once( 'core.php' );
require_api( 'print_api.php' );
require_api( 'bug_api.php' );
require_api('authentication_api.php');

# 关闭问题
if(isset($_GET['close']) && $_GET['close'] == 1 && isset($_GET['bug_id'])) {
    # status:90 为关闭
    bug_set_field(intval($_GET['bug_id']), 'status', 90);
    $t_redirect_url = 'view_all_bug_page.php';
}

/**
 * 加急
 * 说明： 实际上加急不算是状态改变，而是往bug中插入注释（text=“加急”）
 */
if(isset($_GET['jiaji']) && $_GET['jiaji'] == 1 && isset($_GET['bug_id'])) {
    # I. 判断此bug_id是否是用户本人
    $out_user_id = auth_get_current_user_id();
    $realname = out_user_get_field($out_user_id, 'realname');
    $reporter_id = bug_get_field(intval($_GET['bug_id']), 'reporter_id');
    if($out_user_id == $reporter_id) {
        # II. 判断bug是否加急过
        if(!bug_hasjiaji($_GET['bug_id'])) {
            $data = array(
                'is_out_user' => true,        // 是否是外部用户
                'is_pingjia' => false,         // 是否是评价
                'user_name' => $realname,          // 用户名称：外部是名称，内部是员工号
                'content' => '加急',            // 评论内容
                'time' => date('Y-m-d h:i:s', time()),               // 时间
                'star' => '',               // 星级
                'pingjia' => '',            // 评价
                'isSolve' => false             // 是否解决
            );


            # III. 组装$t_data
            $t_data = array(
                'query' =>
                    array(
                        'issue_id' => intval($_GET['bug_id']),      # bug_id
                    ),
                'payload' =>
                    array(
                        'text' => serialize($data),
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

            # IV. 调用IssueNoteAddCommand组件插入注释记录
            $t_command = new IssueNoteAddCommand($t_data);
            $t_command->execute();

            # V. 将bug的优先级提升至“紧急”
            bug_set_field($_GET['bug_id'], 'priority', 50);
        }
    } else {
        # 显示错误报告
        print_error_page('必须由报告bug本人执行加急');
    }

}


print_header_redirect( $t_redirect_url );