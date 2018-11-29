<?php
/**
 * Notice: 输出项目的wbs（excel格式）
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\8\1 0001
 * Time: 13:59
 */

require_once ('core.php');
require_once("./vendor/PHPExcel/Classes/PHPExcel.php");

require_api('gpc_api.php');

// 获取项目信息
$project_id = gpc_get_int('project_id');
$project = project_get_row($project_id);
$project_ext = project_ext_get_row($project_id);
// 获取用户信息
$user_id = auth_get_current_user_id();
$usre_name = user_get_name($user_id);
// 获取项目对应bug列表
$bug_list = bug_get_rows_by_project($project_id);
//var_dump($bug_list);die;
ob_clean(); // 先清一下

// 这里设置加急、待完善、已完成三种状态的颜色RGB
$jiaji_color = 'FA8072';
$done_color = 'EE82EE';
$undone_color = '7FFFAA';

// 根据分类设置相应的颜色
$category_color = [
    '默认分类' => '90EE90',
    '功能开发' => '00FFFF',
    'APP上架' => '7B68EE',
    '支付' => 'DDA0DD',
    '准备资料' => '008080',
    '功能测试' => '00FF7F',
    '注册' => '808000',
    '登录' => 'BDB76B',
    '奖金制度' => 'FFA500',
    '会员激活' => 'A0522D',
    '统计' => '808080',
    '功能修复' => 'FFE4B5',
    '会议&培训' => '006400',
    '工单' => '40E0D0',
];

$sign_time = $project_ext['sign_time'] == null ? '' : date('Y-m-d H:i:s', $project_ext['sign_time']);
$submit_time = $project_ext['submit_time'] == null ? '' : date('Y-m-d H:i:s', $project_ext['submit_time']);

$data = [
    ['项目名称:', $project['name'], '', '', '合同签约时间:', $sign_time,
        '提交上线时间:', $submit_time],
    [],
    ['工作类别', '工作内容', '负责人', '计划开始时间', '计划结束时间', '实际开始时间', '实际结束时间', '颜色块'],
];

$phpxcel = new PHPExcel();


$phpxcel->createSheet();

# 设置宽度
# 选中所有
$phpxcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
$phpxcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
# 设置第一行高度
$phpxcel->getActiveSheet()->getRowDimension(1)->setRowHeight('25px');
# 设置居中方式

