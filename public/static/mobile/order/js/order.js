/**
 * Created by Lu on 2018/10/21.
 */
var order_obj = {
    /**
     * 我的订单
     */
    myorder: function () {

        //去详情
        $('.lists').on('click', '.li', function () {
            if (can_click == 0) {
                return false
            }
            can_click = 0;
            var order_id = $(this).attr('order_id');
            window.location.href = "/mobile/Order/orderdetail/order_id/" + order_id;
        });
        //去评价
        $('.lists').on('click', '.comment_do', function (e) {
            e.stopPropagation();
            var p_li = $(this).parents('.li');
            if (can_click == 0) {
                return false
            }
            can_click = 0;
            var order_id = p_li.attr('order_id');
            window.location.href = "/mobile/Order/comment/order_id/" + order_id;
        });

        //查看物流
        $('.lists').on('click', '.express_do', function (e) {
            e.stopPropagation();
            var p_li = $(this).parents('.li');
            if (can_click == 0) {
                return false
            }
            can_click = 0;
            var order_id = p_li.attr('order_id');
            window.location.href = "/mobile/Order/wuliu/order_id/" + order_id;
        });

        //取消订单
        $('.lists').on('click', '.cancel_do', function (e) {
            e.stopPropagation();
            var curr_tab = $('.bt').find('.color01').attr('status');
            var p_li = $(this).parents('.li');
            if (can_click == 0) {
                return false
            }
            var order_id = p_li.attr('order_id');
            $.confirm("确定取消该订单吗？", function () {
                //点击确认后的回调函数
                $.post('/mobile/Order/order_cancel', {order_id: order_id}, function (data) {
                    if (data.ret == 1) {
                        $.toast(data.msg);
                        if (curr_tab == 0) {
                            p_li.find('.code-right').text('已取消');
                            var html = '<div class="date00  gray-active del_do">删除订单</div>';
                            p_li.find('.datebox1').html(html);
                        } else {
                            p_li.remove();
                        }
                    } else {
                        $.toast(data.msg, 'cancel');
                    }
                    can_click = 1;
                })
            }, function () {
                can_click = 1;
                //点击取消后的回调函数
            });
        });


        //订单确认收货
        $('.lists').on('click', '.confirm_do', function (e) {
            e.stopPropagation();
            var curr_tab = $('.bt').find('.color01').attr('status');
            var p_li = $(this).parents('.li');
            if (can_click == 0) {
                return false
            }
            var order_id = p_li.attr('order_id');
            $.confirm("确定已经收货了吗？", function () {
                //点击确认后的回调函数
                $.post('/mobile/Order/order_confirm', {order_id: order_id}, function (data) {
                    if (data.ret == 1) {
                        $.toast(data.msg);
                        window.location.href = "/mobile/Order/discuss/order_id/" + order_id;
                        if (curr_tab == 0) {
                            p_li.find('.code-right').text('待评价');
                            var html = '<div class="date00 pink-active comment_do">去评价</div>';
                            p_li.find('.datebox1').html(html);
                        } else {
                            p_li.remove();
                        }
                    } else {
                        $.toast(data.msg, 'cancel');
                    }
                    can_click = 1;
                })
            }, function () {
                can_click = 1;
                //点击取消后的回调函数
            });
        });

        //删除订单
        $('.lists').on('click', '.del_do', function (e) {
            e.stopPropagation();
            var p_li = $(this).parents('.li');
            if (can_click == 0) {
                return false
            }
            var order_id = p_li.attr('order_id');
            $.confirm("确定删除该订单吗？", function () {
                //点击确认后的回调函数
                $.post('/mobile/Order/order_del', {order_id: order_id}, function (data) {
                    if (data.ret == 1) {
                        $.toast(data.msg,function () {
                            p_li.remove();
                        });
                    } else {
                        $.toast(data.msg, 'cancel');
                    }
                    can_click = 1;
                })
            }, function () {
                can_click = 1;
                //点击取消后的回调函数
            });
        });

        //点击切换
        $(".bt2").click(function () {
            $(".bt2").removeClass("color01");
            $(this).addClass("color01");
            var status = $(this).attr('status');
            $('input[name=status]').val(status);
            can_load = 1;
            $(".lists").find('.content').html('');
            load_obj.load_ajax($(".lists"), 1, function (data) {
                callback(data);
            });
        });
        load_obj.load_ajax($(".lists"), 1, function (data) {
            callback(data);
        });
        load_obj.load_init($('.lists'), 0, function (data) {
            callback(data);
        });
        var callback = function (data) {
            console.log(data);
            can_click = 1;
            //if (data.attach.total == 0) {
            //    $(".nodata").show();
            //}
        };
    },
    /**
     * 订单详情
     */
    orderdetail: function () {
        //查看物流
        $('.ss').on('click', '.express_do', function (e) {
            var order_id = $('input[name=order_id]').val();
            window.location.href = "/mobile/Order/wuliu/order_id/" + order_id;
        });

        //查看物流
        $('.ss').on('click', '.comment_do', function (e) {
            var order_id = $('input[name=order_id]').val();
            window.location.href = "/mobile/Order/comment/order_id/" + order_id;
        });

        //删除订单
        $('.ss').on('click', '.del_do', function (e) {
            e.stopPropagation();
            if (can_click == 0) {
                return false
            }
            var order_id = $('input[name=order_id]').val();
            $.confirm("确定删除该订单吗？", function () {
                //点击确认后的回调函数
                $.post('/mobile/Order/order_del', {order_id: order_id}, function (data) {
                    if (data.ret == 1) {
                        $.toast(data.msg, 1500, function () {
                            window.location.href = document.referrer;
                        });
                    } else {
                        $.toast(data.msg, 'cancel');
                    }
                    can_click = 1;
                })
            }, function () {
                can_click = 1;
                //点击取消后的回调函数
            });
        });
        //去支付
        $('.ss').on('click', '.pay_do', function (e) {
            e.stopPropagation();
            if (can_click == 0) {
                return false
            }
            can_click = 0;
            var order_id = $('input[name=order_id]').val();
            window.location.href = "/Mobile/Order/order_pay/order_id/" + order_id+"/pay_type/alipay";
        });

        //取消订单
        $('.ss').on('click', '.cancel_do', function (e) {
            e.stopPropagation();
            if (can_click == 0) {
                return false
            }
            var order_id = $('input[name=order_id]').val();
            $.confirm("确定取消该订单吗？", function () {
                //点击确认后的回调函数
                $.post('/mobile/Order/order_cancel', {order_id: order_id}, function (data) {
                    if (data.ret == 1) {
                        $.toast(data.msg, 1500, function () {
                            window.location.href = document.referrer;
                        });
                    } else {
                        $.toast(data.msg, 'cancel');
                    }
                    can_click = 1;
                })
            }, function () {
                can_click = 1;
                //点击取消后的回调函数
            });
        });

        //申请退款
        $('.ss').on('click', '.refund_do', function (e) {
            e.stopPropagation();
            if (can_click == 0) {
                return false
            }
            var order_id = $('input[name=order_id]').val();
            $.confirm("确定申请退款吗？", function () {
                //点击确认后的回调函数
                $.post('/mobile/Order/order_refund', {order_id: order_id}, function (data) {
                    if (data.ret == 1) {
                        $.toast(data.msg, 1500, function () {
                            window.location.href = document.referrer;
                        });
                    } else {
                        $.toast(data.msg, 'cancel');
                    }
                    can_click = 1;
                })
            }, function () {
                can_click = 1;
                //点击取消后的回调函数
            });
        });
    },

    /**
     * 评价页面
     */
    comment: function () {
        $("#submit_do").click(function () {
            if (can_click == 0) {
                return false
            }
            var data = $('#data-form').serialize();
            $.post('/mobile/Order/comment', data, function (data) {
                can_click = 1;
                if (data.ret == 1) {
                    $.toast(data.msg, 1500, function () {
                        if (data.url) {
                            window.location.href = data.url;
                        }
                    })
                } else {
                    alert(data.msg);
                    return false;
                }
            }, 'json');
        });
    }

}

