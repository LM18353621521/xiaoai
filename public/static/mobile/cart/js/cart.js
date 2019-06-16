/**
 * Created by Lu on 2018/10/21.
 */
var cart_obj = {
    /**
     * 首页
     */
    cart: function () {
        countCartPrice();

        $("#buy_now").click(function () {
            var buy_num = 0;
            $.each($('.cartList'), function (i, o) {
                var id = $(this).attr('cart_id');
                var price = $("#price" + id).text();
                var num = $("#num" + id).text();
                if ($(this).find('.bg').hasClass('bg1')) {
                    buy_num += parseFloat(num);
                } else {
                }
            });

            if (buy_num <= 0) {
                $.toast("请选择购买商品", "forbidden");
                return false;
            }
            window.location.href = "/Mobile/Home/orderconfirm/goods_id/" + 0 + "/item_id/" + 0 + "/buy_num/" + 0 + "/action/cart_now/cart_ids/0";
        });


        // 点击减号
        $(".mit").click(function () {
            if (can_click == 0) {
                return false
            }
            var id = $(this).parents(".add").attr('cart_id');
            var num = $(this).parents(".add").find(".number").text();
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
            $.post("/Mobile/Cart/ajax_cart_update", data, function (data) {
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
            var id = $(this).parents(".add").attr('cart_id');
            var num = $(this).parents(".add").find(".number").text();
            var data = {
                id: id,
                number: 1,
                type: 1,
            };
            $.post("/Mobile/Cart/ajax_cart_update", data, function (data) {
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
            $(this).toggleClass("bg1");
            if ($(this).next(".detail").find(".mid .list .lt div").hasClass("down")) {
                $(this).removeClass("bg1");
            }
            var id = $(this).parents('.cartList').attr('cart_id');
            var las = $(".bg").length;
            var ras = $(".way .bg1").length;
            var no = $(".way .down").length;
            if (ras < (las - no)) {
                $(".all .ss").find(".allcheck").removeClass("bg1");
                update_cart_select(1, 0, id);
            } else {
                $(".all .ss").find(".allcheck").addClass("bg1");
                update_cart_select(1, 1, id);
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
                update_cart_select(2, 1, 0);
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
                update_cart_select(2, 0, 0);
            }
            countCartPrice();
        })
    },


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
var update_cart_select = function (type, select, id) {
    var data = {
        type: type,
        selected: select,
        id: id
    }
    $.post("/Home/Cart/cart_select", data, function (data) {
    });
}
