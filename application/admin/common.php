<?php
/**
 * Created by PhpStorm.
 * User: Lu
 * Date: 2018/11/26
 * Time: 22:38
 */

/**
 * 管理员操作记录
 * @param $log_info string 记录信息
 */
function adminLog($log_info){
    $add['log_time'] = time();
    $add['admin_id'] = session('admin_id');
    $add['log_info'] = $log_info;
    $add['log_ip'] = request()->ip();
    $add['log_url'] = request()->baseUrl() ;
    $res=dataUpdate(\tname::admin_log,$add);
}