# 设置提示框颜色
$phpxcel->getActiveSheet()->setCellValue('J2', '加急处理');
$phpxcel->getActiveSheet()->getStyle('J2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$phpxcel->getActiveSheet()->getStyle('K2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$phpxcel->getActiveSheet()->getStyle('K2')->getFill()->getStartColor()->setRGB($jiaji_color);

$phpxcel->getActiveSheet()->setCellValue('J4', '待完善');
$phpxcel->getActiveSheet()->getStyle('J4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$phpxcel->getActiveSheet()->getStyle('K4')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$phpxcel->getActiveSheet()->getStyle('K4')->getFill()->getStartColor()->setRGB($done_color);

$phpxcel->getActiveSheet()->setCellValue('J6', '已完成');
$phpxcel->getActiveSheet()->getStyle('J6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$phpxcel->getActiveSheet()->getStyle('K6')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
$phpxcel->getActiveSheet()->getStyle('K6')->getFill()->getStartColor()->setRGB($undone_color);

$field_y = 1;
// 显示头部
foreach($data as $v){
    $field_x = 'A';
    $t       = count($v);
    foreach( $v as $vo){
        $vo = strip_tags($vo);
        $phpxcel->getActiveSheet()->getStyle($field_x . $field_y)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $phpxcel->getActiveSheet()->getStyle($field_x . $field_y)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $phpxcel->getActiveSheet()->setCellValue($field_x . $field_y, $vo);
        $field_x++;
    }
    ++$field_y;
}
$phpxcel->getActiveSheet()->mergeCells('B1:D1');
// 空行
$phpxcel->getActiveSheet()->mergeCells('A2:H2');


// 每个类别的次数
$cate_count = array();
// 先对bug_list做一些处理
foreach($bug_list as &$bug) {
    // 获取每个分类对应的次数
    if(isset($cate_count[$bug['category_name']])) {
        $cate_count[$bug['category_name']]++;
    } else {
        $cate_count[$bug['category_name']] = 1;
    }
    unset($bug['category_name']);

    // 负责人不能为空默认为项目负责人
    if(empty($bug['realname'])) {
        $bug['realname'] = $project_ext['realname'];
    }

    $bug['real_starttime'] = ' ';
    $bug['real_endtime'] = ' ';
    if($bug['status'] < 80) {
        // 加急了
        if($bug['priority'] >= 50) {
            $bug['color'] = $jiaji_color;
        } else {
            $bug['color'] = $undone_color;
        }
    } else {
        $bug['color'] = $done_color;
    }
    if(empty($bug['expected_starttime'])) {
        $bug['expected_starttime'] = '';
    } else {
        $bug['expected_starttime'] = date('Y-m-d', $bug['expected_starttime']);
    }

    if(empty($bug['expected_endtime'])){
        $bug['expected_endtime'] = '';
    } else {
        $bug['expected_endtime'] = date('Y-m-d', $bug['expected_endtime']);
    }

    unset($bug['status']);
    unset($bug['priority']);
}


// 显示问题分类
$field_y = 4;
foreach($cate_count as $cate_name => $count) {
    $phpxcel->getActiveSheet()->mergeCells('A'.$field_y.':A'.($field_y+$count-1));
    $phpxcel->getActiveSheet()->getStyle('A' . $field_y)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpxcel->getActiveSheet()->getStyle('A' . $field_y)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $phpxcel->getActiveSheet()->setCellValue('A' . $field_y, $cate_name);
    $phpxcel->getActiveSheet()->getStyle('A' . $field_y)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $phpxcel->getActiveSheet()->getStyle('A' . $field_y)->getFill()->getStartColor()->setRGB($category_color[$cate_name]);
    $field_y += $count;
}

// 显示问题列表
$field_y = 4;
// 因为$bug此时是$bug_list最后一个的引用（要unset掉）
unset($bug);

foreach($bug_list as $bug) {
    $field_x = 'B';
    $t       = 7;       // 这里先定死
    foreach($bug as $b) {
        // 根据color即加急、完成、未完成状态来加粗和改变颜色
        switch ($bug['color']) {
            case $done_color:
                $phpxcel->getActiveSheet()->getStyle($field_x . $field_y)->getFont()->getColor()->setRGB('888888');
                break;
            case $jiaji_color:
                $phpxcel->getActiveSheet()->getStyle($field_x . $field_y)->getFont()->setBold(true);
                break;
        }
        $b = strip_tags($b);
        $phpxcel->getActiveSheet()->getStyle($field_x . $field_y)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $phpxcel->getActiveSheet()->getStyle($field_x . $field_y)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $phpxcel->getActiveSheet()->setCellValue($field_x . $field_y, $b);
        // 最后一个是颜色
        if($field_x == 'H') {
            $phpxcel->getActiveSheet()->setCellValue($field_x . $field_y, '');
            $phpxcel->getActiveSheet()->getStyle($field_x . $field_y)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $phpxcel->getActiveSheet()->getStyle($field_x . $field_y)->getFill()->getStartColor()->setRGB($bug['color']);
        }
        $field_x++;
    }
    ++$field_y;
}


header('Content-Type: application/vnd.ms-excel');
// 设置头文件
$ua               = $_SERVER["HTTP_USER_AGENT"];
$filename         = $project['name'].'WBS.xls';
$encoded_filename = str_replace("+", "%20", urlencode($filename));
if (preg_match("/MSIE/", $ua)) {
    header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
} else if (preg_match("/Firefox/", $ua)) {
    header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
} else {
    header('Content-Disposition: attachment; filename="' . $filename . '"');
}
header('Cache-Control: max-age=0');
$writer = new PHPExcel_Writer_Excel5($phpxcel);
$writer->save('php://output'); // 输出到浏览器