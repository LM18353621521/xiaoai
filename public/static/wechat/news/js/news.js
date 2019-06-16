var news_obj = {
    index: function () {
        var category_id = $("#form_ajax input[name=category_id]").val();
        $(".swiper-slide[data-pid=" + category_id + "]").addClass("on").siblings().removeClass("on");
        var swiper = new Swiper('.swiper-container', {
            nextButton: '.swiper-button-next',
            pagination: '.swiper-pagination',
            slidesPerView: 8,
            paginationClickable: true,
        });

        $(".swiper-slide").click(function () {
            $(".nodata").hide();
            $(this).addClass("on").siblings().removeClass("on");
            $("#form_ajax input[name=category_id]").val($(this).attr("data-pid"));
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
            if (data.attach.total == 0) {
                $(".nodata").show();
            }
        };
    },

    detail: function () {
        $("footer").click(function () {
            $(".wirte_layer").show();
            var height = $(window).height();
            var height1 = $(".wirte_lay").height();
            var height2 = height - height1;
            $(".wirte_layer .top").height(height2);
            $('textarea[name=content]').focus();
        });

        $(".wirte_layer").on("click", ".top", function () {
            $(".wirte_layer").hide();
        });

        load_obj.load_ajax($("body"), 1, function (data) {
            callback(data);
        });

        form_obj.form_submit(function (data) {
            callback1(data);
            load_obj.load_ajax($("body"), 1, function (data) {
                callback(data);
            });

        });

        var callback = function (data) {
            if (data.attach.total == 0) {
                $(".nodata").show();
            }
        };

        var callback1 = function (data) {
            $(".wirte_layer").hide();
            $('textarea[name=content]').val('');
            can_click = 1;
        };

        //点赞
        $("#review_list").on('click', ".zan:not(.haszan)", function () {
            if (can_click != 1) {
                return false;
            }
            can_click = 0;
            var review_id = $(this).parents(".list").attr('data-pid');
            $this = $(this);
            $.post($("#do_thumb").val(), {review_id: review_id}, function (data) {
                if (data.ret == 1) {
                    load_obj.load_ajax($("body"), 1, function (data) {
                        callback(data);
                    });
                    can_click = 1;
                } else {
                    if (data.msg) {
                        alert(data.msg);
                    }
                    can_click = 1;
                    return false;
                }
            });
        });

        $("#review_list").on('click', ".haszan", function () {
            alert('您已经点过赞了');
            return false;
        })
    },
}