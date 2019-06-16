/**
 * Created by Lu on 2018/10/21.
 */
var cart_obj = {
    /**
     * 首页
     */
    cart: function () {
        countCartPrice();
        //结算
        $('.check_do').click(function () {
            var buy_num = 0;

            $.each($('.cartList'), function (i, o) {
                if ($(this).find('.single-select').hasClass('active')) {
                    buy_num += 1;
                }
            });

            if (buy_num <= 0) {
                layer.msg("请选择购买商品", {icon: 2});
                return false;
            }
            window.location.href = "/Home/Cart/confirmOrder/goods_id/" + 0 + "/item_id/" + 0 + "/buy_num/" + 0 + "/action/buy_cart/cart_ids/0";
        });

        // 单选
        $(".single-select").click(function () {
            var len = $(".product-list li").length;
            var len1 = $(".single-select.active").length;
            var id = $(this).parents("li").attr('cart_id');
            if ($(this).attr("src") == "/static/home/common/images/no-select.png") {
                update_cart_select(1, 1, id);
                // 影响自己
                $(this).attr("src", "/static/home/common/images/selected.png");
                $(this).addClass("active");
                // 影响全选
                if (len == (len1 + 1)) {
                    $(".head-select img").attr("src", "/static/home/common/images/selected.png");
                    $(".head-select img").addClass("active");
                }
            } else {
                update_cart_select(1, 0, id);
                // 影响自己
                $(this).attr("src", "/static/home/common/images/no-select.png");
                $(this).removeClass("active");
                // 影响全选
                $(".head-select img").attr("src", "/static/home/common/images/no-select.png");
                $(".head-select img").removeClass("active");
            }
            countCartPrice();
        });
        // 全选
        $(".head-select").click(function () {
            if ($(".head-select img").attr("src") == "/static/home/common/images/no-select.png") {
                update_cart_select(2, 1, 0);
                $(".head-select img").attr("src", "/static/home/common/images/selected.png");
                $(".head-select img").addClass("active");
                $(".single-select").attr("src", "/static/home/common/images/selected.png");
                $(".single-select").addClass("active");
            } else {
                update_cart_select(2, 0, 0);
                $(".head-select img").attr("src", "/static/home/common/images/no-select.png");
                $(".head-select img").removeClass("active");
                $(".single-select").attr("src", "/static/home/common/images/no-select.png");
                $(".single-select").removeClass("active");
            }
            countCartPrice();
        });


        //删除
        $(".del").click(function () {
            var that = $(this);
            if (can_click == 0) {
                return false
            }
            var id = $(this).parents("li").attr('cart_id');
            can_click = 0;
            var data = {
                id: id,
                type: 1,
            };
            $.post("/Home/Cart/ajax_cart_del", data, function (data) {
                console.log(data);
                can_click = 1;
                if (data.ret == 1) {
                    that.parents("li").remove();
                    countCartPrice();
                } else {
                }
                can_click = 1;
            });

            layer.confirm("确认要删除吗？", {
                btn: ['确定', '取消'] //按钮
            }, function () {

            }, function () {
            });
        });

        $(".del_all").click(function (e) {
            e.stopPropagation();
            var that = $(this);
            if (can_click == 0) {
                return false
            }
            can_click = 0;
            var data = {
                id: 0,
                type: 2,
            };
            $.post("/Home/Cart/ajax_cart_del", data, function (data) {
                console.log(data);
                can_click = 1;
                if (data.ret == 1) {
                    $(".cartList").remove();
                    countCartPrice();
                } else {
                }
                can_click = 1;
            });
            layer.confirm("确认要删除吗？", {
                btn: ['确定', '取消'] //按钮
            }, function () {
            }, function () {
            });
        });

        // 点击减号
        $(".mit").click(function () {
            if (can_click == 0) {
                return false
            }
            var id = $(this).parents("li").attr('cart_id');
            var num = $(this).parents("li").find(".number").text();
            num = parseInt(num);
            if (num <= 1) {
                return false
            }
            can_click = 0;
            var data = {
                id: id,
                number: 1,
                type: 2,
            };
            $.post("/Home/Cart/ajax_cart_update", data, function (data) {
                console.log(data);
                can_click = 1;
                if (data.ret == 1) {
                    num = parseInt(num) - 1;
                    $("#num" + id).text(num);
                    countCartPrice();
                } else if (data.ret == 2) {
                } else {
                }
                can_click = 1;
            });
        })
        // 点击加号
        $(".plus").click(function () {
            if (can_click == 0) {
                return false
            }
            can_click = 0;
            var id = $(this).parents("li").attr('cart_id');
            var num = $(this).parents("li").find(".number").text();
            var data = {
                id: id,
                number: 1,
                type: 1,
            };
            $.post("/Home/Cart/ajax_cart_update", data, function (data) {
                console.log(data);
                can_click = 1;
                if (data.ret == 1) {
                    num = parseInt(num) + 1;
                    $("#num" + id).text(num);
                    countCartPrice();
                } else if (data.ret == 2) {
                } else {
                }
                can_click = 1;
            });
        });

        // 单选
        $(".add .bg").click(function () {
            $(this).toggleClass("bg1")

            if ($(this).next(".detail").find(".mid .list .lt div").hasClass("down")) {
                $(this).removeClass("bg1")
            }
            var las = $(".bg").length;
            var ras = $(".way .bg1").length;
            var no = $(".way .down").length;

            if (ras < (las - no)) {
                $(".all .ss").find(".allcheck").removeClass("bg1");
            } else {
                $(".all .ss").find(".allcheck").addClass("bg1");
            }
            countCartPrice();
        });

        // 全选
        $(".all .ss").click(function () {
            $(this).find(".allcheck").toggleClass("bg1")
            if ($(this).find(".allcheck").hasClass("bg1")) {
                $(".bg").each(function () {

                    if (!$(this).hasClass("bg1")) {
                        $(this).addClass("bg1")
                        $(this).find("input:checkbox").attr("checked", "true");
                    }
                    if ($(this).next(".detail").find(".mid .list .lt div").hasClass("down")) {
                        $(this).removeClass("bg1")
                        $(this).find("input:checkbox").attr("checked", "false")
                    }
                });
            } else {
                $(".bg").each(function () {
                    if ($(this).hasClass("bg1")) {
                        $(this).removeClass("bg1")
                        $(this).find("input:checkbox").attr("checked", "false")
                    }
                    if ($(this).next(".detail").find(".mid .list .lt div").hasClass("down")) {
                        $(this).removeClass("bg1")
                        $(this).find("input:checkbox").attr("checked", "false")
                    }
                })
            }
            countCartPrice();
        })
    },

    /**
     * 订单结算
     */
    confirmOrder: function () {
        count_pay_money(goods_price,express_fee,coupon_money);
        //选择优惠券
        $(".coupon-use").click(function (e) {
            var coupon_id = $(this).parents('li').attr('coupon_id');
            var money = $(this).parents('li').attr('money');
            var coupon_name = $(this).parents('li').attr('coupon_name');
            coupon_money=money;
            $('.coupon_money').text(coupon_money);
            $('input[name=coupon_id]').val(coupon_id);
            $('.coupon_sel').text("(已选择："+coupon_name+")");
            count_pay_money(goods_price,express_fee,coupon_money) ;
            $(".login-layer").hide();
        })
        //选择地址
        $("#address_list").on('click',"li",function () {
            $(".tianxie-name").removeClass("active");
            $(this).find(".tianxie-name").addClass("active");
            $(".tianxie-list li").removeClass("active");
            $(this).addClass("active");
            var id = $(this).attr('address_id');
            $.post('/Home/Cart/count_repress_fee', {id:id}, function (data) {
                can_click=1;
                if (data.ret == 1) {
                    express_fee=data.data;
                    $('.express_fee').text(express_fee);
                    count_pay_money(goods_price,express_fee,coupon_money) ;
                } else {
                    alert(data.msg);
                    return false;
                }
            }, 'json');
            $('input[name=address_id]').val(id);
        })
        // 编辑地址
        $("#address_list").on('click', '.address_edit', function (e) {
            $(".address_edit_layer").show();
            if (can_click == 0) {
                return false
            }
            e.stopPropagation();
            var that = $(this);
            var id = $(this).parents('li').attr('address_id');
            can_click = 0;
            $.get("/Home/Vip/addredit", {address_id: id}, function (data) {
                $("#form_address_edit").empty().html(data);
                can_click = 1;
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
            alert(id)
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
                    $("#address_list").append(data.data);
                    $("#form_address").empty();
                    $("#form_address_edit").empty();
                    $(".login-layer").hide();
                    layer.msg("保存成功", {icon: 1});

                } else {
                    layer.msg(data.msg, {icon: 2});
                }
            });
        })
        $(document).on("click", ".save_addredit_do", function () {
            var province = $("#form_address_edit").find("#province").val();
            var city = $("#form_address_edit").find("#city").val();
            var district =$("#form_address_edit").find("#district").val();
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
                    $("#address_list").find("#address"+id).html(data.data);
                    $("#form_address").empty();
                    $("#form_address_edit").empty();
                    $(".login-layer").hide();
                    layer.msg("保存成功", {icon: 1});
                } else {
                    layer.msg(data.msg, {icon: 2});
                }
            });
        })

        /**
         * 去支付
         */
        $("#pay_do").click(function () {
            if (can_click == 0) {
                return false
            }
            var address_id = $("#address_list").find('.active').attr('address_id');
            $('input[name=address_id]').val(address_id);
            var address_id = $('input[name=address_id]').val();
            if (!address_id) {
                alert('请选择收货地址');
                return false;
            }
            var data = $('#order_info').serialize();
            $.post('/Home/Order/orderadd', data, function (data) {
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
    }

}

var count_pay_money = function(goods_price,express_fee,coupon_money) {
    var goods_price = parseFloat(goods_price);
    var express_fee = parseFloat(express_fee);
    var coupon_money = parseFloat(coupon_money);
    var pay_money = goods_price + express_fee - coupon_money;
    $('#pay_money').text(pay_money.toFixed(2));
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
        var subtotal = parseFloat(price) * parseFloat(num);
        $(this).find("#subtotal" + id).text(subtotal);

        if ($(this).find('.single-select').hasClass('active')) {
            total_price += parseFloat(price) * parseFloat(num);
            total_num += parseFloat(num);
        } else {
            select_all = 0;
        }
    });
    total_price = total_price.toFixed(2);
    $("#total_price").text(total_price);
    $("#total_num").text(total_num);
    if (select_all == 1) {
        $(".head-select").find('img').addClass("active");
        $(".head-select").find('img').attr("src", "/static/home/common/images/selected.png");
    } else {
        $(".head-select").find('img').removeClass("active");
        $(".head-select").find('img').attr("src", "/static/home/common/images/no-select.png");
    }
}

var update_cart_select = function (type, select, id) {
    var data = {
        type: type,
        selected: select,
        id: id
    }
    $.post("/Home/Cart/cart_select", data, function (data) {
    });
}
