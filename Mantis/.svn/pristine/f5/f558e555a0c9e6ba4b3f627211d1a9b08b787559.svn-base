<?php

/**
 * 修改问题模版
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'prepare_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'tag_api.php' );
require_api( 'user_api.php' );
require_api( 'bug_template_api.php' );

$f_template_id = gpc_get_int( 'template_id' );
$t_template_row = getTemplateById($f_template_id);

$t_summary = $t_template_row['summary'];
$t_description = $t_template_row['description'];

if( !( access_has_global_level( config_get( 'tag_edit_threshold' ) )
	|| ( auth_get_current_user_id() == $t_tag_row['user_id'] )
		&& access_has_global_level( config_get( 'tag_edit_own_threshold' ) ) ) ) {
	access_denied();
}

layout_page_header( sprintf( lang_get( 'tag_update' ), $t_name ) );

layout_page_begin();
?>
<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<form method="post" action="template_update.php">
	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="ace-icon fa fa-tag"></i>
				更新问题模版：<?php echo $t_summary ?>
			</h4>
		</div>
		<div class="widget-body">
		<div class="widget-main no-padding">

		<div class="form-container">
		<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped">
		<fieldset>
			<input type="hidden" name="template_id" value="<?php echo $f_template_id ?>"/>
			<?php echo form_security_field( 'template_update' ) ?>
			<tr>
				<td class="category">
					问题模版编号
				</td>
				<td><?php echo str_pad($t_template_row['id'], 3, '0', STR_PAD_LEFT); ?></td>
			</tr>
			<tr>
                <td class="category">
                    问题简介
                </td>
                <td>
                    <input type="text" <?php echo helper_get_tab_index() ?> id="tag-name" name="summary" class="input-sm" value="<?php echo $t_template_row['summary'] ?>"/>
                </td>
            </tr>

			<tr>
				<td class="category">
                    问题描述
				</td>

                <td>
                    <!-- 富文本编辑器 -->
                    <textarea class="form-control"id="description" name="description" cols="80"
                              rows="10" required><?php echo $t_template_row['description'] ?></textarea>
                </td>
                <!--导入配置文件-->
                <script type="text/javascript" src="/ue/ueditor.config.js"></script>
                <script type="text/javascript" src="/ue/ueditor.all.js"></script>
                <!-- 绑定 ue-->
                <script type="text/javascript">
                    var ue = UE.getEditor('description');
                    document.getElementById('description').className='edui-default';
                </script>
			</tr>
		</fieldset>
		</table>
		</div>
		</div>
		</div>
		</div>
		<div class="widget-toolbox padding-8 clearfix">
			<input <?php echo helper_get_tab_index() ?> type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'tag_update_button' ) ?>" />
            <a style="margin-left: 10px;" class="btn btn-primary btn-white btn-round" href="template_delete.php?template_id=<?php echo $f_template_id ?>">删除标签</a>
		</div>
		</div>
	</form>
</div>

<?php
layout_page_end();
