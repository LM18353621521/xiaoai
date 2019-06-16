var vip_obj = {
    //**********************************源码*********************************************
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

    myaddress: function () {
        //地址选择
        if (type) {
            $(".top").click(function () {
                window.localStorage.address_id = $(this).parent().attr("data-pid");
                window.location.href = window.localStorage.url;
            });
        } else {
            window.localStorage.url = window.location.href;
        }

        //地址删除
        $(".address_del").click(function () {
            $this = $(this);
            if (!confirm('确定要删除该地址吗')) {
                return false
            }
            if (can_click == 0) {
                return false
            }
            can_click = 0;
            var myaddress_id = $(this).parents("li").attr("data-pid");
            $.post('/wechat.php/Vip/addressdel', {myaddress_id: myaddress_id}, function (data) {
                if (data.ret == 1) {
                    $this.parents("li").remove();
                    can_click = 1;
                } else {
                    alert(data.msg);
                    can_click = 1;
                    return false;
                }
            }, 'json');
        });

        //设为默认地址
        $("nav").on("click", ".add:contains(设为默认)", function () {
            $this = $(this);
            if (can_click == 0) {
                return false
            }
            can_click = 0;

            var myaddress_id = $(this).parents("li").attr("data-pid");
            $.post("/wechat.php/Vip/addressdefault", {myaddress_id: myaddress_id}, function (data) {
                if (data.ret == 1) {
                    $this.find(".bg").addClass("bg1")
                        .parent().find(".addrt").addClass("mo").text("默认地址")
                        .parents("li").siblings().find(".add .bg").removeClass("bg1")
                        .parents("li").find(".add .addrt").removeClass("mo").text("设为默认");
                    alert(data.msg);
                    can_click = 1;
                } else {
                    alert(data.msg);
                    can_click = 1;
                    return false;
                }
            });
        });
    },

    addressadd: function () {
        $(".mo").click(function () {
            if ($(this).find(".bg").hasClass("bg1")) {
                $(this).find(".add p").removeClass("bg1").find("input:radio").prop("checked", false);
            } else {
                $(this).find(".add p").addClass("bg1").find("input:radio").prop("checked", true);
            }
        });

        form_obj.form_submit(function (data) {
            callback(data);
        });
        var callback = function (data) {
            window.localStorage.address_id = data.attach.id;
            window.location.href = window.localStorage.url;
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