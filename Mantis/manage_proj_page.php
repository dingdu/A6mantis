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
 * Project Page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses category_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses icon_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses project_hierarchy_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'category_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'icon_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'project_hierarchy_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );
require_api( 'pager_api.php' );

access_ensure_global_level( config_get( 'tag_edit_threshold' ) );

auth_reauthenticate();

$f_sort	= gpc_get_string( 'sort', 'name' );
$f_dir	= gpc_get_string( 'dir', 'ASC' );
$f_proj_name = gpc_get_string('proj_name', '');
$f_proj_no = gpc_get_string('proj_no', '');

# 搜索条件数组
$search_condition = array(
    'proj_name' => $f_proj_name,
    'proj_no' => $f_proj_no,
);

if( 'ASC' == $f_dir ) {
	$t_direction = ASCENDING;
} else {
	$t_direction = DESCENDING;
}

layout_page_header( lang_get( 'manage_projects_link' ) );

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_proj_page.php' );

# Project Menu Form BEGIN
?>
<style>
	.table tbody tr td{
		vertical-align: middle;
	}
</style>
<style>
    .process-bar{width:75%;display:inline-block;*zoom:1;}
    .pb-wrapper{border:1px none #cfd0d2;position:relative;background:#cfd0d2;border-radius: 8px;}
    .pb-container{height:12px;position:relative;left:-1px;margin-right:-2px;font:1px/0 arial;padding:1px;}
    .pb-highlight{position:absolute;left:0;top:0;_top:1px;width:100%;opacity:0.6;filter:alpha(opacity=60);height:6px;background:white;font-size:1px;line-height:0;z-index:1}
    .pb-text{width:100%;position:absolute; left:0;top:0;text-align:center;font:10px/12px arial;color:black;font:10px/12px arial}
    span.sub-text{ font-size:10px;}
</style>

<!--搜索栏-->
<div style="margin: 15px 0 0 15px;">
    <form method="post" action="manage_proj_page.php?sort=<?php echo $f_sort ?>&dir=<?php echo $f_dir ?>">


        <!-- 关键字搜索 -->
        <label for="summary" style="margin-left: 20px;">项目名称:</label>
        <input id="summary" type="text" name="proj_name" value="<?php echo $f_proj_name; ?>">
        <label for="summary" style="margin-left: 20px;">项目编号:</label>
        <input id="summary" type="text" name="proj_no" value="<?php echo $f_proj_no; ?>">
        <button type="submit" class="btn btn-primary  btn-sm"
                style=" margin: 0 10px 2px 15px; border-radius: 2px; vertical-align: middle;">搜索</button>
    </form>
</div>

<div class="col-md-12 col-xs-12">
    <div class="space-10"></div>
	<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<i class="ace-icon fa fa-puzzle-piece"></i>
			<?php echo lang_get( 'projects_title' ) ?>
		</h4>
	</div>

	<div class="widget-body">
	<div class="widget-main no-padding">
	<div class="widget-toolbox padding-8 clearfix">
		<?php
		# Check the user's global access level before allowing project creation
		if( access_has_global_level ( config_get( 'create_project_threshold' ) ) ) {
			print_form_button( 'manage_proj_create_page.php', lang_get( 'create_new_project_link' ), null, null, 'btn btn-primary btn-white btn-round' );
		} ?>
	</div>
	<div class="table-responsive">
	<table class="table table-striped table-bordered table-condensed table-hover">
		<thead>
			<tr>
                <th><?php
                    print_manage_project_sort_link( 'manage_proj_page.php', 编号, 'proj_no', $t_direction, $f_sort );
                    print_sort_icon( $t_direction, $f_sort, 'proj_no' ); ?>
                </th>
				<th><?php
					print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'name' ), 'name', $t_direction, $f_sort );
					print_sort_icon( $t_direction, $f_sort, 'name' ); ?>
				</th>
				<th><?php
					print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'status' ), 'status', $t_direction, $f_sort );
					print_sort_icon( $t_direction, $f_sort, 'status' ); ?>
				</th>
				<th><?php
					print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'enabled' ), 'enabled', $t_direction, $f_sort );
					print_sort_icon( $t_direction, $f_sort, 'enabled' ); ?>
				</th>
				<th><?php
					print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'view_status' ), 'view_state', $t_direction, $f_sort );
					print_sort_icon( $t_direction, $f_sort, 'view_state' ); ?>
				</th>
				<th><?php
					print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'projects_fuzr' ), 'projects_fuzr', $t_direction, $f_sort );
					print_sort_icon( $t_direction, $f_sort, 'projects_fuzr' ); ?>
				</th>
				<th><?php
					print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'need_working').lang_get( 'evaluate_user').'/'.lang_get( 'worker_hours'), 'need_working_user_id', $t_direction, $f_sort );
					print_sort_icon( $t_direction, $f_sort, 'need_working_user_id' ); ?>
				</th>
				<th><?php
					print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'develop_working').lang_get( 'evaluate_user').'/'.lang_get( 'worker_hours'), 'develop_working_user_id', $t_direction, $f_sort );
					print_sort_icon( $t_direction, $f_sort, 'develop_working_user_id' ); ?>
				</th>
				<th><?php
					print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'test_working' ).lang_get( 'evaluate_user').'/'.lang_get( 'worker_hours'), 'test_working_user_id', $t_direction, $f_sort );
					print_sort_icon( $t_direction, $f_sort, 'test_working_user_id' ); ?>
				</th>
				<th><?php
                    print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'total_work_hours').'/'.lang_get( 'use_working_hours' ), 'use_working_hours', $t_direction, $f_sort );
                    print_sort_icon( $t_direction, $f_sort, 'use_working_hours' ); ?>
                </th>
                <th><?php
                    print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'project_progress' ), 'project_progress', $t_direction, $f_sort );
                    print_sort_icon( $t_direction, $f_sort, 'project_progress' ); ?>
                </th>
                <th style="color: #337ab7;">
                    <?php
                    echo  lang_get( 'projects_title').lang_get( 'worker_hours').lang_get( 'proportion');
                    ?>
                </th>
				<th><?php
					print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'description' ), 'description', $t_direction, $f_sort );
					print_sort_icon( $t_direction, $f_sort, 'description' ); ?>
				</th>
			</tr>
		</thead>

		<tbody>
