<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/11 0011
 * Time: 14:46
 */

/* function pagination
   * @ $curpage:当前页
   * @ $count:总记录数
   * @ $eachpage:每页记录数
   * */

function pagination($curpage, $count, $eachpage)
{
    $retData = array();

    $retData['first_row'] = ($curpage - 1) * $eachpage;
    $retData['end_row'] = $retData['first_row'] + $eachpage;
    $pages = ceil($count / $eachpage);
    $retData['total_pages'] = (int)$pages;

    $html = ' <ul class="pagination"> <li><a href="#" data-page="1" aria-label="Previous">首页 </a></li>';
    $page_start = ($curpage == 1) ? $curpage : ($curpage - 1);
    $page_end = $curpage + 1;
    if ($page_end > $pages)
        $page_end = $pages;

    if ($page_start > 1) {
        $html .= "<li  data-page='0' class='disabled' ><a class='disable'>...</a></li>";
    }

    for ($i = $page_start; $i <= $page_end; $i ++) {
        $html = $html."<li ";
        if ($i == $curpage)
            $html .= "class='active'";
        $html .= "><a  href=''  data-page='" . $i . "'>" . $i . "</a></li>";
    }

    if (($curpage + 1) < $pages) {
        $html .= "<li  data-page='0' class='disabled' ><a class='disable'>...</a></li>";
    }

    $html .= '<li><a href="#" data-page="' . $pages . '" aria-label="Next"> 末页 </a></li> </ul>';

    $retData['html'] = $html;
    return $retData;

}