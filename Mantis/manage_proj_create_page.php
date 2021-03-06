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
 * Create a project
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses current_user_api.php
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'current_user_api.php' );
require_api( 'event_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
//require_api( 'bug_set_api.php' );
require_api('bug_template_api.php');

auth_reauthenticate();

access_ensure_global_level( config_get( 'create_project_threshold' ) );

layout_page_header();

//$t_fields = config_get( 'bug_report_page_fields' );
//
//$t_fields = columns_filter_disabled( $t_fields );

layout_page_begin( 'manage_overview_page.php' );
//$t_show_handler = in_array( 'handler', $t_fields ) && access_has_project_level( config_get( 'update_bug_assign_threshold' ) );
$f_handler_id			= gpc_get_int( 'handler_id', 0 );
print_manage_menu( 'manage_proj_page.php' );

$f_parent_id = gpc_get( 'parent_id', null );

// 获取可用的问题模版parent_id = 0的
//$model_bug_set = getModelBugSet();
$bug_template_category_list = getAllBugTemplateCategory();

?>

	<div class="col-md-12 col-xs-12">
		<div class="space-10"></div>

<?php if( project_table_empty() ) { ?>
	<div class="alert alert-sm alert-warning" role="alert">
		<i class="ace-icon fa fa-warning fa-lg"></i> <?php echo lang_get( 'create_first_project' ) ?>
	</div>
<?php } ?>


	<div id="manage-project-create-div" class="form-container">
	<form method="post" id="manage-project-create-form" action="manage_proj_create.php">
	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="ace-icon fa fa-puzzle-piece"></i>
				<?php
				if( null !== $f_parent_id ) {
					echo lang_get( 'add_subproject_title' );
				} else {
					echo lang_get( 'add_project_title' );
				} ?>
			</h4>
		</div>
		<div class="widget-body">
		<div class="widget-main no-padding">
		<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped">
		<fieldset>
			<?php
			echo form_security_field( 'manage_proj_create' );
			if( null !== $f_parent_id ) {
				$f_parent_id = (int) $f_parent_id; ?>
				<input type="hidden" name="parent_id" value="<?php echo $f_parent_id ?>" /><?php
			} ?>

			<tr>
				<td class="category">
					<span class="required">*</span> <?php echo lang_get( 'project_name' ) ?>
				</td>
				<td>
					<input type="text" id="project-name" name="name" class="input-sm" size="60" maxlength="128" required />
				</td>
			</tr>

            <!--项目编号-->
            <tr>
                <td class="category">
                    <span class="required">*</span>
                    合同编号
                </td>
                <td>
                    <input  type="text" id="proj-no" name="proj_no"maxlength="20"  required/>
                </td>
            </tr>

			<tr>
				<td class="category">
					<span class="required">*</span>
					<?php echo lang_get( 'choose_owner_user_id' ) ?>
				</td>
				<td>
					<select <?php echo helper_get_tab_index() ?> id="handler_id" name="owner_user_id" class="input-sm">
						<option value="0" selected="selected"></option>
						<?php print_assign_to_option_list( $f_handler_id,ALL_PROJECTS) ?>
					</select>
				</td>
			</tr>

            <tr>
                <td class="category">
                    <?php echo '选择问题模版' ?>
                </td>
                <td>
                    <select <?php echo helper_get_tab_index() ?> id="select-model" class="input-sm">
                        <option value="0" selected="selected"></option>
                        <?php foreach($bug_template_category_list as $bug_template_category) { ?>
                            <option name="bug_template_category_id" value="<?php echo $bug_template_category['id']; ?>"><?php echo $bug_template_category['name']; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>

            <!--一开始是隐藏的-->
            <tr id="checkTr" style="display: none;">
                <td class="category">
                    <span class="required">*</span>
                    <?php echo '创建问题' ?>
                </td>
                <td>
                    <div  class="checkbox" >
                        <div id="checkbug">
                            <!--<input type="checkbox" name="bug_ids">-->
                        </div>
                    </div>
                </td>
            </tr>

            <!-- 签订日期和上线交货日期 -->
            <tr>
                <td class="category">
                    <?php echo '签订时间' ?>
                </td>
                <td>
                    <input tabindex="1" type="text" id="sign_time" name="sign_time" class="form-control fc-clear datetimepicker input-sm"
                           data-picker-locale="zh-cn" data-picker-format="Y-MM-D" size="20" maxlength="16" >
<!--                    <input id="sign_time" name="sign_time" type="text">-->
                </td>
            </tr>

            <tr>
                <td class="category">
                    <?php echo '提交上线时间' ?>
                </td>
                <td>
                    <input tabindex="1" type="text" id="submit_time" name="submit_time" class="form-control fc-clear datetimepicker input-sm"
                           data-picker-locale="zh-cn" data-picker-format="Y-MM-D" size="20" maxlength="16" >
<!--                    <input id="submit_time" data-picker-locale="zh-cn" name="submit_time" type="text">-->
                </td>
            </tr>
            <!--导入datepickerjs-->
<!--            <script type="text/javascript" src="js/moment-with-locales-2.15.2.min.js" charset="UTF-8"></script>-->
<!--            <script type="text/javascript" src="js/bootstrap-datetimepicker-4.17.47.min.js" charset="UTF-8"></script>-->
<!--            <script>-->
<!--                $('#sign_time').datetimepicker({-->
<!--                        format: 'YYYY-MM-DD'//显示格式-->
<!--                });-->
<!--                $('#submit_time').datetimepicker({-->
<!--                    format: 'YYYY-MM-DD'-->
<!--                });-->
<!--            </script>-->

			<tr>
				<td class="category">
					<span class="required">*</span> <?php echo lang_get( 'need_working' ) ?>
				</td>
				<td>

					<div class="input-group" style="width: 200px;float: left">
						<span class="input-group-addon"><?php echo lang_get( 'need_working_hours' ) ?></span>
						<input type="number"   min="0.1" step="0.1"  id="need_working_hours_day" name="need_working_hours_day"  class="form-control">
						<span class="input-group-addon"><?php echo lang_get( 'working_hours_day' ) ?></span>
					</div>
					<label class="label label-info" style="margin-left: 20px"><?php echo lang_get( 'req_evaluate_user_id' ) ?></label>
					<select <?php echo helper_get_tab_index() ?> id="req_evaluate_user_id" name="req_evaluate_user_id" class="input-sm">
						<option value="0" selected="selected"></option>
						<?php print_assign_to_option_list( $f_handler_id,ALL_PROJECTS) ?>
					</select>

				</td>
			</tr>
			<tr>
				<td class="category">
					<span class="required">*</span> <?php echo lang_get( 'develop_working' ) ?>
				</td>
				<td>

					<div class="input-group" style="width: 200px;float: left">
						<span class="input-group-addon"><?php echo lang_get( 'develop_working_hours' ) ?></span>
						<input type="number"   min="0.1" step="0.1"  id="develop_working_hours_day" name="develop_working_hours_day"  class="form-control">
						<span class="input-group-addon"><?php echo lang_get( 'working_hours_day' ) ?></span>
					</div>
					<label class="label label-info" style="margin-left: 20px"><?php echo lang_get( 'dev_evaluate_user_id' ) ?></label>
					<select <?php echo helper_get_tab_index() ?> id="dev_evaluate_user_id" name="dev_evaluate_user_id" class="input-sm">
						<option value="0" selected="selected"></option>
						<?php print_assign_to_option_list( $f_handler_id,ALL_PROJECTS) ?>
					</select>

				</td>
			</tr>
			<tr>
				<td class="category">
					<span class="required">*</span> <?php echo lang_get( 'test_working' ) ?>
				</td>
				<td>

					<div class="input-group" style="width: 200px;float: left">
						<span class="input-group-addon"><?php echo lang_get( 'test_working_hours' ) ?></span>
						<input type="number" min="0.1" step="0.1"  id="test_working_hours_day" name="test_working_hours_day"  class="form-control">
						<span class="input-group-addon"><?php echo lang_get( 'working_hours_day' ) ?></span>
					</div>
					<label class="label label-info" style="margin-left: 20px"><?php echo lang_get( 'test_evaluate_user_id' ) ?></label>
					<select <?php echo helper_get_tab_index() ?> id="test_evaluate_user_id" name="test_evaluate_user_id" class="input-sm">
						<option value="0" selected="selected"></option>
						<?php print_assign_to_option_list( $f_handler_id,ALL_PROJECTS) ?>
					</select>

				</td>
			</tr>

            <tr>
                <td class="category">
                    完成奖金
                </td>
                <td>
                    <div class="bonus-total-box">
                        <div class="bonus-box" style="margin: 5px 10px;">
                            <label class="label label-info" >完成时间</label>
                            <input tabindex="1" type="text" id="due_date1" name="deadline[]" class="form-control fc-clear datetimepicker input-sm"
                                   data-picker-locale="zh-cn" data-picker-format="Y-MM-D" size="20" maxlength="16" style="" >
                            <label class="label label-info" >完成奖金</label>
                            <input type="number" name="bonus[]" min="0" >
                            <button class="btn btn-info btn-xs del-bonus" style="margin-left:10px;padding: 2px 8px;border-radius: 5px;">删除</button>
                            <button class="btn btn-info btn-xs add-bonus" style="margin-left:10px;padding: 2px 8px;border-radius: 5px;">添加</button>
                        </div>
                    </div>
                </td>
            </tr>




            <tr>
				<td class="category">
					<?php echo lang_get( 'status' ) ?>
				</td>
				<td>
					<select id="project-status" name="status" class="input-sm">
						<?php print_enum_string_option_list( 'project_status' ) ?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'inherit_global' ) ?>
				</td>
				<td>
					<label>
						<input type="checkbox" class="ace" id="project-inherit-global" name="inherit_global" checked="checked">
						<span class="lbl"></span>
					</label>
				</td>
			</tr>
			<?php if( !is_null( $f_parent_id ) ) { ?>
				<tr>
					<td class="category">
						<?php echo lang_get( 'inherit_parent' ) ?>
					</td>
					<td>
						<label>
							<input type="checkbox" class="ace" id="project-inherit-parent" name="inherit_parent" checked="checked">
							<span class="lbl"></span>
						</label>
					</td>
				</tr>
			<?php
			} ?>

			<tr>
				<td class="category">
					<?php echo lang_get( 'view_status' ) ?>
				</td>
				<td>
					<select id="project-view-state" name="view_state" class="input-sm">
						<?php print_enum_string_option_list( 'view_state', config_get( 'default_project_view_status', null, ALL_USERS, ALL_PROJECTS ) ) ?>
					</select>
				</td>
			</tr>

			<?php

			$g_project_override = ALL_PROJECTS;
			if( file_is_uploading_enabled() && DATABASE !== config_get( 'file_upload_method' ) ) {
				$t_file_path = '';
				# Don't reveal the absolute path to non-administrators for security reasons
				if( current_user_is_administrator() ) {
					$t_file_path = config_get_global( 'absolute_path_default_upload_folder' );
				}
				?>
				<tr>
					<td class="category">
						<?php echo lang_get( 'upload_file_path' ) ?>
					</td>
					<td>
						<input type="text" id="project-file-path" name="file_path" class="input-sm" size="60" maxlength="250" value="<?php echo $t_file_path ?>" />
					</td>
				</tr>
			<?php
			} ?>

			<tr>
				<td class="category">
					<?php echo lang_get( 'description' ) ?>
				</td>
				<td>
					<textarea class="form-control" id="project-description" name="description" cols="70" rows="5"></textarea>
				</td>
			</tr>

			<?php event_signal( 'EVENT_MANAGE_PROJECT_CREATE_FORM' ) ?>
		</fieldset>
		</table>
		</div>
		</div>
		</div>
		<div class="widget-toolbox padding-8 clearfix">
			<span class="required pull-right"> * <?php echo lang_get( 'required' ) ?></span>
			<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'add_project_button' ) ?>" />
		</div>
	</div>
	</div>
	</form>
