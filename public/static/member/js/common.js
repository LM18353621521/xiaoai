var can_click = 1;
$(function () {
    $('.daterangepicker-time').daterangepicker({
        format: 'YYYY/MM/DD HH:mm',
        separator: '-',
    });

    load_video_init=function(){
        //单张视频上传
        if ($("form .one-video").length) {
            $("form .one-video").each(function () {
                callback = function (videopath, file_input_obj) {
                    var file_obj = $(file_input_obj).parents(".one-video");
                    var html="";
                    html+='<video controls="controls" width="360" height="200">';
                    html+='<source src="'+videopath+'" type="video/ogg" />';
                    html+='<source src="'+videopath+'" type="video/mp4" />';
                    html+='Your browser does not support the video tag.';
                    html+='</video>';
                    file_obj.find('input[name=video_url]').val(videopath);
                    file_obj.find('.img').html(html);
                };
                webuploader_obj.webuploader_init('#' + $(this).find(".webuploader-picker").attr("id"), $(this).find("input:hidden"),$(this).find(".img"),2,true,1,callback);
            });
        }
    }

    load_one_img_init=function (){
        //单张图片上传
        if ($("form .one-img").length) {
            $("form .one-img").each(function () {
                webuploader_obj.webuploader_init('#' + $(this).find(".webuploader-picker").attr("id"), $(this).find("input:hidden"),$(this).find(".img"));
            });
        }
    }



    //多张图片
    if ($("form .more-img").length) {
        $(".more-img").on('click', ".img div span", function () {
            $(this).parent().remove();
        });

        $("form .more-img").each(function () {
            $this = $(this);

            $this.find('.img div span').on('click', function () {
                $(this).parent().remove();
            });

            pic_count = parseInt($this.find(".help-block span").text());
            callback = function (imgpath, file_input_obj) {
                var file_obj = $(file_input_obj).parents(".more-img");

                pic_count = parseInt(file_obj.find(".help-block span").text());
                if (file_obj.find('.img div').size() >= pic_count) {
                    layer.msg('您上传的图片数量已经超过' + pic_count + '张，不能再上传！',{icon:2});
                    //alert('您上传的图片数量已经超过' + pic_count + '张，不能再上传！');
                    return false;
                }
                var html = '<div>' + webuploader_obj.source_link(imgpath) + '<span>删除</span><input type="hidden" name="' + file_obj.attr("data-imgname") + '" value="' + imgpath + '" /></div>';
                file_obj.find('.img').append(html);
            };

            webuploader_obj.webuploader_init('#' + $this.find(".webuploader-picker").attr("id"), '', '', '', true, pic_count, callback);
        });
    }

    //地图
    if ($("form .map").length) {
        window.addEventListener('message', function (event) {
            // 接收位置信息，用户选择确认位置点后选点组件会触发该事件，回传用户的位置信息
            var loc = event.data;
            //防止其他应用也会向该页面post信息，需判断module是否为'locationPicker'
            if (loc && loc.module == 'locationPicker') {
                $("input[name=lng]").val(loc.latlng.lng);
                $("input[name=lat]").val(loc.latlng.lat);
                $("input[name=address]").val(loc.poiaddress);
                $(".map p.form-control-static").text(loc.poiaddress);
            }
        }, false);
    }

    //点击图片突变弹出图片浮层
    $(document).on("click","a[href=#layer_image]",function(){
        console.log($(this).attr("data-image"));
       $("#layer_image img").attr("src",$(this).attr("data-image"));
    });
});

