var integral_obj = {
    //**********************************源码*********************************************
    index: function () {
        var swiper = new Swiper('.swiper-container', {
            pagination: '.swiper-pagination',
            paginationClickable: true,
            speed: 300,
            autoplay: 3000,
            loop: true,
        });

        load_obj.load_ajax($("nav"), 1, function (data) {
            callback(data);
        });

        load_obj.load_init($('nav'), 0, function (data) {
            callback(data);
        });

        var callback = function (data) {
            if (data.attach.total == 0) {
                $(".nodata").show();
            }
        };
    },

    detail: function () {
        var swiper = new Swiper('.swiper-container', {
            pagination: '.swiper-pagination',
            paginationClickable: true,
            speed: 300,
            autoplay: 3000,
            loop: true,
        });

        $(document).on("click", ".swiper-container .swiper-wrapper .swiper-slide", function () {
            var url = $(this).find("img").attr("src");
            var urls = new Array();
            $(".swiper-container .swiper-wrapper .swiper-slide").each(function () {
                urls.push($(this).find("img").attr("src"));
            })
            image_obj.preview_image(url, urls);
        });

        $("footer div:nth-child(2):not('.on')").click(function () {
            $.post('/wechat.php/Integral/detail', {product_id: product_id}, function (data) {
                if (data.ret == 1) {
                    window.location.href = data.url;
                } else {
                    alert(data.msg);
                    return false;
                }
            });
        });
    },

    order: function () {
        window.localStorage.url = window.location.href;
        //加载地址
        global_obj.load_address(function (data) {
            $("#address_select").html(data.data)
            var header = $("#address_select").height();
            var header1 = $("#address_select .rt img").height();
            $("#address_select .rt img").css("margin-top", (header - header1) / 2);
        });

        //选择配送方式
        $(".deliver_way").click(function () {
            $(".layer_select.deliver").show();
        });
        //选择支付方式
        $(".pay_way").click(function () {
            $(".layer_select.pay").show();
        });

        //点击浮层关闭按钮
        $(document).on("click", ".layer_select .bn", function () {
            $(".layer_select").hide();
        });
        //点击切换方式
        $(document).on("click", ".layer_select .add", function () {
            $(this).parents(".layer_select").find(".bg").removeClass("bg1").find("input:radio").prop("checked", false);
            $(this).find(".bg").addClass("bg1").find("input:radio").prop("checked", true);
            var text = $(this).find(".addrt").text();
            var name = $(this).find("input:radio").attr("name");
            $("." + name + "_way div:nth-child(2)").text(text);
            $(".layer_select").hide();
        });

        form_obj.form_submit(function (data) {
            callpay(data.data,data.attach);
        });

    },

    myintegral: function () {
        load_obj.load_ajax($("nav"), 1, function (data) {
            callback(data);
        });

        load_obj.load_init($('nav'), 0, function (data) {
            callback(data);
        });

        var callback = function (data) {
            if (data.attach.total == 0) {
                $(".nodata").show();
            }
        };
    },


    myorder: function () {
        load_obj.load_ajax($("nav"), 1, function (data) {
            callback(data);
        });

        load_obj.load_init($('nav'), 0, function (data) {
            callback(data);
        });

        var callback = function (data) {
            if (data.attach.total == 0) {
                $(".nodata").show();
            }
        };

        //删除
        $("nav").on("click", ".del", function () {
            $this = $(this);
            if (can_click == 0) {
                return false
            }
            can_click = 0;
            if (!confirm('确定要删除该订单吗')) {
                return false;
                can_click = 1;
            }
            var order_id = $(this).parents("li").attr("data-pid");
            $.post("/wechat.php/Integral/orderdel", {order_id: order_id}, function (data) {
                if (data.ret == 1) {
                    alert(data.msg);
                    $this.parents("li").remove();
                    can_click = 1;
                } else {
                    alert(data.msg);
                    can_click = 1;
                }
            });
        });

        //确认收货
        $("nav").on("click", ".finish", function () {
            $this = $(this);
            if (can_click == 0) {
                return false
            }
            can_click = 0;
            if (!confirm('确定确认收货吗')) {
                return false;
                can_click = 1;
            }
            var order_id = $(this).parents("li").attr("data-pid");
            $.post("/wechat.php/Integral/orderfinish", {order_id: order_id}, function (data) {
                if (data.ret == 1) {
                    alert(data.msg);
                    $this.parents("li").find(".bn botton").text("删除").removeClass("finish").addClass("del");
                    $this.parents("li").find(".top span:nth-child(3)").text("已完成");
                    can_click = 1;
                } else {
                    alert(data.msg);
                    can_click = 1;
                }
            });
        });
    },

}