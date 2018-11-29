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

//form_security_validate( 'user_work_log' );
$f_type =   gpc_get_string( 'type', '' );
db_param_push();
$f_user_id = gpc_get_int( 'user_id', 0 );
$startTime = gpc_get_int( 'start', strtotime(date('Ymd00:00:00')));
$endTime = gpc_get_int( 'end',  time());
// 检查今天的总工时  最高8个工时
if($f_type=='all') {
	$sql = "select uwl.*,b.summary,p.name,pe.req_work_hours,pe.dev_work_hours,pe.test_work_hours,pe.reality_dev_work_hours,pe.reality_req_work_hours,pe.reality_test_work_hours from {user_work_log}  as uwl ".
         "left join {bug} as b on b.id =uwl.task_bug_id left join {project} as p on p.id = uwl.project_id ".
        " left join {project_ext} as pe on pe.project_id = uwl.project_id where uwl.user_id = " . db_param();
}else{
	$sql = "select uwl.*,b.summary,p.name,pe.req_work_hours,pe.dev_work_hours,pe.test_work_hours,pe.reality_dev_work_hours,pe.reality_req_work_hours,pe.reality_test_work_hours from {user_work_log}  as uwl ".
        "left join {bug} as b on b.id =uwl.task_bug_id left join {project} as p on p.id = uwl.project_id ".
        " left join {project_ext} as pe on pe.project_id = uwl.project_id where uwl.user_id = " . db_param() . "  and uwl.add_time>={$startTime} and uwl.add_time<={$endTime}";
}
$t_result = db_query($sql,[$f_user_id]);
$t_history = [];
while($t_row = db_fetch_array($t_result)){
	$t_history[] = $t_row;
}
//项目的已花费标准需求工时
$t_query = "SELECT project_id,SUM(reality_work_hours) AS take_req_work_hours FROM {user_work_log} WHERE user_id=" . db_param() . " AND work_type=1 GROUP BY project_id";
$t_result = db_query( $t_query, array( (int)$f_user_id ) );
$t_project_rows = array();
while( $t_row = db_fetch_array( $t_result ) ) {
    $t_project_rows[$t_row['project_id']]['take_req_work_hours'] = $t_row['take_req_work_hours'];
    $t_project_rows[$t_row['project_id']]['take_dev_work_hours'] = 0.0;
    $t_project_rows[$t_row['project_id']]['take_test_work_hours'] = 0.0;
}
//项目的已花费标准开发工时
$t_query = "SELECT project_id,SUM(reality_work_hours) AS take_dev_work_hours FROM {user_work_log} WHERE user_id=" . db_param() . " AND work_type=2 GROUP BY project_id";
$t_result = db_query( $t_query, array( (int)$f_user_id ) );
while( $t_row = db_fetch_array( $t_result ) ) {
    $t_project_rows[$t_row['project_id']]['take_dev_work_hours'] = $t_row['take_dev_work_hours'];
    if(!isset($t_project_rows[$t_row['project_id']]['take_req_work_hours'])){
        $t_project_rows[$t_row['project_id']]['take_req_work_hours'] = 0.0;
        $t_project_rows[$t_row['project_id']]['take_test_work_hours'] = 0.0;
    }
}
//项目的已花费标准测试工时
$t_query = "SELECT project_id,SUM(reality_work_hours) AS take_test_work_hours FROM {user_work_log} WHERE user_id=" . db_param() . " AND work_type=3 GROUP BY project_id";
$t_result = db_query( $t_query, array( (int)$f_user_id ) );
while( $t_row = db_fetch_array( $t_result ) ) {
    $t_project_rows[$t_row['project_id']]['take_test_work_hours'] = $t_row['take_test_work_hours'];
    if(!isset($t_project_rows[$t_row['project_id']]['take_req_work_hours'])){
        $t_project_rows[$t_row['project_id']]['take_req_work_hours'] = 0.0;
        $t_project_rows[$t_row['project_id']]['take_dev_work_hours'] = 0.0;
    }
}
//var_dump($t_history);die;

