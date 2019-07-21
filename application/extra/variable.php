<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/15
 * Time: 13:44
 */
//数据统计订单类型
define('STATISTIC_TYPE_LIST', ['vip'=>'会员','order' => '订单', 'news' => '新闻']);
//数据变动模块
define('DATA_CHANGE_CLASSIFY', ['money' => '余额', 'integral' => '积分']);
//数据变动类型
define('DATA_CHANGE_TYPE',
[
    'recharge' => '充值',
    'order' => '下单',
    'recharge' => '充值',
    'distribution' => '分销佣金',
    'withdraw' => '提现',
    'withdraw_fail' => '提现失败',
    'integral' => '积分商城',
    'system' => '后台操作'
]);
//数据变动条件
define('DATA_CHANGE_PARAM',
[
    'money' => [
        'operate_table' => \tname::vip,
        'operate_field' => 'money',
        'operate_where' => 'id',
    ],
    'income' => [
        'operate_table' => \tname::vip,
        'operate_field' => 'income',
        'operate_where' => 'id',
    ],
    'integral' => [
        'operate_table' => \tname::vip,
        'operate_field' => 'integral',
        'operate_where' => 'id',
    ],
    'agentMoney' => [
        'operate_table' => \tname::agent_user,
        'operate_field' => 'money',
        'operate_where' => 'id',
    ],
]);