var countCartPrice = function () {
    var select_all = 1;
    var total_num = 0;
    var total_price = 0;
    var cartList = $('.cart_ids');
    $.each($('.cartList'), function (i, o) {
        var id = $(this).attr('cart_id');
        var price = $("#price" + id).text();
        var num = $("#num" + id).text();
        if ($(this).find('.bg').hasClass('bg1')) {
            total_price += parseFloat(price) * parseFloat(num);
            total_num += parseFloat(num);
        } else {
            select_all = 0;
        }
    });
    total_price = total_price.toFixed(2);
    $("#total_price").text(total_price);
    if (select_all == 1) {
        $("#isall").addClass("bg1");
    } else {
        $("#isall").removeClass("bg1");
    }
}
/**
 *支付倒计时
 * @param endDateStr
 * @constructor
 */
var TimeDown = function (endDateStr) {
    //结束时间
    var endDate = new Date(endDateStr.replace(/-/g, "/"));
    //当前时间
    var nowDate = new Date();
    //相差的总秒数
    var time = parseInt((endDate - nowDate) / 1000);
    if (time <= 0) {
        var html = ""
        html + "00时00分00秒";
        // $(".day").html("00");
        // $(".hour").html("00");
        // $(".min").html("00");
        // $(".sec").html("00");

        $(".time").html(html);
    } else {
        // 天数
        //var date = Math.floor(time/24/60/60);  //1
        //if(date<10){
        //    date = "0"+ date;
        //}
        // 小时
        var hour = Math.floor(time / 60 / 60); //3
        if (hour < 10) {
            hour = "0" + hour;
        }
        // 分钟
        var minute = Math.floor(time % (60 * 60) / 60);   //46
        if (minute < 10) {
            minute = "0" + minute;
        }
        // 秒
        var second = Math.floor(time % (60 * 60) % 60);   //40
        if (second < 10) {
            second = "0" + second;
        }
        var html = ""
        html += hour + "时" + minute + "分" + second + "秒";
        // $(".day").html(date);
        // $(".hour").html(hour);
        // $(".min").html(minute);
        // $(".sec").html(second);
        $(".time").html(html);

        //延迟一秒执行自己
        setTimeout(function () {
            TimeDown(endDateStr);
        }, 1000);
    }
}
