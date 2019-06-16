/**
 * Created by Lu on 2018/10/21.
 */
var home_obj = {
    /**
     * 首页
     */
    index: function () {

        /**
         * 获取验证码
         */
        can_get_code = 1;
        $("#get_code").click(function () {
            var mobilephone = $("#reg_username").val();
            if (mobilephone == '') {
                layer.msg("手机号码不能为空",{icon:2});
                return false;
            }
            if (can_get_code != 1) {
                return false;
            }
            can_get_code = 0;
            $.post('/Home/Api/sendSmsCode', {mobile: mobilephone,type:2}, function (data) {
                if (data.ret == 1) {
                    //发送成功
                    time = 60;//秒
                    countdown = time;
                    var setSmsTime = function () {
                        if (countdown == 0) {
                            can_get_code = 1;
                            $('#get_code').css('background', '#b730b0').text('获取验证码');
                            countdown = time;
                            clearInterval(aa);
                        } else {
                            $('#get_code').css('background', '#ccc').text(countdown + 's');
                            countdown--;
                        }
                    }
                    var aa = setInterval(function () {
                        setSmsTime()
                    }, 1000);
                } else {
                    can_get_code = 1;
                    layer.msg(data.msg,{icon:2});
                    return false;
                }
            }, 'json');
        });

        $(".reg_do").click(function () {
            var username = $("#reg_username").val();
            var code = $("#reg_code").val();
            var password = $("#reg_password").val();
            if(username==""){
                layer.msg("请输入账号",{icon:2});
                return false;
            }
            if(code==""){
                layer.msg("请输入验证码",{icon:2});
                return false;
            }
            if(password==""){
                layer.msg("请输入密码",{icon:2});
                return false;
            }
            var data = $('#form_reg').serialize();
            $.post("/Home/Login/register", data, function (data) {
                if (data.ret == 1) {
                    layer.msg(data.msg,{icon:1},function () {
                        window.location.reload();
                    });
                } else {
                    layer.msg(data.msg,{icon:2});
                }
            });
        });

        return;
        load_obj.load_ajax($("nav"), 1, function (data) {
            callback(data);
        });
        load_obj.load_init($('nav'), 0, function (data) {
            callback(data);
        });
        var callback = function (data) {
            console.log(data);
            //if (data.attach.total == 0) {
            //    $(".nodata").show();
            //}
        };
    },
    /**
     * 搜索历史
     */
    search: function () {
        $('.clear_serach').click(function () {
            $.post("/Mobile/Home/clear_keyword", {}, function (data) {
                var collect_num = $('#collect_num').text();
                can_click = 1;
                if (data.ret == 1) {
                    $.toast(data.msg);
                    $('.tuop1').remove();
                    $('.clear_serach').remove();
                } else {
                    $.toast(data.msg, 'cancel');
                }
            });
        })
    },
    /**
     * 分类页
     */
    types: function () {
        $(".menuboxp1").click(function () {
            $(".menuboxp1").removeClass("leftsel");
            $(this).addClass("leftsel");
            var pid = $(this).attr('cate_id');
            $('input[name=pid]').val(pid);
            $('input[name=page]').val(1);
            can_load = 1;
            load_obj.load_ajax($("nav"), 1, function (data) {
                callback(data);
            });
        });
        load_obj.load_ajax($("nav"), 1, function (data) {
            callback(data);
        });
        load_obj.load_init($('nav'), 0, function (data) {
            callback(data);
        });
        var callback = function (data) {
            console.log(data);
            var ad_img = data.attach.categoryParent.ad_img;
            $('.ad').attr('src', ad_img);
            //if (data.attach.total == 0) {
            //    $(".nodata").show();
            //}
        };
    },
    /**
     * 商品列表
     */
    goodlist: function () {
        //搜索
        $('#keyword').bind('keyup', function (event) {
            if (event.keyCode == "13") {
                $(this).blur();
                var keyword = $(this).val();
                var category_id = $('input[name=category_id]').val();
                window.location.href = "/mobile/home/goodlist/keyword/" + keyword + "/category_id/" + category_id;
                return false;
                $('input[name=keyword]').val(keyword);
                //回车执行查询
                can_load = 1;
                load_obj.load_ajax($(".lists"), 1, function (data) {
                    callback(data);
                });
            }
        });

        $(".bt2").click(function () {
            $(".bt2").removeClass("color01");
            $(this).addClass("color01");
            var sort = $(this).attr('sort');
            var asc = sort == 'price' ? 'asc' : 'desc';
            $('input[name=sort]').val(sort);
            $('input[name=asc]').val(asc);
            $('input[name=page]').val(1);
            can_load = 1;
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
            //if (data.attach.total == 0) {
            //    $(".nodata").show();
            //}
        };
    },

    /**
     * 开心一刻
     */
    happy: function () {
        //点击切换
        $(".bt2").click(function () {
            if (can_click == 0) {
                return false
            }
            can_click = 0;
            $(".bt2").removeClass("color01");
            $(this).addClass("color01");
            var _index = $(this).parent().index();
            //$(".tab-content-item").eq(_index).show().siblings(".tab-content-item").hide();
            var type = $(this).attr('type');
            $('input[name=type]').val(type);
            can_load = 1;
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
     * 重载商品数据
     */
    initGoodsPrice: function () {
        var goods_spec_arr = [];
        var goods_num = $('#goods_num').text();
        goods_num = parseInt(goods_num);

        $(".goods_spec_list .active").each(function () {
            var goods_spec = $(this).attr('goods_spec');
            goods_spec_arr.push(goods_spec);
        });

        show_cur_spec();
        console.log('goods_spec_arr', goods_spec_arr);

        var spec_key = "";
        if (goods_spec_arr.length > 0) {
            var spec_key = goods_spec_arr.sort(sortNumber).join('_'); //排序后组合成 key
            item_id = goods_spec_price[spec_key]['item_id'];
            stock = goods_spec_price[spec_key]['store_count'];
            price = goods_spec_price[spec_key]['price'];
            if (stock <= 0) {
                $("#goods_num").text(0);
                $(".goods_num").text(0);
            }

            $("#cur_stock").text(stock);
            $("#cur_price").text(price);
            // $("#cur_spec").text(goods_spec_price[spec_key]['key_name']);
        }

        console.log('spec_key', spec_key);
        console.log(item_id);
        console.log(stock);
        console.log(price);
        var data = {
            goods_id: goods_id,
            item_id: item_id,
            goods_num: goods_num,
        }
        $.post("/Home/Index/activity", data, function (data) {
            console.log(data);
            can_click = 1;
            var goodsInfo = data.data.goods;
            if (data.ret == 1) {
                $("#cur_stock").text(goodsInfo.stock);
                if (goodsInfo.stock <= 0) {
                    $("#goods_num").text(0);
                    $(".goods_num").text(0);
                }
                if(goodsInfo.prom_type==1){
                    // 倒计时
                    var intDiff = parseInt(goodsInfo.intDiff);//倒计时总秒数量
                    timer(intDiff);
                    $("#activity_show").show();
                    $(".old_price").find('em').text(goodsInfo.old_price);
                    $(".old_price").show();
                    prom_type=goodsInfo.prom_type;
                    buy_limit=goodsInfo.buy_limit;
                }


                $("#cur_price").text(goodsInfo.price);
            } else {
                return false;
            }
        });

    },

    /**
     * 详情
     */
    detail: function () {
        home_obj.initGoodsPrice();
        // 加
        $(".swiper-btn1").click(function () {
            var num = parseInt($("#goods_num").text());
            num++;
            if (num > stock) {
                layer.msg('库存不足，剩余' + stock,{icon:2});
                // $.toast('库存不足，剩余' + stock, "text");
                num = stock;
            }
            $("#goods_num").text(num)
        });
        // 减
        $(".swiper-btn2").click(function () {
            var num = parseInt($("#goods_num").text());
            if (num == 1) {
            } else {
                num--;
                $("#goods_num").text(num)
            }
        });

        // 选择套餐
        $(".taocan-item").click(function () {
            if (can_click == 0) {
                return false
            }
            can_click = 0;
            $(this).addClass("active").siblings(".taocan-item").removeClass("active");
            home_obj.initGoodsPrice();
        });

        //收藏
        $('.collect').click(function () {
            if (can_click == 0) {
                return false
            }
            can_click = 0;
            var collect_type = has_collect == 1 ? 2 : 1;
            var data = {
                goods_id: goods_id,
                type: collect_type
            }
            $.post("/Home/Home/collect_do", data, function (data) {
                var collect_num = $('#collect_num').text();
                can_click = 1;
                if (data.ret == 1) {
                    //$.toast(data.msg);
                    if (collect_type == 1) {
                        has_collect = 1;
                        collect_num = parseInt(collect_num) + 1;
                        layer.msg('收藏成功',{icon:1});
                        $('#collect_num').text(collect_num);
                        $('.collect').find("img").attr('src', '/static/home/common/images/icon_like_02.png');
                    } else {
                        has_collect = 0;
                        collect_num = parseInt(collect_num) - 1;
                        $('#collect_num').text(collect_num);
                        layer.msg('收藏已取消',{icon:1});
                        $('.collect').find("img").attr('src', '/static/home/common/images/icon_like_01.png');
                    }
                } else if (data.ret == 2) {
                    layer.confirm(data.msg, {
                        btn: ['确定','取消'] //按钮
                    }, function(){
                        window.location.href = "/Home/Index/index";
                    }, function(){
                    });
                    // $.confirm(data.msg, function () {
                    //     window.location.href = "/Mobile/user/login";
                    // }, function () {
                    //     //取消操作
                    // });
                } else {
                    layer.msg(data.msg,{icon:2});
                    // $.toptip(data.msg, 'error');
                }
            });
        })

        //领券
        $('.getcoupon').click(function () {
            if (can_click == 0) {
                return false
            }
            can_click = 0;
            var that = $(this);
            var coupon_id = $(this).attr('coupon_id');
            var data = {
                id: coupon_id,
            }
            $.post("/Home/Home/getcoupon", data, function (data) {
                can_click = 1;
                if (data.ret == 1) {
                    layer.msg(data.msg,{icon:1});
                    // $.toptip(data.msg, 'success');
                    that.text('已领取');
                    that.parents('.hui-box').find('.huibg').attr('src', '/static/mobile/home/images/hui-bga.png');
                } else if (data.ret == 2) {
                    layer.confirm(data.msg, {
                        btn: ['确定','取消'] //按钮
                    }, function(){
                        window.location.href = "/Home/Index/index";
                    }, function(){
                    });
                    // $.confirm(data.msg, function () {
                    //     window.location.href = "/Mobile/user/login";
                    // }, function () {
                    //     //取消操作
                    // });
                } else {
                    layer.msg(data.msg,{icon:2});
                    // $.toptip(data.msg, 'error');
                }
            });
        })

        //加入购物车
        $('#cart_add').click(function () {
            var number = parseInt($("#goods_num").text());
            if (number <= 0) {
                return false
            }
            if (can_click == 0) {
                return false
            }
            can_click = 0;
            var that = $(this);

            var data = {
                goods_id: goods_id,
                item_id: item_id,
                number: number,
                type: 1,
            }
            $.post("/Home/Cart/cart_update", data, function (data) {
                console.log(data)
                can_click = 1;
                if (data.ret == 1) {
                    layer.msg(data.msg,{icon:1});
                    // $.toast(data.msg, 'text');
                } else if (data.ret == 2) {
                    layer.confirm(data.msg, {
                        btn: ['确定','取消'] //按钮
                    }, function(index){
                        layer.close(index);
                        $(".login-layer").hide();
                        $(".login-register").show();
                        // window.location.href = "/Home/Index/index";
                    }, function(){
                    });
                    // $.confirm(data.msg, function () {
                    //     window.location.href = "/Mobile/user/login";
                    // }, function () {
                    //     //取消操作
                    // });
                } else {
                    layer.msg(data.msg,{icon:2});
                    // $.toptip(data.msg, 'error');
                }
            });
        })
        //立即购买
        $('#buy_now').click(function () {
            var number = parseInt($("#goods_num").text());
            if (number <= 0) {
                return false
            }
            if (can_click == 0) {
                return false
            }
            if(prom_type>0){
                if(number>buy_limit){
                    layer.msg("本次活动每人限购："+buy_limit+"件",{icon:2});
                    return false;
                }
            }


            can_click = 0;
            var that = $(this);
            $.post("/Home/Api/check_login", {}, function (data) {
                can_click = 1;
                if (data.ret == 1) {
                    window.location.href = "/Home/Cart/confirmOrder/goods_id/" + goods_id + "/item_id/" + item_id + "/buy_num/" + number + "/action/buy_now/cart_ids/0";
                } else {
                    layer.confirm(data.msg, {
                        btn: ['确定','取消'] //按钮
                    }, function(index){
                        layer.close(index);
                        $(".login-layer").hide();
                        $(".login-register").show();
                        // window.location.href = "/Home/Index/index";
                        // window.location.href = "/Home/user/login";
                    }, function(){
                    });
                    // $.confirm(data.msg, function () {
                    //     window.location.href = "/Mobile/user/login";
                    // }, function () {
                    //     //取消操作
                    // });
                }

            });
        });


        $('.discuss-item').click(function () {
            //$(this).parents('.discuss-wrapper').find('.discuss-item').removeClass('.discuss-active');
            $(this).siblings('.discuss-item').removeClass('discuss-active');
            $(this).addClass('discuss-active');
            var star = $('.discuss-active').attr('star');
            $('input[name=star]').val(star);
            $('input[name=page]').val(1);
            can_load = 1;
            load_obj.load_ajax($(".lists"), 1, function (data) {
                callback(data);
            });
        });

        //加载评论
        // load_obj.load_ajax($(".lists"), 1, function (data) {
        //     callback(data);
        // });
        // load_obj.load_init($('.lists'), 0, function (data) {
        //     callback(data);
        // });
        // var callback = function (data) {
        //     console.log(data);
        //     //if (data.attach.total == 0) {
        //     //    $(".nodata").show();
        //     //}
        // };


    },
    /**
     * 确认订单
     */
    orderconfirm: function () {
        ajax_addresslist();
        count_pay_money(goods_price, express_fee, coupon_money);
        /**
         * 去支付
         */
        $("#pay_do").click(function () {
            if (can_click == 0) {
                return false
            }
            var address_id = $('input[name=address_id]').val();
            if (!address_id) {
                alert('请选择收货地址');
                return false;
            }
            var data = $('#order_info').serialize();
            $.post('/mobile/Order/orderadd', data, function (data) {
                can_click = 1;
                console.log(data);
                if (data.ret == 1) {
                    if (data.url) {
                        window.location.href = data.url;
                    }
                } else {
                    alert(data.msg);
                    return false;
                }
            }, 'json');

        });

        // f1-确认优惠券
        $(".sure_coupon").click(function () {
            var huiTxt = $('.coupon_list').find('.color-sel').text();
            var coupon_id = $('.coupon_list').find('.color-sel').attr('coupon_id');
            var coupon_money = $('.coupon_list').find('.color-sel').attr('coupon_money');
            $(".hui span").text(huiTxt);
            $('input[name=coupon_id]').val(coupon_id);
            $('#coupon_money').text(coupon_money);
            coupon_money = coupon_money;
            $(".f1").hide();
            $(".wrapper").removeClass("hidecontainer");
            count_pay_money(goods_price, express_fee, coupon_money);
        });

        //选择地址
        $('#addrList').on('click', '.addritem', function (e) {
            var id = $(this).data('id');
            var linkman = $(this).find('.linkman').text();
            var linktel = $(this).find('.linktel').text();
            var address = $(this).find('.address').text();
            $.post('/mobile/Home/count_repress_fee', {id: id}, function (data) {
                can_click = 1;
                if (data.ret == 1) {
                    express_fee = data.data;
                    $('#express_fee').text(express_fee);
                    count_pay_money(goods_price, express_fee, coupon_money);
                } else {
                    alert(data.msg);
                    return false;
                }
            }, 'json');
            $('input[name=address_id]').val(id);
            var html = "";
            html += "<div class='addrs-box'>";
            html += "<div class='addrs-name'>收货人：<a class='linkman'>" + linkman + "</a><span class='linktel'>" + linktel + "</span></div>";
            html += "<div class='addrs-info'>收货地址：<span class='address'>" + address + "</span></div>";
            html += "</div>";
            console.log(html);
            $('.order-addrs').find('div').remove();
            $('.order-addrs').append(html);
            $(".f4").hide();
            $(".wrapper").removeClass("hidecontainer");
        });

        //保存地址
        $(".addnewaddr").click(function () {
            if (can_click == 0) {
                return false
            }
            var linkman = $('input[name=linkman]').val();
            var linktel = $('input[name=linktel]').val();
            var region = $('input[name=region]').val();
            var address = $('input[name=address]').val();

            if (linkman == '') {
                alert('请输入收货人');
                return false;
            }
            if (linktel == '') {
                alert('请输入手机号');
                return false;
            }
            if (linkman == '') {
                alert('请选择地区');
                return false;
            }
            if (linkman == '') {
                alert('请输入详细地址');
                return false;
            }
            can_click = 0;
            var data = $('#addInfo').serialize();
            $.post('/mobile/Vip/addredit', data, function (data) {
                can_click = 1;
                if (data.ret == 1) {
                    $(".f3").hide();
                    $(".f4").show();
                    $.toast(data.msg);
                    ajax_addresslist();
                } else {
                    alert(data.msg);
                    return false;
                }
            }, 'json');

        });
        //显示添加地址层
        $(".showf3").click(function () {
            $('.fuceng').hide();
            $('.f3').show();
        })
    },

    /**
     * 分享商品
     */
    distributgoods: function () {
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
    }


}

var sortNumber = function (a, b) {
    return a - b;
}

var initOrderConfirm = function () {
    var total_money
}


var ajax_addresslist = function () {
    $.post("/Mobile/Home/addresslist", {}, function (data) {
        can_click = 1;
        if (data.ret == 1) {
            $('#addrList').html(data.data);
        } else {
            $.toptip(data.msg, 'error');
        }
    });
}

var count_pay_money = function (goods_price, express_fee, coupon_money) {
    var goods_price = parseFloat(goods_price);
    var express_fee = parseFloat(express_fee);
    var coupon_money = parseFloat(coupon_money);
    ;
    var pay_money = goods_price + express_fee - coupon_money;
    $('#pay_money').text(pay_money);
}
//将选择的属性添加到已选
var show_cur_spec = function () {
    var title = '';
    $('.spec_list').find('.fftuop11 ').each(function (i, o) {   //获取已选择的属性，规格
        if ($(o).hasClass('ffsuel')) {
            title += $(o).attr('title') + '&nbsp;&nbsp;';
        }
    });
    var goods_num = $('#goods_num').text();
    $('.goods_num').text(goods_num);
    var sel = title + '&nbsp;&nbsp;';
    $('.cur_spec').html(sel);
}
/**
 * Created by Administrator on 2019/4/9.
 */