?>
<?php
layout_page_header( lang_get( 'manage_users_link' ) );

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_user_page.php' );
?>
    <style>
        .process-bar{width:100%;display:inline-block;*zoom:1;}
        .pb-wrapper{border:1px none #cfd0d2;position:relative;background:#cfd0d2;border-radius: 8px;}
        .pb-container{height:12px;position:relative;left:-1px;margin-right:-2px;font:1px/0 arial;padding:1px;}
        .pb-highlight{position:absolute;left:0;top:0;_top:1px;width:100%;opacity:0.6;filter:alpha(opacity=60);height:6px;background:white;font-size:1px;line-height:0;z-index:1}
        .pb-text{width:100%;position:absolute; left:0;top:0;text-align:center;font:10px/12px arial;color:black;font:10px/12px arial}
    </style>
	<div class="col-md-12 col-xs-12">
		<a id="history"></a>
		<div class="space-10"></div>
		<div id="history" class="widget-box widget-color-blue2 <?php echo $t_block_css ?>">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<i class="ace-icon fa fa-history"></i>
					<?php echo lang_get( 'bug_history' ) ?>
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
                                <th class="small-caption" style="width:200px;">
                                    <?php echo lang_get( 'total_work_hours') .'/'.lang_get( 'use_working_hours')?>
                                </th>
								<th class="small-caption">
									<?php echo lang_get( 'bug' ) ?>
								</th>
                                <th class="small-caption">
                                    <?php echo lang_get( 'worker_hours' ) ?>
                                </th>
								<th class="small-caption">
									<?php echo lang_get( 'work_summary' ) ?>
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
                                $total_hours = $t_item['reality_dev_work_hours'] + $t_item['reality_req_work_hours'] + $t_item['reality_test_work_hours'];
                                $t_take_hours = $t_project_rows[$t_item['project_id']];
                                $current_hours = $t_take_hours['take_req_work_hours'] + $t_take_hours['take_dev_work_hours'] + $t_take_hours['take_test_work_hours'];
							    if($total_hours == 0) $total_hours = 1; //防止除1
                                $current_req_work_proc = $t_take_hours['take_req_work_hours'] / $total_hours * 100;
                                $current_dev_work_proc = $t_take_hours['take_dev_work_hours'] / $total_hours * 100;
                                $current_test_work_proc = $t_take_hours['take_test_work_hours'] / $total_hours * 100;
                                $current_proc = $current_hours / $total_hours * 100;
								?>
								<tr>
									<td class="small-caption">
										<?php echo date('Y-m-d H:i:s',$t_item['add_time']) ?>
									</td>
                                    <td class="small-caption">
                                        <a href="view_all_bug_page.php?project_id=<?php echo $t_item['project_id'] ?>"><?php echo $t_item['name'] ?></a>
                                    </td>
                                    <td>
                                        <span>
                                             <a href="manage_proj_edit_page.php?project_id=<?php echo $t_item['project_id'] ?>">
                                            <?php echo lang_get( 'total_work_hours').':'.$total_hours; ?> /
                                            <?php echo lang_get( 'use_working_hours').':'.$current_hours; ?></a>
                                        </span>
                                    </td>
									<td class="small-caption">
                                        <a href="view.php?id=<?php echo $t_item['task_bug_id'] ?>"><?php echo $t_item['summary'] ?></a>
									</td>
                                    <td class="small-caption">
                                        <?php echo $t_item['work_hours'] ?>
                                    </td>
									<td class="small-caption">
                                        <?php echo  $t_item['description']?>
									</td>
                                    <td class="small-caption">
                                        <div class="process-bar skin-green">
                                            <div class="pb-wrapper">
                                                <div class="pb-highlight"></div>
                                                <div class="pb-container">
                                                    <div class="pb-text"><?php echo number_format($current_req_work_proc,2) ?>%</div>
                                                    <div class="pb-value" style="height: 100%;
                                                            width: <?php if($current_req_work_proc>100) echo 100; echo number_format($current_req_work_proc,2) ?>%;
                                                            background:<?php if($current_req_work_proc>100) echo '#d73519'; else echo '#19d73d'?>;
                                                            border-radius: 8px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="small-caption">
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
                                    <td class="small-caption">
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
                                    <td class="small-caption">
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




