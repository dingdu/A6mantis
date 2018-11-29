<?php
/**
 * Created by PhpStorm.
 * Notes: 自定义的工具函数库
 * User: Administrator
 * Date: 2018\7\20 0020
 * Time: 11:58
 */

/**
 * Notes: 判断字符串是否被序列化过
 * User: dingduming
 * Date: 2018\7\20 0020
 * Time: 12:01
 * @param $data
 * @return bool
 */
function is_serialized( $data ) {
    $data = trim( $data );
    if ( 'N;' == $data ) return true;
    if ( !preg_match( '/^([adObis]):/', $data, $badions ) ) return false;
    switch ( $badions[1] )
    {
        case 'a' : case 'o' : case 's' :
        if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) ) return true;
        break;
        case 'b' : case 'i' : case 'd':
        if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) ) return true;
        break;
    }
    return false;
}
