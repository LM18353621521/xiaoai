var can_load = 1;
var can_click = 1;
var load_obj = {
    load_init: function (obj, page, callback) {
        var oldtop = 0;
        var top0 = 0;
        $(window).scroll(function () {
            oldtop = top0;
            top0 = $(window).scrollTop();
            if (top0 > oldtop) {
                var bottom = $(document).innerHeight() - top0 - $(window).height();
                if (bottom <= 200 && can_load == 1) {
                    load_obj.load_ajax(obj, page, callback);
                }
            }
        });
    },
    load_ajax: function (obj, page, callback) {
        if (page != 1) {
            page = obj.find('input[name=page]').val();
        }
        var url = obj.find('input[name=url]').val();
        var data = $('#form_ajax').serialize();//条件
        if (can_load == 1) {
            can_load = 0;
            $(".nodata").hide();
            $(".load").text('正在加载中...').show();
            $.post(url, data + '&page=' + page, function (data) {
                if(page==1){
                    obj.find('.content').html('');
                }
                var page_size=data.attach.page_size;
                var count=data.attach.count;
                if (data.ret == 1) {
                    if(page==1&&count==0){//无数据
                        $(".nodata").show();
                        $(".load").hide();
                    }else if(page>=1&&count<page_size){//已加载完
                        obj.find('.content').append(data.data);
                        $(".load").text('人家是有底线的~').show();
                    }else{//继续加载
                        obj.find('input[name=page]').val(parseInt(page) + 1);
                        obj.find('.content').append(data.data);
                        $(".load").text('上拉加载更多...').show();
                        can_load = 1;
                    }
                    //if (data.data != '') {
                    //    obj.find('input[name=page]').val(parseInt(page) + 1);
                    //    obj.find('.content').append(data.data);
                    //}
                    if (callback) {
                        callback(data);
                    }

                }
            });
        }
    },
};

var form_obj = {
    form_submit: function (callback) {
        $('#data-form').submit(function (e) {
            e.preventDefault();
            if (can_click != 1) {
                return false;
            }
            if (global_obj.check_form($('#data-form').find('*[notnull]'))) {
                return false
            }
            can_click = 0;
            $.post($("#do_action").val(), $('#data-form').serialize(), function (data) {
                console.log(data);
                if (data.ret == 1) {
                    if (data.msg) {
                        $.toast(data.msg,1500);
                        //$.alert(data.msg);
                    }
                    if (callback) {
                        callback(data);
                    }
                } else {
                    if (data.msg) {
                        $.alert(data.msg);
                    }
                    if (data.url) {
                        window.location.href = data.url;
                    }
                    can_click = 1;
                    return false;
                }
            }, 'json');
        });
    },
};

var global_obj = {

    div_mask: function (remove) {
        if (remove == 1) {
            $('#div_mask').remove();
        } else {
            $('body').prepend('<div id="div_mask"></div>');
            $('#div_mask').css({
                width: '100%',
                height: $(document).height(),
                overflow: 'hidden',
                position: 'fixed',
                top: 0,
                left: 0,
                background: '#000',
                opacity: 0.6,
                'z-index': 10000
            });
        }
    },
    win_alert: function (tips, handle) {
        $('body').prepend('<div id="global_win_alert"><div>' + tips + '</div><h1>好</h1></div>');
        $('#global_win_alert').css({
            position: 'fixed',
            left: $(window).width() / 2 - 125,
            top: '30%',
            background: '#fff',
            border: '1px solid #ccc',
            opacity: 0.95,
            width: 250,
            'z-index': 1000000,
            'border-radius': '8px'
        }).children('div').css({
            'text-align': 'center',
            padding: '30px 10px',
            'font-size': 16
        }).siblings('h1').css({
            height: 40,
            'line-height': '40px',
            'text-align': 'center',
            'border-top': '1px solid #ddd',
            'font-weight': 'bold',
            'font-size': 20
        });
        $('#global_win_alert h1').click(function () {
            $('#global_win_alert').remove();
        });
        if ($.isFunction(handle)) {
            $('#global_win_alert h1').click(handle);
        }
    },

    check_form: function (obj) {
        var flag = false;
        obj.each(function () {
            if ($(this).val() == '') {
                $.toast($(this).attr("notice"),'text');
                //$.alert($(this).attr("notice"));
                flag = true;
                $(this).focus();
                return false;
            }
        });
        return flag;
    },

    load_address: function (callback) {
        var address_id = window.localStorage.address_id;
        if (!address_id) {
            address_id = 0;
        }

        $.post("/wechat.php/Vip/address", {address_id: address_id}, function (data) {
            if (data.ret == 1) {
                if (callback) {
                    callback(data);
                }
            }
        }, 'json');
    },

};