</div>
<!--    <input type="checkbox"  id="project-inherit-parent" name="inherit_parent" checked="checked">-->
    <script>
        $('#select-model').change(function() {
            // alert($('#select-model').val());
            // 发送Ajax获取对应的问题列表
            $.get('ajax_handle_bug_template.php?type=getBugTemplateList&bug_template_category_id='+$('#select-model').val(),
                function(data, status) {
                $('#checkbug').empty();

                if(data == '') {
                    $('#checkTr').hide();
                    return;
                }
                rs = eval('('+data+')');
                $('#checkbug').empty();

                $('#checkTr').show();
                $.each(rs, function(i){
                    /* 插入问题多选选项 */
                    html = '<div style="margin: 0 10px 10px 10px;"><input style="margin-left: 3.5px;margin-bottom: 1.5px" checked="checked" id="bug_ids_sel'+ rs[i].id
                        +'" type="checkbox" class="ace" name="bug_template_ids[]" value="'+ rs[i].id +'">'+ '<span class="lbl"></span>'
                        +"<label style='margin-left: -10px;'>"
                        +"<a href='template_update_page.php?template_id=" + rs[i].id + "' target='blank' style=' text-decoration:none;  color:#000;'>"
                        + rs[i].summary + "</a></label></div>";
                    $('#checkbug').append(html);

                });

            });
        });

        /* 关闭浏览器时候删除所有临时生成的模版 */
        // window.onbeforeunload = function(event){
        //        //     return '您可能有数据没有保存';
        //        // };

        /*结束时保存设备状态*/
        // window.onbeforeunload=function(){//必须使用beforeunload
        //     var url ="ajax_handle_bug_set.php?type=clearUserBugModel";
        //     $.ajax({
        //         url:url,
        //         async:false                //必须采用同步方法
        //     });
        // }

        /*关闭浏览器前执行清除草稿操作*/
        // window.onbeforeunload = window.onbeforeunload || function(e) {
        //     $.ajax({
        //         type: "GET",
        //         async: false, //异步执行设置为 false 否则浏览器关闭，不会执行
        //         url: 'ajax_handle_bug_set.php?type=clearUserBugModel',
        //     });
        // }
        // $("form").on("submit", function() {
        //     window.onbeforeunload = null;
        // });
        function initBonusBtn() {
            $('.bonus-box>.del-bonus').on("click", function () {
                var node = $(this);
                delBonusBox(node);
                return false;
            });

            $('.bonus-box>.add-bonus').on("click",function(){
                var node = $(this);
                addBonusBox(node);
                return false;
            });
        }
        initBonusBtn();
        function delBonusBox(node) {
            var parent = node.parent();
            // 判断是否至少有一个奖金输入
            if(parent.parent().children().length <= 1) {
                return false;
            }
            parent.remove();
            return false;
        }

        function addBonusBox(node) {
            var parent = node.parent();
            var copy = parent.clone();
            parent.parent().append(copy);
            copy.children(".del-bonus").on("click", function () {
                var node = $(this);
                delBonusBox(node);
                return false;
            });
            copy.children(".add-bonus").on("click", function () {
                var node = $(this);
                addBonusBox(node);
                return false;
            });
            $("input.datetimepicker").datetimepicker({
                'format': 'Y-MM-D'
            })
        }

    </script>

<?php
layout_page_end();