<?php
		$t_manage_project_threshold = config_get( 'manage_project_threshold' );
		// 获取所有可用项目的id
		$t_projects = user_get_accessible_projects( auth_get_current_user_id(), true , $search_condition);
		$t_full_projects = array();
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

                //排序用到
                $t_full_projects[$key]['need_working_user_id'] = $rew['req_evaluate_user_id'];
                $t_full_projects[$key]['develop_working_user_id'] = $rew['dev_evaluate_user_id'];
                $t_full_projects[$key]['test_working_user_id'] = $rew['test_evaluate_user_id'];
                $t_full_projects[$key]['total_hours'] = $total_hours;
                $t_full_projects[$key]['use_working_hours'] = $current_hours;
                $t_full_projects[$key]['current_req_work_proc'] =  number_format($current_req_work_proc,2);
                $t_full_projects[$key]['current_dev_work_proc'] =  number_format($current_dev_work_proc,2);
                $t_full_projects[$key]['current_test_work_proc'] =  number_format($current_test_work_proc,2);
                $t_full_projects[$key]['project_progress'] =  number_format($current_proc,2);
				if($t_full_projects[$key]['ext']['owner_user_id']>0){
					$t_result2 = db_query( $t_query3, array( $t_full_projects[$key]['ext']['owner_user_id'] ) );
					$t_full_projects[$key]['owner_user_id'] =  db_fetch_array($t_result2);
				}
				if($t_full_projects[$key]['ext']['req_evaluate_user_id']>0){
					$t_result2 = db_query( $t_query3, array( $t_full_projects[$key]['ext']['req_evaluate_user_id'] ) );
					$t_full_projects[$key]['req_evaluate_user_id'] =  db_fetch_array($t_result2);
				}
				if($t_full_projects[$key]['ext']['dev_evaluate_user_id']>0){
					$t_result2 = db_query( $t_query3, array( $t_full_projects[$key]['ext']['dev_evaluate_user_id'] ) );
					$t_full_projects[$key]['dev_evaluate_user_id'] =  db_fetch_array($t_result2);
				}
				if($t_full_projects[$key]['ext']['test_evaluate_user_id']>0){
					$t_result2 = db_query( $t_query3, array( $t_full_projects[$key]['ext']['test_evaluate_user_id'] ) );
					$t_full_projects[$key]['test_evaluate_user_id'] =  db_fetch_array($t_result2);
				}
			}
		}
		$t_projects = multi_sort( $t_full_projects, $f_sort, $t_direction );
		$t_stack = array( $t_projects );

		while( 0 < count( $t_stack ) ) {
			$t_projects = array_shift( $t_stack );
			if( 0 == count( $t_projects ) ) {
				continue;
			}

            $t_project = array_shift( $t_projects );
			$t_project_id = $t_project['id'];
			$t_level      = count( $t_stack );
//            var_dump($t_project);die;

            # only print row if user has project management privileges
			if( access_has_project_level( $t_manage_project_threshold, $t_project_id, auth_get_current_user_id() ) ) { ?>
			<tr>
                <td><?php echo $t_project['proj_no']?></td>
                <td>
					<a href="manage_proj_edit_page.php?project_id=<?php echo $t_project['id'] ?>"><?php echo str_repeat( "&raquo; ", $t_level ) . string_display( $t_project['name'] ) ?></a>
				</td>
				<td><?php echo get_enum_element( 'project_status', $t_project['status'] ) ?></td>
				<td class="center"><?php echo trans_bool( $t_project['enabled'] ) ?></td>
				<td><?php echo get_enum_element( 'project_view_state', $t_project['view_state'] ) ?></td>
				<td>
					<span class="text-info"><a href ="/view_user_page.php?user_id=<?php echo $t_project['ext']['owner_user_id'] ?>"><?php echo $t_project['req_evaluate_user_id']['username'].'('.$t_project['req_evaluate_user_id']['realname'].')' ?></a></span>
				</td>
				<td>
					<span class="text-info"><a href ="/view_user_page.php?user_id=<?php echo $t_project['ext']['req_evaluate_user_id'] ?>"><?php echo $t_project['req_evaluate_user_id']['username'].'('.$t_project['req_evaluate_user_id']['realname'].')' ?></a></span>
                    <br><span class="text-info"><?php echo $t_project['ext']['req_work_hours'] ?></span>
					<?php echo lang_get( 'working_hours_day' ) ; ?>
				</td>
				<td>
					<span class="text-info"><a href ="/view_user_page.php?user_id=<?php echo $t_project['ext']['dev_evaluate_user_id'] ?>"><?php echo $t_project['dev_evaluate_user_id']['username'].'('.$t_project['dev_evaluate_user_id']['realname'].')' ?></a></span>
                    <br><span class="text-info"><?php echo $t_project['ext']['dev_work_hours'] ?></span>
					<?php echo lang_get( 'working_hours_day' ) ; ?>
				</td>
				<td>
					<span class="text-info"><a href ="/view_user_page.php?user_id=<?php echo $t_project['ext']['test_evaluate_user_id'] ?>"><?php echo $t_project['test_evaluate_user_id']['username'].'('.$t_project['test_evaluate_user_id']['realname'].')' ?></a></span>
                    <br><span class="text-info"><?php echo  $t_project['ext']['test_work_hours']  ?></span>
					<?php echo lang_get( 'working_hours_day' ) ; ?>
				</td>
				<td>
					<span class="text-info">
                        <?php echo lang_get( 'total_work_hours').':'.$t_project['total_hours'] ?><br>
                        <?php echo lang_get( 'use_working_hours').':'.$t_project['use_working_hours'] ?></span>
				</td>
                <td style="width:100px;">
                    <div class="process-bar skin-green" style="width:100%">
                        <div class="pb-wrapper">
                            <div class="pb-highlight"></div>
                            <div class="pb-container">
                                <div class="pb-text"><?php echo $t_project['project_progress'] ?>%</div>
                                <div class="pb-value" style="height: 100%;
                                        width:<?php if($t_project['project_progress']>100) echo 100; else echo $t_project['project_progress']; ?>%;
                                        background:<?php if($t_project['project_progress']>100) echo '#d73519'; else echo '#19d73d'?>;
                                        border-radius: 8px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
                <td  style="width:140px;">
                    <span class="sub-text"><?php echo lang_get( 'need_working') ?></span>
                    <div class="process-bar skin-green">
                        <div class="pb-wrapper">
                            <div class="pb-highlight"></div>
                            <div class="pb-container">
                                <div class="pb-text"><?php echo $t_project['current_req_work_proc'] ?>%</div>
                                <div class="pb-value" style="height: 100%;
                                        width:<?php if($t_project['current_req_work_proc']>100) echo 100; else echo $t_project['current_req_work_proc']; ?>%;
                                        background:<?php if($t_project['current_req_work_proc']>100) echo '#d73519'; else echo '#19d73d'?>;
                                        border-radius: 8px;">
                                </div>
                            </div>
                        </div>
                    </div><br/>
                    <span class="sub-text"><?php echo lang_get( 'develop_working') ?></span>
                    <div class="process-bar skin-green">
                        <div class="pb-wrapper">
                            <div class="pb-highlight"></div>
                            <div class="pb-container">
                                <div class="pb-text"><?php echo $t_project['current_dev_work_proc'] ?>%</div>
                                <div class="pb-value" style="height: 100%;
                                        width:<?php if($t_project['current_dev_work_proc']>100) echo 100; else echo $t_project['current_dev_work_proc']; ?>%;
                                        background:<?php if($t_project['current_dev_work_proc']>100) echo '#d73519'; else echo '#19d73d'?>;
                                        border-radius: 8px;">
                                </div>
                            </div>
                        </div>
                    </div><br/>
                    <span class="sub-text"><?php echo lang_get( 'test_working') ?></span>
                    <div class="process-bar skin-green">
                        <div class="pb-wrapper">
                            <div class="pb-highlight"></div>
                            <div class="pb-container">
                                <div class="pb-text"><?php echo $t_project['current_test_work_proc'] ?>%</div>
                                <div class="pb-value" style="height: 100%;
                                        width:<?php if($t_project['current_test_work_proc']>100) echo 100; else echo $t_project['current_test_work_proc']; ?>%;
                                        background:<?php if($t_project['current_test_work_proc']>100) echo '#d73519'; else echo '#19d73d'?>;
                                        border-radius: 8px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
				<td>
					<?php echo string_display_links( $t_project['description'] ) ?>
				</td>
			</tr><?php
			}
			$t_subprojects = project_hierarchy_get_subprojects( $t_project_id, true );

			if( 0 < count( $t_projects ) || 0 < count( $t_subprojects ) ) {
				array_unshift( $t_stack, $t_projects );
			}

			if( 0 < count( $t_subprojects ) ) {
				$t_full_projects = array();
				foreach ( $t_subprojects as $t_project_id ) {
					$t_full_projects[] = project_get_row( $t_project_id );
				}
				$t_subprojects = multi_sort( $t_full_projects, $f_sort, $t_direction );
				array_unshift( $t_stack, $t_subprojects );
			}
		} ?>
		</tbody>

	</table>
