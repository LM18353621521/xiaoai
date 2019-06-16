/**
 * Created by Lu on 2018/10/21.
 */
var order_obj = {
    /**
     * 我的订单
     */
    order_list: function () {
        //去详情
        $('.lists').on('click', '.order-detail', function () {
            if (can_click == 0) {
                return false
            }
            can_click = 0;
            var order_id = $(this).parents('.detail_do').attr('order_id');
            window.location.href = "/Home/Order/orderdetail/order_id/" + order_id;
        });
        //去评价
        $('.lists').on('click', '.comment_do', function (e) {
            e.stopPropagation();
            if (can_click == 0) {
                return false
            }
            can_click = 0;
            var order_id = $(this).parents('.detail_do').attr('order_id');
            window.location.href = "/Home/Order/comment/order_id/" + order_id;
        });

        //去支付
        $('.lists').on('click', '.pay_do', function (e) {
            e.stopPropagation();
            if (can_click == 0) {
                return false
            }
            can_click = 0;
            var order_id = $(this).parents('.detail_do').attr('order_id');
            window.location.href = "/Home/Order/settlement/order_id/" + order_id;
        });

        //查看物流
        $('.lists').on('click', '.express_do', function (e) {
            e.stopPropagation();
            if (can_click == 0) {
                return false
            }
            can_click = 0;
            var order_id = $(this).parents('.detail_do').attr('order_id');
            window.location.href = "/Home/Order/wuliu/order_id/" + order_id;
        });
        //取消订单
        $('.lists').on('click', '.cancel_do', function (e) {
            e.stopPropagation();
            if (can_click == 0) {
                return false
            }
            var order_id = $(this).parents('.detail_do').attr('order_id');
            $.confirm("确定取消该订单吗？", function () {
                //点击确认后的回调函数
                $.post('/Home/Order/order_cancel', {order_id: order_id}, function (data) {
                    if (data.ret == 1) {
                        layer.msg(data.msg,{icon:1},function (e) {
                            window.location.reload();
                        })
                    } else {
                        layer.msg(data.msg,{icon:2})
                        can_click = 1;
                    }
                })
            }, function () {
                can_click = 1;
                //点击取消后的回调函数
            });
        });


        //订单确认收货
        $('.lists').on('click', '.confirm_do', function (e) {
            e.stopPropagation();
            if (can_click == 0) {
                return false
            }
            var order_id = $(this).parents('.detail_do').attr('order_id');
            layer.confirm("确定已经收货了吗？", function () {
                //点击确认后的回调函数
                $.post('/Home/Order/order_confirm', {order_id: order_id}, function (data) {
                    if (data.ret == 1) {
                        layer.msg(data.msg, {icon: 1}, function (e) {
                            window.location.reload();
                        })
                    } else {
                        layer.msg(data.msg, {icon: 2})
                        can_click = 1;
                    }
                });
            }, function () {
                can_click = 1;
                //点击取消后的回调函数
            });
        });

        //申请退款
        $('.lists').on('click', '.refund_do', function (e) {
            e.stopPropagation();
            if (can_click == 0) {
                return false
            }
            var order_id = $(this).parents('.detail_do').attr('order_id');
            layer.confirm("确定申请退款吗？", function () {
                //点击确认后的回调函数
                $.post('/mobile/Order/order_refund', {order_id: order_id}, function (data) {
                    if (data.ret == 1) {
                        layer.msg(data.msg,{icon:1},function () {
                            window.location.reload();
                        })
                    } else {
                        layer.msg(data.msg,{icon:2})
                        can_click = 1;
                    }
                })
            }, function () {
                can_click = 1;
                //点击取消后的回调函数
            });
        });

        //删除订单
        $('.lists').on('click', '.del_do', function (e) {
            e.stopPropagation();
            if (can_click == 0) {
                return false
            }
            var order_id = $(this).parents('.detail_do').attr('order_id');
            layer.confirm("确定删除该订单吗？", function () {
                //点击确认后的回调函数
                $.post('/Home/Order/order_del', {order_id: order_id}, function (data) {
                    if (data.ret == 1) {
                        layer.msg(data.msg,{icon:1},function (e) {
                            window.location.reload();
                        })
                    } else {
                        layer.msg(data.msg,{icon:2})
                        can_click = 1;
                    }
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

        //去支付
        $('.ss').on('click', '.pay_do', function (e) {
            if (can_click == 0) {
                return false
            }
            can_click = 0;
            var order_id = $('input[name=order_id]').val();
            window.location.href = "/Home/Order/settlement/order_id/" + order_id;
        });


        //查看物流
        $('#ss').on('click', '.comment_do', function (e) {
            var order_id = $('input[name=order_id]').val();
            window.location.href = "/Home/Order/comment/order_id/" + order_id;
        });

        //取消订单
        $('#ss').on('click', '.cancel_do', function (e) {
            e.stopPropagation();
            if (can_click == 0) {
                return false
            }
            var order_id = $('input[name=order_id]').val();
            layer.confirm("确认要取消吗？", {
                btn: ['确定', '放弃'] //按钮
            }, function () {
                $.post('/Home/Order/order_cancel', {order_id: order_id}, function (data) {
                    if (data.ret == 1) {
                        layer.msg(data.msg,{icon:1},function () {
                            window.location.reload();
                            can_click = 1;
                        })
                    } else {
                       layer.msg(data.msg,{icon:2})
                        can_click = 1;
                    }
                })
            }, function () {
                can_click = 1;
            });
        });

        //删除订单
        $('#ss').on('click', '.del_do', function (e) {
            e.stopPropagation();
            if (can_click == 0) {
                return false
            }
            var order_id = $('input[name=order_id]').val();
            layer.confirm("确认要删除吗？", {
                btn: ['确定', '放弃'] //按钮
            }, function () {
                //点击确认后的回调函数
                $.post('/Home/Order/order_del', {order_id: order_id}, function (data) {
                    if (data.ret == 1) {
                        layer.msg(data.msg,{icon:1},function () {
                            window.location.href=document.referrer;
                            can_click = 1;
                        })
                    } else {
                        layer.msg(data.msg,{icon:2})
                    }
                })
            }, function () {
                can_click = 1;
                //点击取消后的回调函数
            });
        });

        //申请退款
        $('#ss').on('click', '.refund_do', function (e) {
            e.stopPropagation();
            var order_id = $('input[name=order_id]').val();
            layer.confirm("确定申请退款吗？", function () {
                if (can_click == 0) {
                    return false
                }
                can_click=0;
                //点击确认后的回调函数
                $.post('/mobile/Order/order_refund', {order_id: order_id}, function (data) {
                    if (data.ret == 1) {
                        layer.msg(data.msg,{icon:1},function () {
                            window.history.go(-1);
                            can_click = 1;
                        })
                    } else {
                        layer.msg(data.msg,{icon:2})
                        can_click = 1;
                    }
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
            $.post('/Home/Order/comment', data, function (data) {
                can_click = 1;
                if (data.ret == 1) {
                    layer.msg(data.msg,{icon:1,time:1500},function () {
                        window.history.go(-1);
                        can_click = 1;
                    })
                } else {
                    layer.msg(data.msg,{icon:2})
                    can_click = 1;
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
