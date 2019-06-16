var money_obj = {
    index: function () {
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

    card: function () {
        var card_id = $(".recharge div:nth-child(1)").addClass("on").attr("data-pid");
        $("input[name=card_id]").val(card_id);

        $(".recharge .card").click(function () {
            $("input[name=money]").val('');
            $(this).addClass("on").siblings().removeClass("on");
            card_id = $(".recharge div.on").attr("data-pid");
            $("input[name=card_id]").val(card_id);
        });

        $(".money input").click(function () {
            $(".recharge div").removeClass("on");
            $("input[name=card_id]").val(0);
        });

        form_obj.form_submit(function (data) {
            callpay(data.data,data.attach);
        });

    },

    withdraw: function () {
        form_obj.form_submit(function (data) {
            callback(data);
        });
        var callback = function (data) {
            window.location.href = data.url;
        };
    },
}