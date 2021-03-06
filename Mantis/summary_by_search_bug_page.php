<?php
/**
 * Notice: 统计单个用户、单个身份、某一月份、按照项目
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

access_ensure_project_level( config_get( 'view_summary_threshold' ) );



// 获取搜索框内容
//db_param_push();
//$f_user_id = gpc_get_int( 'user_id', 0 );
//$f_type =   gpc_get_string( 'type', '' );

// 获取当前页码
$f_page_number = gpc_get_int('page_number', 1);
// 每页问题个数
$f_per_number = 10;

$f_proj_name = gpc_get_string( 'proj_name', '' );
$f_project_id = gpc_get_int( 'project_id', '' );
$f_bug_summary = gpc_get_string( 'bug_summary', '' );
$f_work_summary = gpc_get_string( 'work_summary', '' );
$f_user_id = gpc_get_int('handler_id', 0);
//$f_handler_id = gpc_get_int('handler_id', 0);
$f_reporter_id = gpc_get_int('reporter_id', 0);
// 这里0表示
$f_reporter_access_level = gpc_get_int('reporter_access_level', 0);
$f_handler_access_level = gpc_get_int('handler_access_level', 0);
// 月份（0表示当前月份）
$f_due_date = gpc_get_string('due_date', '');

if(empty($f_user_id)) {
    $user_id_sql = '';
} else {
    $user_id_sql = ' AND b.handler_id = '. $f_user_id;
}

if(empty($f_project_id)) {
    $prject_id_sql = '';
} else {
    $prject_id_sql = ' AND b.project_id = '. $f_project_id;
}

// 默认所有时间
if(empty($f_due_date)) {
    $due_date_sql = '';
} else {
    // 判断日期格式是否正确
    $year = date('Y', $f_due_date);
    $month = date('m', $f_due_date);
    if(checkdate($month, '01', $year)) {
        $startTime = strtotime($f_due_date);
        $endTime = strtotime('+1 month', $startTime) - 1;
        if(empty($startTime)) {
            print_error_page('日期格式不正确');
        }
        $due_date_sql =  " AND b.date_submitted>=". $startTime ." AND b.date_submitted<=". $endTime;
    } else {
        print_error_page('日期格式不正确');
    }

}

if(empty($f_proj_name)) {
    $proj_name_sql = '';
} else {
    $proj_name_sql = ' AND p.name like "%'. $f_proj_name .'%" ';
}

if(empty($f_bug_summary)) {
    $bug_summary_sql = '';
} else {
    $bug_summary_sql = ' AND b.summary like "%'. $f_bug_summary .'%" ';
}

if(empty($f_work_summary)) {
    $work_summary_sql = '';
} else {
    $work_summary_sql = ' AND uwl.description like "%'. $f_work_summary . '%" ';
}

if(empty($f_reporter_id)) {
    $reporter_id_sql = '';
} else {
    $reporter_id_sql = ' AND b.reporter_id = '.$f_reporter_id;
}

if(empty($f_reporter_access_level)) {
    $reporter_access_level_sql = '';
} else {
    $reporter_access_level_sql = ' AND ur.access_level = '. $f_reporter_access_level;
}

if(empty($f_handler_access_level)) {
    $handler_access_level_sql = '';
} else {
    $handler_access_level_sql = ' AND uh.access_level = '. $f_handler_access_level;
}


// 先获取总数
$sql = "select count(1) as total_count,sum(uwl.work_hours) as total_hours from {user_work_log}  as uwl ".
    "left join {bug} as b on b.id =uwl.task_bug_id left join {project} as p on p.id = uwl.project_id ".
    " left join {project_ext} as pe on pe.project_id = uwl.project_id 
    left join {user} as uh on uh.id=b.handler_id 
    left join {user} as ur on ur.id=b.reporter_id where 1=1 ".
    $user_id_sql . $due_date_sql . $handler_access_level_sql . $reporter_access_level_sql . $reporter_id_sql .
    $bug_summary_sql . $work_summary_sql . $proj_name_sql . $bug_summary_sql . $prject_id_sql
    .' GROUP BY b.id';
$t_result = db_query($sql);
$t_summary_arr = array();
$f_total_hours = 0;
while($row = db_fetch_array($t_result)) {
    $t_summary_arr[] = $row;
    $f_total_hours += $row['total_hours'];
    $f_total_count = $row['total_count'];
}
$f_total_count = count($t_summary_arr);
//var_dump($f_total_hours,$f_total_count);die;
//

// 获取各个项目对应搜索结果的工时比例和
// 先获取总数
$sql = "select p.id as proj_id,p.name, sum(uwl.work_hours) as proj_total_hours from {user_work_log}  as uwl ".
    "left join {bug} as b on b.id =uwl.task_bug_id left join {project} as p on p.id = uwl.project_id ".
    " left join {project_ext} as pe on pe.project_id = uwl.project_id 
    left join {user} as uh on uh.id=b.handler_id 
    left join {user} as ur on ur.id=b.reporter_id where 1=1 ".
    $user_id_sql . $due_date_sql . $handler_access_level_sql . $reporter_access_level_sql . $reporter_id_sql .
    $bug_summary_sql . $work_summary_sql . $proj_name_sql . $bug_summary_sql . $prject_id_sql .
    " GROUP BY p.id";
$t_result = db_query($sql);
$f_summary_proj = array();
while($row = db_fetch_array($t_result)) {
    $f_summary_proj[] = $row;
}
// 计算总页数
$f_total_page = ceil($f_total_count / $f_per_number);

//$sql = "select b.summary,ur.realname as reporter_name,uh.realname as handler_name,b.evaluate_time,p.name,pe.req_work_hours,pe.dev_work_hours,pe.test_work_hours,pe.reality_dev_work_hours,pe.reality_req_work_hours,pe.reality_test_work_hours from {user_work_log}  as uwl ".
//    "left join {bug} as b on b.id =uwl.task_bug_id left join {project} as p on p.id = uwl.project_id ".
//    " left join {project_ext} as pe on pe.project_id = uwl.project_id
//    left join {user} as uh on uh.id=b.handler_id
//    left join {user} as ur on ur.id=b.reporter_id where 1=1 ".
//    $user_id_sql . $due_date_sql . $handler_access_level_sql . $reporter_access_level_sql .
//    $reporter_id_sql . $bug_summary_sql . $work_summary_sql . $proj_name_sql .
//    ' GROUP BY uwl.task_bug_id ORDER BY b.date_submitted DESC LIMIT '. ($f_page_number-1)*$f_per_number. ', '. $f_per_number;

// 查询当前页内容
    $sql = 'select b.id as bug_id,b.project_id as proj_id,b.date_submitted,p.name as project_name,sum(uwl.work_hours) as work_hours,
        b.summary, ur.realname as reporter_name,uh.realname as handler_name, 
        b.evaluate_time from {bug}  as b 
        left join {user_work_log} as uwl on uwl.task_bug_id = b.id '.
        'left join {project} as p on p.id=b.project_id '.
        'left join {user} as uh on uh.id=b.handler_id '.
        'left join {user} as ur on ur.id=b.reporter_id where 1=1'.
        $user_id_sql . $due_date_sql . $handler_access_level_sql . $reporter_access_level_sql .
        $reporter_id_sql . $bug_summary_sql . $work_summary_sql . $proj_name_sql . $prject_id_sql .
        ' GROUP BY b.id ORDER BY b.date_submitted DESC LIMIT '. ($f_page_number-1)*$f_per_number. ', '. $f_per_number;


$t_result = db_query($sql);
$t_history = [];
while($t_row = db_fetch_array($t_result)){
	$t_history[] = $t_row;
}

//echo($t_result->sql);die;

//var_dump($t_history);die;

// 获取所有项目的进度
$t_projects = user_get_accessible_projects( auth_get_current_user_id(), true , $search_condition);
$t_full_projects = array();
$t_project_rows = array();

foreach ( $t_projects as $key=>$t_project_id ) {
    $t_full_projects[] = project_get_row( $t_project_id );
    $t_query2 = 'SELECT * FROM   {project_ext}  WHERE project_id=' . db_param();
    $t_result2 = db_query( $t_query2, array( $t_project_id ) );

    $t_query3 = 'SELECT username,realname FROM  {user}  WHERE id=' . db_param();

    $t_full_projects[$key]['ext'] = '';
    $t_full_projects[$key]['req_evaluate_user_id'] ='';
    $t_full_projects[$key]['dev_evaluate_user_id'] ='';
    $t_full_projects[$key]['test_evaluate_user_id'] ='';
    $rew = db_fetch_array( $t_result2 );
    if($rew) {
        $t_full_projects[$key]['ext'] =$rew;
        //$total_hours = $rew['req_work_hours'] + $rew['dev_work_hours'] + $rew['test_work_hours'];
        $total_hours = $rew['reality_dev_work_hours'] + $rew['reality_req_work_hours'] + $rew['reality_test_work_hours'];
        $t_take_hours = project_get_take_hours($t_project_id);
        $current_hours = $t_take_hours['take_req_work_hours'] + $t_take_hours['take_dev_work_hours'] + $t_take_hours['take_test_work_hours'];
        if($total_hours == 0) $total_hours = 1; //防止除1
        $current_req_work_proc = $t_take_hours['take_req_work_hours'] / $total_hours * 100;
        $current_dev_work_proc = $t_take_hours['take_dev_work_hours'] / $total_hours * 100;
        $current_test_work_proc = $t_take_hours['take_test_work_hours'] / $total_hours * 100;
        $current_proc = $current_hours / $total_hours * 100;
        $t_project_rows[$t_full_projects[$key]['id']]['total_hours'] = $total_hours;
        $t_project_rows[$t_full_projects[$key]['id']]['reality_dev_work_hours'] = $rew['reality_dev_work_hours'];
        $t_project_rows[$t_full_projects[$key]['id']]['reality_req_work_hours'] = $rew['reality_req_work_hours'];
        $t_project_rows[$t_full_projects[$key]['id']]['reality_test_work_hours'] = $rew['reality_test_work_hours'];
        $t_project_rows[$t_full_projects[$key]['id']]['take_req_work_hours'] = $t_take_hours['take_req_work_hours'];
        $t_project_rows[$t_full_projects[$key]['id']]['take_dev_work_hours'] = $t_take_hours['take_dev_work_hours'];
        $t_project_rows[$t_full_projects[$key]['id']]['take_test_work_hours'] = $t_take_hours['take_test_work_hours'];
        $t_project_rows[$t_full_projects[$key]['id']]['current_req_work_proc'] =  number_format($current_req_work_proc,2);
        $t_project_rows[$t_full_projects[$key]['id']]['current_dev_work_proc'] =  number_format($current_dev_work_proc,2);
        $t_project_rows[$t_full_projects[$key]['id']]['current_test_work_proc'] =  number_format($current_test_work_proc,2);
        $t_project_rows[$t_full_projects[$key]['id']]['project_progress'] =  number_format($current_proc,2);
    }
}

//var_dump($t_project_rows);die;

?>
<?php


layout_page_header( lang_get( 'summary_link' ) );

layout_page_begin( 'summary_count_page.php' );

print_summary_menu( 'summary_page.php' );
print_summary_submenu();
?>
    <style>
        .process-bar{width:100%;display:inline-block;*zoom:1;}
        .pb-wrapper{border:1px none #cfd0d2;position:relative;background:#cfd0d2;border-radius: 8px;}
        .pb-container{height:12px;position:relative;left:-1px;margin-right:-2px;font:1px/0 arial;padding:1px;}
        .pb-highlight{position:absolute;left:0;top:0;_top:1px;width:100%;opacity:0.6;filter:alpha(opacity=60);height:6px;background:white;font-size:1px;line-height:0;z-index:1}
        .pb-text{width:100%;position:absolute; left:0;top:0;text-align:center;font:10px/12px arial;color:black;font:10px/12px arial}
    </style>

    <!--搜索栏-->
    <div style="margin: 15px 0 0 15px;">
        <form method="get" action="">
            <label for="select-project-id" style="margin-left: 20px;">选择项目:</label>
            <!--项目选择-->
            <select id="select-project-id" name="project_id" class="input-sm">
                <option value="0">所有项目</option>
                <?php print_project_option_list($f_project_id, false, $f_project_id, true, true) ?>
            </select>
            <script>
                $('option').removeAttr("disabled");
            </script>

            <label for="summary" style="margin-left: 20px;">报告者:</label>
            <select <?php echo helper_get_tab_index() ?> id="reporter_id" name="reporter_id" class="input-sm">
                <option value="0" selected="selected"></option>
                <?php print_assign_to_option_list($f_reporter_id) ?>
            </select>
s
            <label for="summary" style="margin-left: 20px;">报告者身份:</label>
            <select id="user-access-level" name="reporter_access_level" class="input-sm">
                <option value="" selected="selected"></option>
                <?php print_project_access_levels_option_list($f_reporter_access_level) ?>
            </select>


            <label for="summary" style="margin-left: 20px;">问题负责人:</label>
            <select <?php echo helper_get_tab_index() ?> id="handler_id" name="handler_id" class="input-sm">
                <option value="0" selected="selected"></option>
                <?php print_assign_to_option_list($f_user_id) ?>
            </select>
            <label for="summary" style="margin-left: 20px;">问题负责人身份:</label>
            <select id="user-access-level" name="handler_access_level" class="input-sm">
                <option value="" selected="selected"></option>
                <?php print_project_access_levels_option_list($f_handler_access_level) ?>
            </select>

            <br><br>

            <!-- 关键字搜索 -->
            <label for="summary" style="margin-left: 20px;">项目名称:</label>
            <input id="summary" class="input-sm" type="text" name="proj_name" value="<?php echo $f_proj_name; ?>">
            <label for="summary" style="margin-left: 20px;">问题简介:</label>
            <input id="summary" class="input-sm" type="text" name="bug_summary" value="<?php echo $f_bug_summary; ?>">
<!--            <label for="summary" style="margin-left: 20px;">工作摘要:</label>-->
<!--            <input id="summary" class="input-sm" type="text" name="work_summary" value="--><?php //echo $f_work_summary; ?><!--">-->
<!---->


            <!-- 选择月份 -->
            <label for="due_date" style="margin-left: 20px;">选择月份:</label>
            <?php echo '<input ' . helper_get_tab_index() . ' type="text" id="due_date" name="due_date" class="form-control fc-clear datetimepicker input-sm" ' .
                'data-picker-locale="' . lang_get_current_datetime_locale() .
                '" data-picker-format="' . config_get( 'datetime_picker_format_month' ) . '" ' .
                'size="20" maxlength="16" value="' . $f_due_date . '" />' ?>
            <i class="fa fa-calendar fa-xlg datetimepicker"></i>


            <button type="submit" class="btn btn-primary  btn-sm"
                    style=" margin: 0 10px 2px 25px; border-radius: 2px; vertical-align: middle;">搜索</button>
        </form>
    </div>



	<div class="col-md-12 col-xs-12">
		<a id="history"></a>
		<div class="space-10"></div>
		<div id="history" class="widget-box widget-color-blue2 <?php echo $t_block_css ?>">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<i class="ace-icon fa fa-history"></i>
                    搜索结果
				</h4>
				<div class="widget-toolbar">
					<a data-action="collapse" href="#">
						<i class="1 ace-icon fa <?php echo $t_block_icon ?> bigger-125"></i>
					</a>
				</div>
			</div>
			<div class="widget-body">
				<div class="widget-main no-padding">
					<div class="table-responsive">
						<table class="table table-bordered table-condensed table-hover table-striped">
							<thead>
							<tr>
								<th class="small-caption">
									<?php echo lang_get( 'date_modified' ) ?>
								</th>
                                <th class="small-caption">
                                    <?php echo lang_get( 'project_name' ) ?>
                                </th>
                                <th class="small-caption" style="width:100px;">
                                    <?php echo lang_get( 'total_work_hours') .'/'.lang_get( 'use_working_hours')?>
                                </th>
								<th class="small-caption">
									<?php echo lang_get( 'bug' ) ?>
								</th>
                                <th class="small-caption" style="width:50px;">
                                    报告人
                                </th>
                                <th class="small-caption" style="width:50px;">
                                    处理人
                                </th>
                                <th class="small-caption" style="min-width: 80px;">
                                    <?php echo lang_get( 'worker_hours' ) ?>
                                </th>
                                <th class="small-caption">
                                    <?php echo lang_get( 'projects_title').lang_get( 'need_working_hours').lang_get( 'proportion') ?>
                                </th>
                                <th class="small-caption">
                                    <?php echo lang_get( 'projects_title').lang_get( 'develop_working_hours').lang_get( 'proportion') ?>
                                </th>
                                <th class="small-caption">
                                    <?php echo lang_get( 'projects_title').lang_get( 'test_working_hours').lang_get( 'proportion') ?>
                                </th>
                                <th class="small-caption">
                                    <?php echo lang_get( 'project_progress' ) ?>
                                </th>
							</tr>
							</thead>

							<tbody>
							<?php
							foreach( $t_history as $t_item ) {
							    //$total_hours = $t_item['req_work_hours'] + $t_item['dev_work_hours'] + $t_item['test_work_hours'];
                                $t_take_hours = $t_project_rows[$t_item['proj_id']];
							    if($total_hours == 0) $total_hours = 1; //防止除1
//                                var_dump($t_take_hours);die;
//                                $current_hours = $t_take_hours['take_req_work_hours'] + $t_take_hours['take_dev_work_hours'] + $t_take_hours['take_test_work_hours'];
//                                $current_req_work_proc = $t_take_hours['take_req_work_hours'] / $total_hours * 100;
//                                $current_dev_work_proc = $t_take_hours['take_dev_work_hours'] / $total_hours * 100;
//                                $current_test_work_proc = $t_take_hours['take_test_work_hours'] / $total_hours * 100;
//                                $current_proc = $current_hours / $total_hours * 100;
                                $current_hours = $t_take_hours['take_req_work_hours'] + $t_take_hours['take_dev_work_hours'] + $t_take_hours['take_test_work_hours'];
                                $total_hours = $t_take_hours['total_hours'];
                                $current_req_work_proc = $t_take_hours['take_req_work_hours'];
                                $current_dev_work_proc = $t_take_hours['take_dev_work_hours'];
                                $current_test_work_proc = $t_take_hours['take_test_work_hours'];
                                $current_proc = $current_hours / $total_hours * 100;
								?>
								<tr>
									<td class="small-caption">
										<?php echo date('Y-m-d H:i:s',$t_item['date_submitted']) ?>
									</td>
                                    <td class="small-caption">
                                        <a href="view_all_bug_page.php?project_id=<?php echo $t_item['proj_id'] ?>"><?php echo $t_item['project_name'] ?></a>
                                    </td>
                                    <td>
                                        <span>
                                             <a href="manage_proj_edit_page.php?project_id=<?php echo $t_item['project_id'] ?>">
                                            <?php echo $total_hours; ?> /
                                            <?php echo $current_hours; ?></a>
                                        </span>
                                    </td>
									<td class="small-caption">
                                        <a href="view.php?id=<?php echo $t_item['bug_id'] ?>"><?php echo $t_item['summary'] ?></a>
									</td>
                                    <td class="small-caption">
                                        <?php echo $t_item['reporter_name'] ?>
                                    </td>
                                    <td class="small-caption">
                                        <?php echo $t_item['handler_name'] ?>
                                    </td>
                                    <td class="small-caption">
                                        <?php echo ($t_item['work_hours']==null ? 0 : $t_item['work_hours']); ?>
                                    </td>
<!--									<td class="small-caption">-->
<!--                                        --><?php //echo  $t_item['description']?>
<!--									</td>-->

                                    <td  style="width: 120px;" class="small-caption">
                                        <div class="process-bar skin-green">
                                            <div class="pb-wrapper">
                                                <div class="pb-highlight"></div>
                                                <div class="pb-container">
                                                    <div class="pb-text"><?php echo number_format($current_req_work_proc,2) ?>%</div>
                                                    <div class="pb-value" style="height: 100%;
                                                            width:<?php if($current_req_work_proc>100) echo 100; else echo number_format($current_req_work_proc,2) ?>%;
                                                            background:<?php if($current_req_work_proc>100) echo '#d73519'; else echo '#19d73d'?>;
                                                            border-radius: 8px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td  style="width: 120px;" class="small-caption">
                                        <div class="process-bar skin-green">
                                            <div class="pb-wrapper">
                                                <div class="pb-highlight"></div>
                                                <div class="pb-container">
                                                    <div class="pb-text"><?php echo number_format($current_dev_work_proc,2) ?>%</div>
                                                    <div class="pb-value" style="height: 100%;
                                                            width:<?php if($current_dev_work_proc>100) echo 100; else echo number_format($current_dev_work_proc,2) ?>%;
                                                            background:<?php if($current_dev_work_proc>100) echo '#d73519'; else echo '#19d73d'?>;
                                                            border-radius: 8px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="width: 120px;" class="small-caption">
                                        <div class="process-bar skin-green">
                                            <div class="pb-wrapper">
                                                <div class="pb-highlight"></div>
                                                <div class="pb-container">
                                                    <div class="pb-text"><?php echo number_format($current_test_work_proc,2) ?>%</div>
                                                    <div class="pb-value" style="height: 100%;
                                                            width: <?php if($current_test_work_proc>100) echo 100; echo number_format($current_test_work_proc,2) ?>%;
                                                            background:<?php if($current_test_work_proc>100) echo '#d73519'; else echo '#19d73d'?>;
                                                            border-radius: 8px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="width: 120px;" class="small-caption">
                                        <div class="process-bar skin-green">
                                            <div class="pb-wrapper">
                                                <div class="pb-highlight"></div>
                                                <div class="pb-container">
                                                    <div class="pb-text"><?php echo number_format($current_proc,2) ?>%</div>
                                                    <div class="pb-value" style="height:100%;
                                                            width:<?php if($current_proc>100) echo 100; else echo number_format($current_proc,2) ?>%;
                                                            background:<?php if($current_proc>100) echo '#d73519'; else echo '#19d73d'?>;
                                                            border-radius: 8px;"></div>
                                                </div>
                                            </div>
                                        </div>

                                    </td>
								</tr>
								<?php
							} # end for loop
							?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
        <div class="btn-group pull-right">
                <?php
                    print_page_links('summary_by_search_bug_page.php?proj_name='. $f_proj_name
                        .'&bug_summary='. $f_bug_summary .'&reporter_id='. $f_reporter_id .
                        '&reporter_access_level='. $f_reporter_access_level . '&handler_id='. $f_user_id .
                        '&handler_access_level='. $f_handler_access_level .'&due_date='. $f_due_date .
                        '&project_id='. $f_project_id
                        , 1, $f_total_page, $f_page_number);
                ?>
        </div>
        <?php
        ?>
	</div>



    <!-- 统计框 -->
    <div class="col-md-12 col-xs-12">
        <a id="history"></a>
        <div class="space-10"></div>
        <div id="history" class="widget-box widget-color-blue2 <?php echo $t_block_css ?>">
            <div class="widget-header widget-header-small">
                <h4 class="widget-title lighter">
                    <i class="ace-icon fa fa-history"></i>
                    项目工时统计
                    <span class="badge"> 总工时：<?php echo $f_total_hours ?></span>
                </h4>
                <div class="widget-toolbar">
                    <a data-action="collapse" href="#">
                        <i class="1 ace-icon fa <?php echo $t_block_icon ?> bigger-125"></i>
                    </a>
                </div>
            </div>
            <div class="widget-body">
                <div class="widget-main no-padding">
                    <div class="table-responsive">
                        <table class="table table-bordered table-condensed table-hover table-striped">
                            <thead>
                            <tr>
                                <th class="small-caption">
                                    <?php echo lang_get( 'project_name' ) ?>
                                </th>
                                <th class="small-caption" style="width:200px;">
                                    <?php echo lang_get( 'total_work_hours') .'/'.lang_get( 'use_working_hours')?>
                                </th>
                                <th class="small-caption" style="width: 200px;">
                                    搜索日志所占总工时
                                </th>

                                <th class="small-caption">
                                    <?php echo lang_get( 'projects_title').lang_get( 'need_working_hours').lang_get( 'proportion') ?>
                                </th>
                                <th class="small-caption">
                                    <?php echo lang_get( 'projects_title').lang_get( 'develop_working_hours').lang_get( 'proportion') ?>
                                </th>
                                <th class="small-caption">
                                    <?php echo lang_get( 'projects_title').lang_get( 'test_working_hours').lang_get( 'proportion') ?>
                                </th>
                                <th class="small-caption">
                                    <?php echo lang_get( 'project_progress' ) ?>
                                </th>
                            </tr>
                            </thead>

                            <tbody>
                            <?php
                            foreach( $f_summary_proj as $t_item ) {
                                //$total_hours = $t_item['req_work_hours'] + $t_item['dev_work_hours'] + $t_item['test_work_hours'];
                                $total_hours = $t_project_rows[$t_item['proj_id']]['reality_dev_work_hours'] + $t_project_rows[$t_item['proj_id']]['reality_req_work_hours'] + $t_project_rows[$t_item['proj_id']]['reality_test_work_hours'];
                                $t_take_hours = $t_project_rows[$t_item['proj_id']];
                                if($total_hours == 0) $total_hours = 1; //防止除1
                                $current_hours = $t_take_hours['take_req_work_hours'] + $t_take_hours['take_dev_work_hours'] + $t_take_hours['take_test_work_hours'];
                                $current_req_work_proc = $t_take_hours['take_req_work_hours'];
                                $current_dev_work_proc = $t_take_hours['take_dev_work_hours'];
                                $current_test_work_proc = $t_take_hours['take_test_work_hours'];
                                $current_proc = $current_hours / $total_hours * 100;
                                ?>
                                <tr>
                                    <td class="small-caption">
                                        <a href="view_all_bug_page.php?project_id=<?php echo $t_item['proj_id'] ?>"><?php echo $t_item['name'] ?></a>
                                    </td>
                                    <td>
                                        <span>
                                             <a href="manage_proj_edit_page.php?project_id=<?php echo $t_item['proj_id'] ?>">
                                            <?php echo lang_get( 'total_work_hours').':'.$total_hours; ?> /
                                                 <?php echo lang_get( 'use_working_hours').':'.$current_hours; ?></a>
                                        </span>
                                    </td>

                                    <td class="small-caption" >
                                        <?php echo $t_item['proj_total_hours'] ?>
                                    </td>


                                    <td  style="width: 120px;" class="small-caption">
                                        <div class="process-bar skin-green">
                                            <div class="pb-wrapper">
                                                <div class="pb-highlight"></div>
                                                <div class="pb-container">
                                                    <div class="pb-text"><?php echo number_format($current_req_work_proc,2) ?>%</div>
                                                    <div class="pb-value" style="height: 100%;
                                                            width:<?php if($current_req_work_proc>100) echo 100; else echo number_format($current_req_work_proc,2) ?>%;
                                                            background:<?php if($current_req_work_proc>100) echo '#d73519'; else echo '#19d73d'?>;
                                                            border-radius: 8px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td  style="width: 120px;" class="small-caption">
                                        <div class="process-bar skin-green">
                                            <div class="pb-wrapper">
                                                <div class="pb-highlight"></div>
                                                <div class="pb-container">
                                                    <div class="pb-text"><?php echo number_format($current_dev_work_proc,2) ?>%</div>
                                                    <div class="pb-value" style="height: 100%;
                                                            width:<?php if($current_dev_work_proc>100) echo 100; else echo number_format($current_dev_work_proc,2) ?>%;
                                                            background:<?php if($current_dev_work_proc>100) echo '#d73519'; else echo '#19d73d'?>;
                                                            border-radius: 8px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="width: 120px;" class="small-caption">
                                        <div class="process-bar skin-green">
                                            <div class="pb-wrapper">
                                                <div class="pb-highlight"></div>
                                                <div class="pb-container">
                                                    <div class="pb-text"><?php echo number_format($current_test_work_proc,2) ?>%</div>
                                                    <div class="pb-value" style="height: 100%;
                                                            width: <?php if($current_test_work_proc>100) echo 100; echo number_format($current_test_work_proc,2) ?>%;
                                                            background:<?php if($current_test_work_proc>100) echo '#d73519'; else echo '#19d73d'?>;
                                                            border-radius: 8px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="width: 120px;" class="small-caption">
                                        <div class="process-bar skin-green">
                                            <div class="pb-wrapper">
                                                <div class="pb-highlight"></div>
                                                <div class="pb-container">
                                                    <div class="pb-text"><?php echo number_format($current_proc,2) ?>%</div>
                                                    <div class="pb-value" style="height:100%;
                                                            width:<?php if($current_proc>100) echo 100; else echo number_format($current_proc,2) ?>%;
                                                            background:<?php if($current_proc>100) echo '#d73519'; else echo '#19d73d'?>;
                                                            border-radius: 8px;"></div>
                                                </div>
                                            </div>
                                        </div>

                                    </td>
                                </tr>
                                <?php
                            } # end for loop
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

<?php

layout_page_end();




