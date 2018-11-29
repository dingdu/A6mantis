<?php

/**
 *
 * 问题模块管理
 *
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'database_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'bug_template_api.php' );

access_ensure_global_level( config_get( 'tag_edit_threshold' ) );

compress_enable();

$t_can_edit = access_has_global_level( config_get( 'tag_edit_threshold' ) );
$f_filter = mb_strtoupper( gpc_get_string( 'filter', config_get( 'default_manage_tag_prefix' ) ) );
$f_page_number = gpc_get_int( 'page_number', 1 );

# Start Index Menu
$t_prefix_array = array( 'ALL' );

for( $i = 'A'; $i != 'AA'; $i++ ) {
	$t_prefix_array[] = $i;
}

for( $i = 0; $i <= 9; $i++ ) {
	$t_prefix_array[] = (string)$i;
}
if( $f_filter === 'ALL' ) {
	$t_name_filter = '';
} else {
	$t_name_filter = $f_filter;
}

# Set the number of Tags per page.
$t_per_page = 20;
$t_offset = (( $f_page_number - 1 ) * $t_per_page );


# Retrieve Tags from table
//$t_result = tag_get_all( $t_name_filter, $t_per_page, $t_offset ) ;
// 获取模版
$t_templates = getTemplates();

// 获取所有分类
$t_category = getAllBugTemplateCategory();

$t_cate_arr = array();

foreach ($t_category as $cate) {
    $t_cate_arr[$cate['id']] = $cate['name'];
}

# Determine number of tags in tag table
//$t_total_tag_count = tag_count( $t_name_filter );
$t_total_tag_count= count($t_templates);

#Number of pages from result
$t_page_count = ceil( $t_total_tag_count / $t_per_page );

if( $t_page_count < 1 ) {
	$t_page_count = 1;
}

# Make sure $p_page_number isn't past the last page.
if( $f_page_number > $t_page_count ) {
	$f_page_number = $t_page_count;
}

# Make sure $p_page_number isn't before the first page
if( $f_page_number < 1 ) {
	$f_page_number = 1;
}

layout_page_header( lang_get( 'manage_tags_link' ) );

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_template_page.php' );
?>

<div class="col-md-12 col-xs-12">
<!-- ABCD标签跳转栏 -->
<!--	<div class="space-10"></div>-->
<!--	<div class="center">-->
<!--		<div class="btn-toolbar inline">-->
<!--		<div class="btn-group">-->
<!--	--><?php
//	foreach ( $t_prefix_array as $t_prefix ) {
//		$t_caption = ( $t_prefix === 'ALL' ? lang_get( 'show_all_tags' ) : $t_prefix );
//		$t_active = $t_prefix == $f_filter ? 'active' : '';
//		echo '<a class="btn btn-xs btn-white btn-primary ' . $t_active .
//		'" href="manage_tags_page.php?filter=' . $t_prefix .'">' . $t_caption . '</a>' ."\n";
//	} ?>
<!--		</div>-->
<!--	</div>-->
<!--	</div>-->

<div class="space-10"></div>

<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<i class="ace-icon fa fa-tags"></i>
			模版管理
			<span class="badge"><?php echo $t_total_tag_count ?></span>
		</h4>
	</div>

	<div class="widget-body">
		<?php if ($t_can_edit) { ?>
			<div class="widget-toolbox padding-8 clearfix">
				<?php print_small_button( '#templatecreate', '创建问题模版' ) ?>
				<button class="btn btn-primary btn-white btn-round btn-sm" data-toggle="modal" data-target="#myModal">创建模版类型</button>
			</div>
		<?php } ?>


    <!-- 输入类型名称模态框 -->
    <div class="modal fade basic" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" style="max-width: 450px !important;  min-width: 320px !important; ">
            <form id="cateForm" name="cateForm"  method="post">
                <input type="hidden" name="type" value="gongdan">
                <div class="modal-content">

                    <div class="modal-body material-content clearfix" >

                        <div class="form-group">
                            <input id="catename" type="text" name="catename" ng-model='smscode' class="form-control" placeholder="输入类型名称">
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button onclick="createCate()" type="button" class="btn btn-primary">确定</button>
                        <button type="button" class="btn smscodebtn-default" data-dismiss="modal">取消</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


	<div class="widget-main no-padding">
	<div class="table-responsive">
	<table class="table table-striped table-bordered table-condensed table-hover">
		<thead>
			<tr>
				<td>编号</td>
				<td>简介</td>
				<td>描述</td>
				<td>模版类别</td>
			</tr>
		</thead>
		<tbody>
<?php
		# Display all tags
		foreach( $t_templates as  $t_template) {
?>
			<tr>
				<td><a href="template_update_page.php?template_id=<?php echo $t_template['id'] ?>" ><?php echo str_pad($t_template['id'], 3, '0', STR_PAD_LEFT); ?></a></td>
				<td><?php echo $t_template['summary'] ?></td>
				<td><?php echo  $t_template['description'] ?></td>
				<td><?php echo  $t_cate_arr[$t_template['temp_category_id']] ?></td>
			</tr>
<?php
		} # end while loop on tags
?>
		</tbody>
	</table>
	</div>
	</div>
	<div class="widget-toolbox padding-8 clearfix">
	<div class="btn-toolbar pull-right"><?php
		# @todo hack - pass in the hide inactive filter via cheating the actual filter value
		print_page_links( 'manage_tags_page.php', 1, $t_page_count, (int)$f_page_number, $f_filter ); ?>
	</div>
</div>
</div>
</div>

<?php if( $t_can_edit ) { ?>
<div class="space-10"></div>
	<form id="manage-template-create-form" method="post" action="template_create.php">
	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="ace-icon fa fa-tag"></i>
				创建问题模版
			</h4>
		</div>
		<div class="widget-body">
			<a name="templatecreate"></a>
			<div class="widget-main no-padding">
		<div class="form-container">
		<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped">
		<fieldset>
			<?php echo form_security_field( 'template_create' ); ?>
			<tr>
				<td class="category">
					<span class="required">*</span> 问题类型
				</td>
				<td>
				    <select name="temp_category_id" id="temp_category_id">
				        <?php foreach($t_category as $t_cate) { ?>
				            <option value="<?php echo $t_cate['id'] ?>"><?php echo $t_cate['name'] ?></option>
				        <?php } ?>
                    </select>
				</td>
			</tr>
			<tr>
				<td class="category">
					<span class="required">*</span> 问题简介
				</td>
				<td>
					<input type="text" id="template-summary" name="summary" class="input-sm" size="40" maxlength="100" required />
				</td>
			</tr>
			<tr>
				<td class="category">
					<span class="required">*</span>问题详情
				</td>
				<td>
					<!-- 富文本编辑器 -->
                    <textarea class="form-control"id="description" name="description" cols="80"
                      rows="10" required></textarea>
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
			<div class="widget-toolbox padding-8 clearfix">
				<span class="required pull-right"> * <?php echo lang_get( 'required' ); ?></span>
				<input type="submit" name="config_set" class="btn btn-primary btn-sm btn-white btn-round"
					   value="创建问题模版"/>
			</div>
		</div>
	</div>
    </form>
    <script >
        function createCate() {
            /* 动态生成类型脚本 */
            $.post('ajax_handle_bug_template.php?type=createTemplateCategory',
                $('#cateForm').serialize(), function(data) {
                if(data == 1) {
                    alert('添加分类成功');
                    location.reload();
                } else if(data == 2) {
                    alert('分类已存在');
                } else {
                    alert('添加异常');
                }
            });
        }

    </script>
<?php
} #End can Edit
echo '</div>';
layout_page_end();