var common_obj = {
    data_handle: function (callback) {
        $("tbody").on('click', 'input[type=checkbox]', function () {
            if ($(this).is(':checked') == true) {
                $(this).attr('checked', true);
            } else {
                $(this).attr('checked', false);
            }
            var name = $('#check_all').attr('data-name');
            var all_checkbox = $('input[name=' + name + ']').length;
            var checked_checkbox = $('input[name=' + name + ']:checked').length;
            if(checked_checkbox >= all_checkbox){
                $('#check_all').click();
            }else {
                $('#check_all').attr('checked',false);
            }
        });

        //全选
        $(document).on('change', '#check_all', function () {
            var name = $(this).attr('data-name');
            console.log($(this).is(':checked'));
            if ($(this).is(':checked') == true) { //选中
                $('input[name=' + name + ']').prop('checked', true);
            } else { //取消选中
                $('input[name=' + name + ']').prop('checked', false);
            }
        });

        //批量修改
        $('.submit-all').click(function () {

            var operate_value = $('select[name=oprate]').val();
            var cur = $("select[name=oprate] option:selected");
            var url = cur.attr('data-url'); //url
            var tablename =cur.attr('data-tablename');
            var fieldname = cur.attr('data-fieldname');
            var afterchange = cur.attr("data-value");
            var where = cur.attr('data-where');
            var all_id = [];

            var data={
                id: all_id, //多选的值
                operate_value:operate_value,//下拉框类型
                where: where,//多选数据字段
                tablename: tablename,//修改表
                fieldname: fieldname,//修改字段
                afterchange: afterchange//修改成的值
            };

            switch (operate_value){
                case 'sort':
                    all_id =sort(cur,url,data,callback);
                    break;
                case 'update':
                   all_id =  update(cur,url,data,callback);
                    break;
                default:
                    all_id = update(cur,url,data,callback);
                    layer.msg('无选项',{icon:2});
                    //alert('无选项');
            }
        });

        function sort(cur,url,data,callback){
            var all_id = [];
            var name = cur.attr('data-name');
            $('input[name='+name+']').each(function () {
                var array = {};
                array['id'] = $(this).attr('data-id');
                array[name] = $(this).val();
                all_id.push(array);
            })
            all_id = JSON.stringify(all_id);
            //return all_id;
            data.id=all_id;
            $.post(url,data, function (data) {
                $('select[name=oprate]').blur();
                if (data.ret == 1) {
                    if (data.msg) {
                        layer.msg(data.msg, {icon: 1,time:2000});
                    }
                    $('input[type=checkbox]').removeAttr('checked');
                    common_obj.load_form(callback);
                } else {
                    layer.msg(data.msg, {icon: 2,time:2500});
                    return false;
                }
            }, 'json');
        }

        function update(cur,url,data,callback){
            var all_id = [];
            var name = cur.attr('data-name');
            $('input[name=' + name + ']').each(function () {
                if($(this).prop('checked')==true){
                    all_id.push($(this).val());
                }
            });
            //console.log(all_id);
            if (all_id.length == 0) {
                layer.msg(cur.attr('data-info'),{icon:1,time:2000});
                //alert(cur.attr('data-info'));
                return false;
            }

            data.id=all_id;
            if (cur.attr("data-text")) {
                var check=true;
                layer.confirm(
                    cur.attr("data-text"),
                    {title:'提示'},
                    function (res) {
                        layer.close(res);
                        $.post(url,data, function (data) {
                            $('select[name=oprate]').blur();
                            if (data.ret == 1) {
                                if (data.msg) {
                                    layer.msg(data.msg, {icon: 1,time:2000});
                                }
                                $('input[type=checkbox]').removeAttr('checked');
                                common_obj.load_form(callback);
                            } else {
                                layer.msg(data.msg, {icon: 2,time:2500});
                                return false;
                            }
                        }, 'json');
                    },function(res){
                    }
                );
                //var check = confirm(cur.attr("data-text"));
                //if (check == false) {
                //    return false
                //}
            }else{
                $.post(url,data, function (data) {
                    $('select[name=oprate]').blur();
                    if (data.ret == 1) {
                        if (data.msg) {
                            layer.msg(data.msg, {icon: 1,time:2000});
                        }
                        $('input[type=checkbox]').removeAttr('checked');
                        common_obj.load_form(callback);
                    } else {
                        layer.msg(data.msg, {icon: 2,time:2500});
                        return false;
                    }
                }, 'json');
            }
            //return all_id;
        }

        //操作
        $('table').on("click", "td a[href=#deal]", function () {

            $this = $(this);

            if ($(this).attr("data-text")) {
                //var check = confirm($(this).attr("data-text"));
                layer.confirm($(this).attr("data-text"),{title:"提示"},
                    function(res){
                        common_obj.deal_do($this);
                        layer.close(res);
                    },function(res){
                    }
                );
                //if (check == false) {
                //    return false;
                //}
            }else{
                common_obj.deal_do($this,callback);
            }
        });

        common_obj.load_form(callback);
        $('#search-form').submit(function (e) {
            e.preventDefault();
            $("#search-form input[name=page]").val(1).attr("data-value", 1);
            common_obj.load_form(callback);
        });

        //点击底部页面分页
        $("#page").on("click", ".pagination li a", function (e) {
            e.preventDefault();
            var page = $(this).attr("href");
            page = page.split('?');
            page = page[1].substring(5);

            $("#search-form input[name=page]").val(page).attr("data-value", page);
            common_obj.load_form(callback);
        });

        //导出
        $('#search-form input:button').click(function () {
            window.location = $(this).attr("data-url");
        });
    },
    /**
     * 操作
     * @param callback
     */
    deal_do:function(that,callback){
        var url = that.attr('data-url');
        var id = that.parent().attr('data-pid');
        var tablename = that.attr('data-tablename');
        var fieldname = that.attr('data-fieldname');
        var afterchange = that.attr("data-value");
        $.post(url, {
            id: id,
            tablename: tablename,
            fieldname: fieldname,
            afterchange: afterchange
        }, function (data) {
            if (data.ret == 1) {
                if (data.msg) {
                    layer.msg(data.msg, {icon: 1,time:2000},function(){
                        common_obj.load_form(callback);
                    });
                }else{
                    common_obj.load_form(callback);
                }
            } else {
                layer.msg(data.msg, {icon: 2,time:2500});
                return false;
            }
        }, 'json');
    },

    load_form: function (callback) {
        $('#search-form input:not(input:submit),#search-form select').each(function () {
            $(this).attr("data-value", $(this).val());
            //console.log($(this).val());  console.log($(this).attr("data-value"));
        });
        //return false;
        var obj = {};
        $.each($('#search-form input:not(input:submit):not(input:button),#search-form select'), function (i, n) {
            var name = $(this).attr("name");
            obj[name] = $(this).attr("data-value");
        })


        $.post('', obj, function (data) {
            if (data.ret == 1) {
                $("#data-table tbody").html(data.data);
                if ($("#page").length) {
                    $("#page").html(data.attach.page)
                }
            } else {
                if (data.msg) {
                    layer.msg(data.msg, {icon: 2,time:2500});
                    return false;
                }
            }
            if (callback) {
                callback(data);
            }
        }, 'json');
    },

    form_submit: function () {
        $('#data-form').submit(function (e) {
            e.preventDefault();
            if (global_obj.check_form($('#data-form').find('*[notnull]'))) {
                return false
            }
            if (can_click != 1) {
                return false;
            }
            can_click = 0;
            $.post($("#do_action").val(), $('#data-form').serialize(), function (data) {
                if (data.ret == 1) {
                    if (data.msg) {
                        layer.msg(data.msg, {icon: 1,time:2000},function(){
                            if ($('#do_jump').length) {
                                window.location.href = $('#do_jump').val();
                            } else {
                                window.history.go(-1);
                            }
                        });
                        //alert(data.msg);
                    }

                } else {
                    if (data.msg) {
                        layer.msg(data.msg,{icon:2})
                        //alert(data.msg);
                        can_click = 1;
                        return false;
                    }
                }
            }, 'json');
        });
    },
}


