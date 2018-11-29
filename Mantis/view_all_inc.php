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
 * View all bugs include file
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses category_api.php
 * @uses columns_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses event_api.php
 * @uses filter_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */

if( !defined( 'VIEW_ALL_INC_ALLOW' ) ) {
	return;
}

require_api( 'category_api.php' );
require_api( 'columns_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'event_api.php' );
require_api( 'filter_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );

$t_filter = current_user_get_bug_filter();
filter_init( $t_filter );

list( $t_sort, ) = explode( ',', $g_filter['sort'] );
list( $t_dir, ) = explode( ',', $g_filter['dir'] );

$g_checkboxes_exist = false;


# Improve performance by caching category data in one pass
if( helper_get_current_project() > 0 ) {
	category_get_all_rows( helper_get_current_project() );
}

$g_columns = helper_get_columns_to_view( COLUMNS_TARGET_VIEW_PAGE );
bug_cache_columns_data( $t_rows, $g_columns );

$t_filter_position = config_get( 'filter_position' );

# -- ====================== FILTER FORM ========================= --
if( ( $t_filter_position & FILTER_POSITION_TOP ) == FILTER_POSITION_TOP && !isset($_SESSION['is_out_user'])) {
	filter_draw_selection_area( $f_page_number );
}
# -- ====================== 客户端搜索 ===========================
if (isset($_SESSION['is_out_user'])) {
    # 先获取所有类型
    $project_id = out_user_get_field(auth_get_current_user_id(), 'project_id');
//    $cates = category_get_all_rows($project_id);

    # 获取上次搜索的类别、进度、搜索内容
    # 类别
//    $search_cate = '';
//    if(isset($_POST['category_id'])) {
//        $search_cate = intval($_POST['category_id']);
//    }
    # 进度
    $status = 0;
    if(isset($_SESSION['filter_status'])) {
        $status = intval($_SESSION['filter_status']);
    }
    $search = '';
    if(isset($_SESSION['filter_summary'])) {
        $search = $_SESSION['filter_summary'];
    }

    # 开始日期
    $startdate = '';
    if(isset($_SESSION['filter_startdate'])) {
        $startdate = $_SESSION['filter_startdate'];
    }

    # 结束日期
    $enddate = '';
    if(isset($_SESSION['filter_enddate'])) {
        $enddate = $_SESSION['filter_enddate'];
    }
//    echo $search;die;
?>
    <div style="margin: 5px 0 0 20px;">
        <form method="post" action="view_all_bug_page.php">
            <!-- 去掉类别筛选 默认只显示工单 -->
            <!--<select name="category_id" class="selectpicker" style="margin: 5px 20px 0 30px; width: 100px;">
                <option value="0">所有类别</option>
                <?php
/*                foreach ($cates as $cate) {
                */?>
                    <option value="<?php /*echo $cate['id']*/?>" <?php /*if( $cate['id'] == $search_cate) echo 'selected="selected"'; */?>>
                    <?php /*echo $cate['name']*/?></option>
                <?php
/*                }
                */?>
            </select>-->
            <script type="text/javascript" src="js/moment-with-locales-2.15.2.min.js" charset="UTF-8"></script>
            <script type="text/javascript" src="js/bootstrap-datetimepicker-4.17.47.min.js" charset="UTF-8"></script>

            <!-- 编号筛选 -->
            <label for="startdate">提交时间:</label>
            <input id="startdate" name="startdate" value="<?php echo $startdate; ?>" type="text">
            <label for="enddate">至</label>
            <input id="enddate" name="enddate" value="<?php echo $enddate; ?>" type="text">
            <script>
                $('#startdate').datetimepicker({
                    format: 'YYYY-MM-DD'//显示格式
                });
                $('#enddate').datetimepicker({
                    format: 'YYYY-MM-DD'
                });
            </script>
            <!-- 状态筛选 -->
            <label for="selstatus" style="margin-left: 20px;">处理状况: </label>
            <select id="selstatus" name="status" class="selectpicker">
                <option value="0" <?php if($status == 0) echo 'selected="selected"'; ?>>所有状态</option>
                <option value="10" <?php if($status == 10) echo 'selected="selected"'; ?>>未受理</option>
                <!--<option value="20" <?php /*if($status == 20) echo 'selected="selected"'; */?>>反馈</option>
                <option value="30" <?php /*if($status == 30) echo 'selected="selected"'; */?>>公认</option>-->
                <option value="40" <?php if($status == 40) echo 'selected="selected"'; ?>>已查看</option>
                <option value="50" <?php if($status == 50) echo 'selected="selected"'; ?>>已受理</option>
                <option value="80" <?php if($status == 80) echo 'selected="selected"'; ?>>已处理</option>
                <!--<option value="90" <?php /*if($status == 90) echo 'selected="selected"'; */?>>已关闭</option>-->
            </select>

            <!-- 关键字搜索 -->
            <label for="summary" style="margin-left: 20px;">关键字:</label>
            <input id="summary" type="text" name="summary" value="<?php echo $search; ?>">
            <button type="submit" class="btn btn-primary  btn-sm" style=" margin: 0 10px 2px 10px; border-radius: 2px; vertical-align: middle;">搜索</button>
        </form>
    </div>
<?php
}

# -- ====================== end of FILTER FORM ================== --


# -- ====================== BUG LIST ============================ --

?>
<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<form id="bug_action" method="post" action="view_all_set.php?f=3">
<?php # CSRF protection not required here - form does not result in modifications ?>
<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
	<h4 class="widget-title lighter">
		<i class="ace-icon fa fa-columns"></i>
		<?php
            if(!isset($_SESSION['is_out_user'])) {
                echo lang_get( 'viewing_bugs_title' );
            } else {
                echo '查看工单';
            }
        ?>
		<?php
			# -- Viewing range info --
			$v_start = 0;
			$v_end = 0;
			if (count($t_rows) > 0) {
				$v_start = $g_filter['per_page'] * ($f_page_number - 1) + 1;
				$v_end = $v_start + count($t_rows) - 1;
			}
			echo '<span class="badge"> ' . $v_start . ' - ' . $v_end . ' / ' . $t_bug_count . '</span>' ;
		?>
	</h4>
	</div>

	<div class="widget-body">
    <?php
    if(!isset($_SESSION['is_out_user'])) { ?>
	<div class="widget-toolbox padding-8 clearfix">
		<div class="btn-toolbar">
			<div class="btn-group pull-left">
		<?php
			$t_filter_param = filter_get_temporary_key_param( $t_filter );
			$t_filter_param = ( empty( $t_filter_param ) ? '' : '?' ) . $t_filter_param;
			# -- Print and Export links --
			print_small_button( 'print_all_bug_page.php' . $t_filter_param, lang_get( 'print_all_bug_page_link' ) );
			print_small_button( 'csv_export.php' . $t_filter_param, lang_get( 'excel_export' ) );
//			print_small_button( 'excel_xml_export.php' . $t_filter_param, lang_get( 'excel_export' ) );

			$t_event_menu_options = $t_links = event_signal('EVENT_MENU_FILTER');

			foreach ($t_event_menu_options as $t_plugin => $t_plugin_menu_options) {
				foreach ($t_plugin_menu_options as $t_callback => $t_callback_menu_options) {
					if (!is_array($t_callback_menu_options)) {
						$t_callback_menu_options = array($t_callback_menu_options);
					}

					foreach ($t_callback_menu_options as $t_menu_option) {
						if ($t_menu_option) {
							echo $t_menu_option;
						}
					}
				}
			}
		?>
		</div>
		<div class="btn-group pull-right"><?php
			# -- Page number links --
			$t_tmp_filter_key = filter_get_temporary_key( $t_filter );
			print_page_links( 'view_all_bug_page.php', 1, $t_page_count, (int)$f_page_number, $t_tmp_filter_key );
			?>
		</div>
	</div>
    <?php } ?>

    </div>

<div class="widget-main no-padding">
	<div class="table-responsive">
	<table id="buglist" class="table table-bordered table-condensed table-hover table-striped">
	<thead>
<?php # -- Bug list column header row -- ?>
<tr class="buglist-headers">
<?php
    // 客户端显示的列不一样
    if(isset($_SESSION['is_out_user'])) {
//        $t_columns = array('工单No.', '问题摘要', '状态', '提交时间', '操作');
        $g_columns = array('id', 'summary', 'status', 'last_updated','date_submitted', '操作');
    }
    # 这里根据column拼接函数名 形如print_column_title_id
    $t_title_function = 'print_column_title';
    $t_sort_properties = filter_get_visible_sort_properties_array($t_filter, COLUMNS_TARGET_VIEW_PAGE);

	foreach( $g_columns as $t_column ) {
		helper_call_custom_function( $t_title_function, array( $t_column, COLUMNS_TARGET_VIEW_PAGE, $t_sort_properties ) );
	}

?>
</tr>

</thead><tbody>

<?php

/**
 * 客户 问题列表
 */
function out_write_bug_rows($t_rows) {
    # 10:新建,20:反馈,30:公认,40:已确认,50:已分派,80:已解决,90:已关闭
    $status_arr = array(
        '10' => '未受理',
//        '20' => '反馈',
//        '30' => '公认',
        '40' => '已查看',
        '50' => '已受理',
        '80' => '已处理',
    );
    // 判断是否有提交数据
    foreach($t_rows as $row) {
        echo '<tr>';
        # 订单号
        echo '<td>'.str_pad($row->id, 7, "0", STR_PAD_LEFT).'</td>';
//        # 工单类型
//        echo '<td>'.category_get_name($row->category_id).'</td>';
        # 工单内容
        echo '<td style="text-align: left;  width:300px;">'.$row->summary.'</td>';
        # 工单状态
        echo '<td>'.$status_arr[$row->status].'</td>';
        # 最后更新时间
        echo '<td>'.date('Y-m-d', $row->last_updated).'</td>';
        # 提交时间
        echo '<td>'.date('Y-m-d', $row->date_submitted).'</td>';
        # 加急按钮
        echo '<td style="width:150px;">';
        echo "<a href='view_bug_detail_page.php?bug_id=$row->id'>查看</a>&nbsp;&nbsp;";
        if($row->status < 80) {
            # 加急按钮（要判断是否加急过）
            if(!bug_hasjiaji($row->id)) {
                echo '<a href = "out_bug_status.php?bug_id=' . $row->id . '&jiaji=1" > 加急</a >';
            } else {
                echo '<a>已加急</a >';
            }
            # 关闭按钮
            echo '&nbsp;&nbsp;<a href = "out_bug_status.php?bug_id='.$row->id.'&close=1" > 关闭</a >';
        }
        echo '</td>';
        echo '</tr>';
    }
}

/**
 * Output Bug Rows
 *
 * @param array $p_rows An array of bug objects.
 * @return void
 */
function write_bug_rows( array $p_rows ) {
	global $g_columns, $g_filter;

	$t_in_stickies = ( $g_filter && ( 'on' == $g_filter[FILTER_PROPERTY_STICKY] ) );

	# -- Loop over bug rows --

	$t_rows = count( $p_rows );
	for( $i=0; $i < $t_rows; $i++ ) {
		$t_row = $p_rows[$i];

		if( ( 0 == $t_row->sticky ) && ( 0 == $i ) ) {
			$t_in_stickies = false;
		}
		if( ( 0 == $t_row->sticky ) && $t_in_stickies ) {	# demarcate stickies, if any have been shown
?>
		   <tr>
				   <td colspan="<?php echo count( $g_columns ); ?>" bgcolor="#d3d3d3"></td>
		   </tr>
<?php
			$t_in_stickies = false;
		}

		echo '<tr>';

		$t_column_value_function = 'print_column_value';
		foreach( $g_columns as $t_column ) {
			helper_call_custom_function( $t_column_value_function, array( $t_column, $t_row ) );
		}
		echo '</tr>';
	}
}


if(isset($_SESSION['is_out_user'])) {
    out_write_bug_rows($t_rows);
} else {
    write_bug_rows( $t_rows );
}
# -- ====================== end of BUG LIST ========================= --
?>

</tbody>
</table>
</div>

<div class="widget-toolbox padding-8 clearfix">
<?php
# -- ====================== MASS BUG MANIPULATION =================== --
# @@@ ideally buglist-footer would be in <tfoot>, but that's not possible due to global g_checkboxes_exist set via write_bug_rows()
?>
	<div class="form-inline pull-left">
<?php
		if( $g_checkboxes_exist ) {
			echo '<label class="inline">';
			echo '<input class="ace check_all input-sm" type="checkbox" id="bug_arr_all" name="bug_arr_all" value="all" />';
			echo '<span class="lbl padding-6">' . lang_get( 'select_all' ) . ' </span > ';
			echo '</label>';
		}
		if( $g_checkboxes_exist ) {
?>
			<select name="action" class="input-sm">
				<?php print_all_bug_action_option_list($t_unique_project_ids) ?>
			</select>
			<input type="submit" class="btn btn-primary btn-white btn-sm btn-round" value="<?php echo lang_get('ok'); ?>"/>
<?php
		} else {
			echo '&#160;';
		}
?>
			</div>
			<div class="btn-group pull-right">
				<?php
					$t_tmp_filter_key = filter_get_temporary_key( $t_filter );
					print_page_links('view_all_bug_page.php', 1, $t_page_count, (int)$f_page_number, $t_tmp_filter_key );
				?>
			</div>
<?php # -- ====================== end of MASS BUG MANIPULATION ========================= -- ?>
</div>
</div>

<?php
    # 修复div不匹配
    if(!isset($_SESSION['is_out_user'])) {
        echo '</div>';
    }
?>

</div>
</form>
</div>
<?php

# -- ====================== FILTER FORM ========================= --
if( ( $t_filter_position & FILTER_POSITION_BOTTOM ) == FILTER_POSITION_BOTTOM ) {
	filter_draw_selection_area( $f_page_number );
}
# -- ====================== end of FILTER FORM ================== --
