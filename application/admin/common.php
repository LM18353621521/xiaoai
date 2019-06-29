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

function getMenuArr(){
    $menuArr = config('menu');
    $act_list = session('act_list');
    if($act_list != 'all' && !empty($act_list)){
        $right = db(\tname::system_menu)->where("id in ($act_list)")->cache(true)->column('right');
        $role_right = '';
        foreach ($right as $val){
            $role_right .= $val.',';
        }
        foreach($menuArr as $k=>$val){
            foreach ($val['_child'] as $j=>$v){
                if(strpos(strtolower($role_right),$val['group'].'@'.$v['_child'][0]) === false){
                    unset($menuArr[$k]['_child'][$j]);//过滤菜单
                }
            }
        }
        foreach($menuArr as $k=>$val){
            if(empty($val['_child'])){
                unset($menuArr[$k]);//过滤菜单
            }
        }
    }
    return $menuArr;
}