</div>
	</div>
	</div>
<!--		分页-->
<!--		--><?php //$page = pagination(1, 20, 20);
//		echo $page['html'];
//		?>
	</div>

	<div class="space-10"></div>

	<div id="categories" class="form-container">

	<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<i class="ace-icon fa fa-sitemap"></i>
			<?php echo lang_get( 'global_categories' ) ?>
		</h4>
	</div>
	<div class="widget-body">
		<div class="widget-main no-padding">
		<div class="table-responsive">
		<table class="table table-striped table-bordered table-condensed table-hover">
<?php
		$t_categories = category_get_all_rows( ALL_PROJECTS );
		$t_can_update_global_cat = access_has_global_level( config_get( 'manage_site_threshold' ) );

		if( count( $t_categories ) > 0 ) {
?>
		<thead>
			<tr>
				<td><?php echo lang_get( 'category' ) ?></td>
				<td><?php echo lang_get( 'assign_to' ) ?></td>
				<?php if( $t_can_update_global_cat ) { ?>
				<td class="center"><?php echo lang_get( 'actions' ) ?></td>
				<?php } ?>
			</tr>
		</thead>

		<tbody>
<?php
			foreach( $t_categories as $t_category ) {
				$t_id = $t_category['id'];
?>
			<tr>
				<td><?php echo string_display( category_full_name( $t_id, false ) )  ?></td>
				<td><?php echo prepare_user_name( $t_category['user_id'] ) ?></td>
				<?php if( $t_can_update_global_cat ) { ?>
				<td class="center">
<?php
					$t_id = urlencode( $t_id );
					$t_project_id = urlencode( ALL_PROJECTS );
					echo '<div class="btn-group inline">';
					echo '<div class="pull-left">';
					print_form_button( "manage_proj_cat_edit_page.php?id=$t_id&project_id=$t_project_id", lang_get( 'edit_link' ) );
					echo '</div>';
					echo '<div class="pull-left">';
					print_form_button( "manage_proj_cat_delete.php?id=$t_id&project_id=$t_project_id", lang_get( 'delete_link' ) );
					echo '</div>';
?>
				</td>
			<?php } ?>
			</tr>
<?php
			} # end for loop
?>
		</tbody>
<?php
		} # end if
?>
	</table>

	</div>
	</div>

<?php if( $t_can_update_global_cat ) { ?>
	<form method="post" action="manage_proj_cat_add.php" class="form-inline">
		<div class="widget-toolbox padding-8 clearfix">
			<?php echo form_security_field( 'manage_proj_cat_add' ) ?>
			<input type="hidden" name="project_id" value="<?php echo ALL_PROJECTS ?>" />
			<input type="text" name="name" class="input-sm" size="32" maxlength="128" />
			<input type="submit" class="btn btn-primary btn-sm btn-white btn-round" value="<?php echo lang_get( 'add_category_button' ) ?>" />
			<input type="submit" name="add_and_edit_category" class="btn btn-primary btn-sm btn-white btn-round" value="<?php echo lang_get( 'add_and_edit_category_button' ) ?>" />
		</div>
	</form>
<?php } ?>
</div>
</div>
</div>
<?php
echo '</div>';
layout_page_end();
