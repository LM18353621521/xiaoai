can_click = 1;
can_get_code = 1;
var vip_obj = {
    //**********************************源码*********************************************
    /**
     * 首页
     */
    index: function () {
        //修改信息
        $("#edit_info_do").click(function (e) {
            if (can_click == 0) {
                return false
            }
            var sex= $('input[name=sex]').val();
            var nickname= $('input[name=nickname]').val();
            if(sex==""||sex==null){
                layer.msg("请选择性别", {icon: 2});
                return false;
            }
            if(nickname==""||nickname==null){
                layer.msg("请输入昵称", {icon: 2});
                return false;
            }
            can_click=0;
            var data = $("#edit_info").serialize();
            $.post("/home/Vip/myinfo", data, function (data) {
                if (data.ret == 1) {
                    layer.msg(data.msg, {icon: 1}, function (e) {
                        window.location.reload();
                    })
                    can_click = 1;
                } else {
                    layer.msg(data.msg, {icon: 2})
                    can_click = 1;
                    return false;
                }
            }, 'json');

        })
        
        //qq登录
        $('.edit_password_btn').click(function () {
            if (can_click == 0) {
                return false
            }
            var password_old = $('input[name=password_old]').val();
            var password = $.trim($('input[name=password]').val());
            var passwords = $('input[name=passwords]').val();
            if (password_old == "") {
                layer.msg("请输入原密码", {icon: 2});
                return false;
            }
            if (password == "") {
                layer.msg("请输入新密码", {icon: 2});
                return false;
            }
            if (password.length < 6) {
                layer.msg("新密码长度不能小于6位", {icon: 2});
                return false;
            }
            if (password != passwords) {
                layer.msg("两次密码输入一致", {icon: 2,time:1});
                return false;
            }

            var url = $("#edit_password_form").attr('action');
            var data = $("#edit_password_form").serialize();
            $.post(url, data, function (data) {
                if (data.ret == 1) {
                    layer.msg(data.msg, {icon: 1}, function (e) {
                        window.location.reload();
                    })
                    can_click = 1;
                } else {
                    layer.msg(data.msg, {icon: 2})
                    can_click = 1;
                    return false;
                }
            }, 'json');
        });

        //qq登录
        $('#qq_login').click(function () {
            window.location.href = "/mobile/Login/qq_login";
        });
        //获取验证码
        $("#bind_do").click(function () {
            var mobile = $("#bind_mobile").val();
            var code = $("#bind_code").val();
            if (mobile == '') {
                alert("手机号码不能为空");
                return false;
            }
            if (!(/^1(3|4|5|7|8|9)\d{9}$/.test(mobile))) {
                alert("手机号码有误，请重新输入");
                return false;
            }
            if (code == '') {
                alert("请输入您收到的验证码");
                return false;
            }
            if (can_click != 1) {
                return false;
            }
            can_click = 0;
            $.post('/mobile/Vip/bind_mobile', {mobile: mobile, code: code}, function (data) {
                if (data.ret == 1) {
                    can_click = 1;
                    $(".fuceng").hide();
                    $.toast(data.msg, 1500, function () {
                        window.location.reload();
                    });
                } else {
                    can_click = 1;
                    alert(data.msg);
                    return false;
                }
            }, 'json');
        });

        $.post('/mobile/Vip/order_status', {}, function (data) {
            var order_status = data.data;
            for (var i = 0; i < order_status.length; i++) {
                if (order_status[i] > 0) {
                    $('.order_num' + i).text(order_status[i]).show();
                } else {
                    $('.order_num' + i).text(order_status[i]).hide();
                }
            }
        });
    },

    myinfo: function () {
        $(".select").find("span").text($("select").find("option:selected").text());

        $("select").change(function () {
            $(this).parents("div").children("span").text($(this).find("option:selected").text());
        })


        form_obj.form_submit(function (data) {
            callback(data);
        });
        var callback = function (data) {
            window.location.href = data.url;
        };
    },

    /**
     * 我的关注
     */
    mycollect: function () {
        //去详情
        $('.lists').on('click', '.detail_do', function (e) {
            var that = $(this);
            if (can_click == 0) {
                return false
            }
            can_click = 0;
            var id = $(this).attr('cid');
            var goods_id = $(this).attr('goods_id');
            $.post('/Home/Vip/check_goods', {goods_id: goods_id}, function (data) {
                if (data.ret == 1) {
                    window.location.href = "/Home/Index/detail/goods_id/" + goods_id;
                } else {
                    layer.confirm('该商品已下架，是否删除收藏？', function () {
                        $.post('/Home/Vip/collect_del', {id: id}, function (data) {
                            if (data.ret == 1) {
                                layer.msg(data.msg, {icon: 1}, function (e) {
                                    window.location.reload();
                                })
                                // that.parents('.detail_do').remove();
                                can_click = 1;
                            } else {
                                layer.msg(data.msg, {icon: 2})
                                can_click = 1;
                            }
                        })
                    }, function () {
                        //取消操作
                    });
                }
                can_click = 1;
            })
        });

        $('.lists').on('click', '.del', function (e) {
            e.stopPropagation();
            var that = $(this);
            if (can_click == 0) {
                return false
            }
            can_click = 0;
            var id = $(this).parents('.detail_do').attr('cid');
            var goods_id = $(this).parents('.detail_do').attr('goods_id');
            $.post('/Home/Vip/collect_del', {id: id}, function (data) {
                if (data.ret == 1) {
                    layer.msg(data.msg, {icon: 1}, function (e) {
                        window.location.reload();
                        can_click = 1;
                    })
                } else {
                    layer.msg(data.msg, {icon: 2})
                    can_click = 1;
                }
            });
        });


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
     * 我的优惠券
     */
    mycoupon: function () {
        //去详情
        $('#coupon_list').on('click', ".get_coupon",function () {
            var that = $(this);
            var id = $(this).attr('coupon_id');
            if (can_click == 0) {
                return false
            }
            can_click = 0;
            $.post('/Home/Vip/getcoupon', {id: id}, function (data) {
                if (data.ret == 1) {
                    layer.msg(data.msg,{icon:1,time:1500},function (e) {
                    })
                    that.parents('li').removeClass('coupon_useing').addClass('coupon_used');
                    that.text('已领取');
                    that.removeClass('get_coupon');
                    can_click = 1;
                } else {
                    layer.msg(data.msg,{icon:2});
                    can_click = 1;
                }
            });

        });


    },

    feedback: function () {
        form_obj.form_submit(function (data) {
            callback(data);
        });
        var callback = function (data) {
            window.location.href = data.url;
        };
    },

    register: function () {
        can_get_code = 1;
        $("#get_code").click(function () {
            var mobilephone = $("input[name=mobilephone]").val();
            if (mobilephone == '') {
                alert("手机号码不能为空");
                return false;
            }
            if (can_get_code != 1) {
                return false;
            }
            can_get_code = 0;
            $.post('/wechat.php/Vip/createsms', {mobilephone: mobilephone}, function (data) {
                if (data.ret == 1) {
                    //发送成功
                    time = 60;//秒
                    countdown = time;
                    var setSmsTime = function () {
                        if (countdown == 0) {
                            can_get_code = 1;
                            $('#get_code').css('background', '#ff5500').text('获取验证码');
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
                    alert(data.msg);
                    return false;
                }
            }, 'json');
        });

        form_obj.form_submit(function (data) {
            callback(data);
        });
        var callback = function (data) {
            window.location.href = data.url;
        };
    },

    addresslist: function () {
        // 编辑地址
        $("#address_list").on('click', '.address_edit', function (e) {
            if (can_click == 0) {
                return false
            }
            e.stopPropagation();
            var that = $(this);
            var id = $(this).parents('li').attr('address_id');
            can_click = 0;
            $.get("/Home/Vip/addredit", {address_id: id}, function (data) {
                $("#form_address_edit").empty().html(data);
                $(".address_edit_layer").show();
                can_click = 1;
            });
        })
        //保存新增地址
        $(document).on("click", ".save_addr_do", function () {
            var province = $("input[name=province_id]").val();
            var city = $("input[name=city_id]").val();
            var district = $("input[name=district_id]").val();
            var address = $("input[name=address]").val();
            var linkman = $("input[name=linkman]").val();
            var linktel = $("input[name=linktel]").val();
            if (linkman == "") {
                layer.msg("请填写收货人", {icon: 2});
                return false;
            }
            if (linktel == "") {
                layer.msg("请填写手机号码", {icon: 2});
                return false;
            }
            if (province == "" || city == "" || district == "") {
                layer.msg("请选择所在区域", {icon: 2});
                return false;
            }
            if (address == "") {
                layer.msg("请填写详细地址", {icon: 2});
                return false;
            }

            var data = $("#form_address").serialize();
            $.post("/Home/Vip/address_add", data, function (data) {
                console.log(data);
                if (data.ret == 1) {
                    // $("#address_list").append(data.data);
                    window.location.reload();
                    hide_login_layer();
                    layer.msg("保存成功", {icon: 1});

                } else {
                    layer.msg(data.msg, {icon: 2});
                }
            });
        });
        $(document).on("click", ".save_addredit_do", function () {
            var province = $("#form_address_edit").find("#province").val();
            var city = $("#form_address_edit").find("#city").val();
            var district = $("#form_address_edit").find("#district").val();
            var address = $("#form_address_edit").find("input[name=address]").val();
            var linkman = $("#form_address_edit").find("input[name=linkman]").val();
            var linktel = $("#form_address_edit").find("input[name=linktel]").val();
            var id = $("#form_address_edit").find("input[name=id]").val();
            if (linkman == "") {
                layer.msg("请填写收货人", {icon: 2});
                return false;
            }
            if (linktel == "") {
                layer.msg("请填写手机号码", {icon: 2});
                return false;
            }
            if (province == "" || city == "" || district == "") {
                layer.msg("请选择所在区域", {icon: 2});
                return false;
            }
            if (address == "") {
                layer.msg("请填写详细地址", {icon: 2});
                return false;
            }
            var data = $("#form_address_edit").serialize();
            $.post("/Home/Vip/addredit", data, function (data) {
                console.log(data);
                if (data.ret == 1) {
                    // $("#address_list").find("#address"+id).html(data.data);
                    window.location.reload();
                    hide_login_layer();
                    layer.msg("保存成功", {icon: 1});
                } else {
                    layer.msg(data.msg, {icon: 2});
                }
            });
        })

        // 删除地址
        $("#address_list").on('click', '.address_del', function (e) {
            if (can_click == 0) {
                return false
            }
            e.stopPropagation();
            var that = $(this);
            var id = $(this).parents('li').attr('address_id');
            layer.confirm("确认要删除吗？", {
                btn: ['确定', '取消'] //按钮
            }, function () {
                can_click = 0;
                $.post("/Home/Vip/addressdel", {address_id: id}, function (data) {
                    console.log(data);
                    if (data.ret == 1) {
                        layer.msg("删除成功", {icon: 1});
                        that.parents("li").remove();
                    } else {
                        layer.msg(data.msg, {icon: 2});
                    }
                    can_click = 1;
                });
            }, function () {
            });
        })

        //设为默认地址
        $("#address_list").on('click', '.address_default', function (e) {
            $this = $(this);
            if (can_click == 0) {
                return false
            }
            can_click = 0;
            var id = $(this).parents('li').attr('address_id');
            $.post("/Home/Vip/addressdefault", {myaddress_id: id}, function (data) {
                if (data.ret == 1) {
                    layer.msg("设置成功", {icon: 1});
                    window.location.reload();
                    hide_login_layer();
                    can_click = 1;
                } else {
                    layer.msg(data.msg, {icon: 2});
                    can_click = 1;
                    return false;
                }
            });
        });
    },

    addredit: function () {
        form_obj.form_submit(function (data) {
            callback(data);
        });
        var callback = function (data) {
            setTimeout(function () {
                if (data.url) {
                    window.location.href = data.url;
                }
            }, 1500)
        };
    },

    register1_init: function () {
        can_get_code = 1;
        $("#get_code").click(function () {
            var mobilephone = $("input[name=mobilephone]").val();
            if (mobilephone == '') {
                alert("手机号码不能为空");
                return false;
            }
            if (can_get_code != 1) {
                return false;
            }
            can_get_code = 0;
            $.post('/wechat.php/Vip/createsms', {mobilephone: mobilephone}, function (data) {
                if (data.ret == 1) {
                    time = 60;//秒
                    countdown = time;
                    var setSmsTime = function () {
                        if (countdown == 0) {
                            can_click = 1;
                            $('#get_code').css({'color': '#ff5500', 'border': '1px #ff5500 solid'})
                                .text('获取验证码');
                            countdown = time;
                            clearInterval(aa);
                        } else {
                            $('#get_code').css({'color': '#4c4c4c', 'border': '1px #ccc solid'})
                                .text(countdown + 's');
                            countdown--;
                        }
                    }
                    var aa = setInterval(function () {
                        setSmsTime()
                    }, 1000);
                } else {
                    can_get_code = 1;
                    alert(data.msg);
                    return false;
                }
            }, 'json');
        });

        form_obj.form_submit(function (data) {
            callback(data);
        });
        var callback = function (data) {
            window.location.href = data.url;
        };
    },

    review_init: function () {
        $(".subbn1 span").height($(".subbn1 span:first").width());
        $(document).on("click", ".subbn1 span", function () {
            $(this).addClass("active").prevAll("span").addClass("active");
            $(this).nextAll("span").removeClass("active");
            var sel = $(this).index();
            $(this).parents(".parsub").find("input[type=hidden]").val(sel * 1 + 1);
        });

        $("footer").click(function () {
            image_obj.upload_image();
        });

        upload_success = function () {
            $.post('?s=Vip/review', $("form").serialize(), function (data) {
                if (data.ret == 1) {

                } else {
                    alert(data.msg);
                    return false;
                }
            });
        }

    },
}