var webuploader_obj = {
    webuploader_init: function  (file_input_obj, filepath_input_obj, img_detail_obj, size) {//file_input_obj 上传图片元素 filepath_input_obj图片路径元素 img_detail_obj 追加图片元素
        var multi = (typeof(arguments[4]) == 'undefined') ? false : arguments[4];	//是否多张
        var queueSizeLimit = (typeof(arguments[5]) == 'undefined') ? 5 : arguments[5];	//最多上传张数
        var callback = arguments[6];	//回调函数
        var fileExt = (typeof(arguments[7]) == 'undefined') ? '*.jpg;*.png;*.gif;*.jpeg;*.bmp' : arguments[7];//可上传格式
        //console.log(file_input_obj);console.log(filepath_input_obj);console.log(img_detail_obj);console.log(size);
        //destroy();
        var uploader = WebUploader.create({
            auto: true,
            swf: './static/common/js/webuploader/Uploader.swf',// swf文件路径
            server: '/admin.php/Member/webfileupload',// 文件接收服务端。

            // 选择文件的按钮。可选。
            // 内部根据当前运行是创建，可能是input元素，也可能是flash.
            pick: {
                id: file_input_obj,
                multiple: multi
            },
            resize: false,// 不压缩image, 默认如果是jpeg，文件上传前会压缩一把再上传！
            disableGlobalDnd: true,	 // 禁掉全局的拖拽功能。这样不会出现图片拖进页面的时候，把图片打开。
            fileNumLimit: 300	//最多上传张数
        });

        // 文件上传成功，给item添加成功class, 用样式标记上传成功。
        uploader.on('uploadSuccess', function (file, response) {
            //var jsonData=eval('('+response+')');
            var jsonData = response;
            if (jsonData.status == 1) {
                if (!multi) {//单图
                    filepath_input_obj.val(jsonData.imgpath);
                    img_detail_obj.html(webuploader_obj.source_link(jsonData.imgpath));
                } else {
                    callback(jsonData.imgpath, file_input_obj);
                }
            } else {
                layer.msg('文件上传失败，出现未知错误！',{icon:2});
                //alert('文件上传失败，出现未知错误！');
            }
        });

        // 文件上传失败，显示上传出错。
        uploader.on('uploadError', function (file, response) {
            var jsonData = eval('(' + response + ')');
            if (jsonData.status == 1) {

            } else {
                layer.msg('文件上传失败，出现未知错误！',{icon:2});
                //alert('文件上传失败，出现未知错误！');
            }
        });

        // 完成上传完了，成功或者失败，先删除进度条。
        uploader.on('uploadComplete', function (file, response) {

        });
    },

    source_link: function (img) {
        if (!img) {
            return;
        }
        return '<a href="' + img + '" target="_blank"><img src="' + img + '"></a>';
    }
};

var global_obj = {
    check_form: function (obj) {
        var flag = false;
        obj.each(function () {
            if ($(this).val() == '') {
                layer.msg($(this).attr("placeholder"),{icon:2});
                //alert($(this).attr("placeholder"));
                flag = true;
                $(this).focus();
                return false;
            }
        });
        return flag;
    },
};
