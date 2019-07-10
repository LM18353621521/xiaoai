<?php

return [
    [
        'group' => 'config',
        'name' => '系统设置',
        'icon' => 'icon-cog',
        '_child' => [
            [
                'name' => '基础设置',
                '_child' => ['config']
            ],
            [
                'name' => '快递编码',
                '_child' => ['express_list','express_add']
            ],
            [
                'name' => '运费设置',
                '_child' => ['freight_list','freight_add']
            ],
            [
                'name' => '轮播设置',
                '_child' => ['carousel']
            ],
//            [
//                'name' => 'PC底部标签',
//                '_child' => ['slogen']
//            ],
//            [
//                'name' => '地区列表',
//                '_child' => ['area','areaadd']
//            ],
//            [
//                'name' => '数据导入',
//                '_child' => ['import']
//            ],
            [
                'name' => '清除缓存',
                '_child' => ['cleancache']
            ],
        ]
    ],
    [
        'group' => 'wechat',
        'name' => '微信设置',
        'icon' => 'icon-comments',
        '_child' => [
//            [
//                'name' => '公众号微信授权配置',
//                '_child' => ['auth']
//            ],
//            [
//                'name' => '公众号微信支付配置',
//                '_child' => ['pay']
//            ],
            [
                'name' => '小程序授权配置',
                '_child' => ['appletauth']
            ],
            [
                'name' => '小程序支付配置',
                '_child' => ['appletpay']
            ],
//            [
//                'name' => '其他配置',
//                '_child' => ['config']
//            ],
//            [
//                'name' => '微众绑号专用',
//                '_child' => ['fileupload']
//            ],
//            [
//                'name' => '系统链接列表',
//                '_child' => ['url', 'urladd']
//            ],
//            [
//                'name' => '图文素材列表',
//                '_child' => ['news', 'newsadd']
//            ],
//            [
//                'name' => '关键词回复设置',
//                '_child' => ['keywords', 'keywordsadd']
//            ],
//            [
//                'name' => '微信事件设置',
//                '_child' => ['wxevent']
//            ],
//            [
//                'name' => '自定义菜单设置',
//                '_child' => ['classify']
//            ],
        ]
    ],

    [
        'group' => 'account',
        'name' => '权限管理',
        'icon' => 'icon-reorder',
        '_child' => [
            [
                'name' => '管理员列表',
                '_child' => ['account','accountadd']
            ],
            [
                'name' => '角色管理',
                '_child' => ['auth','authadd']
            ],
            [
                'name' => '权限列表',
                '_child' => ['right_list','edit_right']
            ],

        ]
    ],

    [
        'group' => 'mall',
        'name' => '商城管理',
        'icon' => 'icon-shopping-cart',
        '_child' => [
            [
                'name' => '商品列表',
                '_child' => ['product', 'productadd']
            ],
            [
                'name' => '订单列表',
                '_child' => ['order', 'orderdetail']
            ],
            [
                'name' => '退款订单',
                '_child' => ['order_refund', 'order_refund_detail']
            ],
            [
                'name' => '商品分类',
                '_child' => ['category', 'categoryadd']
            ],
            [
                'name' => '商品模型',
                '_child' => ['goods_type', 'goodstypeadd']
            ],
            [
                'name' => '商品规格',
                '_child' => ['goods_spec', 'goods_spec_add']
            ],
            [
                'name' => '品牌列表',
                '_child' => ['brandlist', 'brand_add']
            ],
            [
                'name' => '商品评价',
                '_child' => ['comment', 'commentdetail']
            ],
//            [
//                'name' => '门店列表',
//                '_child' => ['store', 'storeadd']
//            ]
        ]
    ],
    [
        'group' => 'promotion',
        'name' => '促销管理',
        'icon' => 'icon-shopping-cart',
        '_child' => [
            [
                'name' => '限时秒杀',
                '_child' => ['seckill', 'seckill_detail']
            ],
        ]
    ],
    [
        'group' => 'coupon',
        'name' => '优惠券管理',
        'icon' => 'icon-money',
        '_child' => [
            [
                'name' => '优惠券列表',
                '_child' => ['coupon_list', 'coupon_add']
            ],
        ]
    ],
    [
        'group' => 'fun',
        'name' => '开心一刻',
        'icon' => 'icon-gittip',
        '_child' => [
            [
                'name' => '开心一刻',
                '_child' => ['funlist', 'fun_add']
            ],
        ]
    ],
    [
        'group' => 'distribution',
        'name' => '分销管理',
        'icon' => 'icon-group',
        '_child' => [
            [
                'name' => '分销设置',
                '_child' => ['config']
            ],
            [
                'name' => '分销关系',
                '_child' => ['mytrader', 'mytraderdetail']
            ],
            [
                'name' => '佣金记录',
                '_child' => ['income']
            ],
            [
                'name' => '佣金明细',
                '_child' => ['income_log']
            ]
        ]
    ],

//    [
//        'group' => 'money',
//        'name' => '余额管理',
//        'icon' => 'icon-money',
//        '_child' => [
//            [
//                'name' => '余额设置',
//                '_child' => ['config']
//            ],
//            [
//                'name' => '充值卡列表',
//                '_child' => ['card', 'cardadd']
//            ],
//            [
//                'name' => '充值列表',
//                '_child' => ['order', 'orderdetail']
//            ],
//            [
//                'name' => '余额日志',
//                '_child' => ['log', 'moneyadd']
//            ],
//        ]
//    ],

//    [
//        'group' => 'integral',
//        'name' => '积分管理',
//        'icon' => 'icon-magnet',
//        '_child' => [
//            [
//                'name' => '积分设置',
//                '_child' => ['config']
//            ],
//            [
//                'name' => '积分日志',
//                '_child' => ['log', 'integraladd']
//            ],
//            [
//                'name' => '积分商城轮播',
//                '_child' => ['carousel']
//            ],
//            [
//                'name' => '商品列表',
//                '_child' => ['product', 'productadd']
//            ],
//            [
//                'name' => '订单列表',
//                '_child' => ['order', 'orderdetail']
//            ]
//        ]
//    ],

    [
        'group' => 'news',
        'name' => '文章管理',
        'icon' => 'icon-tasks',
        '_child' => [
            [
                'name' => '文章分类',
                '_child' => ['category', 'categoryadd']
            ],
            [
                'name' => '文章列表',
                '_child' => ['news', 'newsadd']
            ],
//            [
//                'name' => '评价列表',
//                '_child' => ['review']
//            ]
        ]
    ],
    [
        'group' => 'agent',
        'name' => '代理商管理',
        'icon' => 'icon-user',
        '_child' => [
            [
                'name' => '代理商列表',
                '_child' => ['index','detail']
            ],
            [
                'name' => '代理商等级',
                '_child' => ['level_list','edit_level']
            ],
            [
                'name' => '充值记录',
                '_child' => ['recharge_list']
            ],
        ]
    ],
    [
        'group' => 'vip',
        'name' => '会员管理',
        'icon' => 'icon-user',
        '_child' => [
            [
                'name' => '用户列表',
                '_child' => ['vip']
            ],
            [
                'name' => '提现列表',
                '_child' => ['withdraw']
            ],
            [
                'name' => '意见反馈',
                '_child' => ['feedback', 'feedbackdetail']
            ]
        ]
    ],
    [
        'group' => 'statistics',
        'name' => '数据统计',
        'icon' => 'icon-bar-chart',
        '_child' => [
            [
                'name' => '数据统计',
                '_child' => ['statistics']
            ],
        ]
    ],

];