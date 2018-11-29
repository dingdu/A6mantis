<?php
/**
 * Notice: 处理问题集所需要的ajax操作
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\8\2 0002
 * Time: 15:46
 */

require_once ('core.php');

require_api('bug_template_api.php');

// 根据type选择不同的操作
if(isset($_GET['type']) && !empty($_GET['type'])) {
        switch ($_GET['type']) {
            // 获取对应问题模版列表
            case 'getBugTemplateList':
                if(isset($_GET['bug_template_category_id']) && !empty($_GET['bug_template_category_id'])) {
                    $list = getBugTemplatesByCategory($_GET['bug_template_category_id']);
                    echo json_encode($list);
                }
                break;
            // 创建模版分类
            case 'createTemplateCategory':
                if(!isset($_POST['catename']) || empty($_POST['catename'])) {
                    echo 0;
                } else {
                    if(checkBugTemplateCategoryExist($_POST['catename'])) {
                        echo 2;
                    } else {
                        createBugTemplateCategory($_POST['catename']);
                        echo 1;
                    }
                }
                break;

    }

}
