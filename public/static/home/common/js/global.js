

/**
 * 获取城市
 * @param t  省份select对象
 * @param city
 * @param district
 * @param twon
 */
function get_city(t,city,district,twon){
    var parent_id = $(t).val();
    if(!parent_id > 0){
        var v = '<option>请选择</option>';
        $('#city').empty().html(v);
        $('#district').empty().html(v);
        return;
    }
    var city_id = 'city';
    if(typeof(city) != 'undefined' && city != ''){
        city_id = city;
    }
    var district_id = 'district';
    if(typeof(district) != 'undefined' && district != ''){
        district_id = district;
    }
    var twon_id = 'twon';
    if(typeof(twon) != 'undefined' && twon != ''){
        twon_id = twon;
    }
    $('#'+district_id).empty().css('display','none');
    $('#'+twon_id).empty().css('display','none');
    var url = '/Home/Api/getRegion/level/2/parent_id/'+ parent_id;
    $.ajax({
        type : "GET",
        url  : url,
        error: function(request) {
            alert("服务器繁忙, 请联系管理员!");
            return;
        },
        success: function(v) {
            console.log(v);
            v = '<option value="0">选择城市</option>'+ v;
            $('#'+city_id).empty().html(v);
        }
    });
}
/**
 * 获取地区
 * @param t  城市select对象
 * @param district
 * @param twon
 */
function get_area(t,district,twon){
    var parent_id = $(t).val();
    if(!parent_id > 0){
        return;
    }
    var district_id = 'district';
    if(typeof(district) != 'undefined' && district != ''){
        district_id = district;
    }
    var twon_id = 'twon';
    if(typeof(twon) != 'undefined' && twon != ''){
        twon_id = twon;
    }
    $('#'+district_id).empty().css('display','inline');
    $('#'+twon_id).empty().css('display','none');
    var url = '/Home/Api/getRegion/level/3/parent_id/'+ parent_id;
    $.ajax({
        type : "GET",
        url  : url,
        error: function(request) {
            alert("服务器繁忙, 请联系管理员!");
            return;
        },
        success: function(v) {
            v = '<option>选择区域</option>'+ v;
            $('#'+district_id).empty().html(v);
        }
    });
}

// 获取最后一级乡镇
function get_twon(obj,twon){
    var twon_id = 'twon';
    if(typeof(twon) != 'undefined' && twon != ''){
        twon_id = twon;
    }
    var parent_id = $(obj).val();
    var url = '/index.php?m=Home&c=Api&a=getTwon&parent_id='+ parent_id;
    $.ajax({
        type : "GET",
        url  : url,
        success: function(res) {
            if(parseInt(res) == 0){
                $('#'+twon_id).empty().css('display','none');
            }else{
                $('#'+twon_id).css('display','inline').empty().html(res);
            }
        }
    });
}

$(function () {
    $(".login_do").click(function () {
        if(can_click==0){return false};
        var username = $("#login_username").val();
        var password = $("#login_password").val();
        if(username==""){
            layer.msg("请输入账号",{icon:2});
            return false;
        }
        if(password==""){
            layer.msg("请输入密码",{icon:2});
            return false;
        }
        can_click=0;
        $.post("/Home/Login/login", {mobile:username,password:password}, function (data) {
            if (data.ret == 1) {
                layer.msg(data.msg,{icon:1,time:1200},function () {
                    window.location.reload();
                    can_click=1;
                });
            } else {
                layer.msg(data.msg,{icon:2});
                can_click=1;
            }
        });
    });
    /**
     * 退出登录
     */
    $(".login_out").click(function () {
        if(can_click==0){return false};
        can_click=0;
        $.post("/Home/Login/login_out", {}, function (data) {
            if (data.ret == 1) {
                layer.msg(data.msg,{icon:1,time:1200},function () {
                    setCookies('bindTips',0);
                    window.location.reload();
                    can_click=1;
                });
            } else {
                layer.msg(data.msg,{icon:2});
                can_click=1;
            }
        });
    });
})