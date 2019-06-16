var wechat_obj = {
    classify: function () {
        $(".classify-tree").sortable({items: "> .wechat-classify"});
        $(".wechat-classify-cur-two").sortable({items: "> .wechat-classify-two"});

        //发布菜单
        $(".classify-publish").click(function () {
            $.post($(this).attr("data-url"), '', function (data) {
                if (data.ret == 1) {
                    alert('发布成功,菜单将在24小时内生效');
                    window.location.reload();
                } else {
                    alert(data.msg);
                    return false;
                }
            });
        });

        //删除菜单
        $(".classify-del").click(function () {
            $.post($(this).attr("data-url"), '', function (data) {
                if (data.ret == 1) {
                    alert('删除成功,菜单将在24小时内生效');
                    window.location.reload();
                } else {
                    alert(data.msg);
                    return false;
                }
            });
        });

        //增加一级菜单
        $(".classify-add").click(function () {
            var number = $(".wechat-classify").length;
            if (number >= 3) {
                alert('主菜单最多三个！');
                return false;
            }
            var html = $(".wechat-classify-demo").html();
            $(".classify-tree").append('<div class="row li01 wechat-classify">' + html + '</div>');
        });

        //增加二级菜单
        $(".classify-tree").on("click", ".wechat-classify-one-addchild", function () {
            $(this).parents(".wechat-classify").find(".wechat-classify-one .wechat-classify-one-setting").hide();
            //var cur = $(this).parents(".wechat-classify").attr("data-pid");
            var number = $(this).parents(".wechat-classify").find(".wechat-classify-two").length;
            if (number >= 5) {
                alert('每级子菜单最多五个！');
                return false;
            }
            var html = $(".wechat-classify-two-demo").html();
            $(this).parents(".wechat-classify").find(".wechat-classify-cur-two").append('<div class="col-lg-12 mbot-15 wechat-classify-two">' + html + '</div>');
        });

        //删除一级菜单
        $(".classify-tree").on("click", ".wechat-classify-one-del", function () {
            if (!confirm('删除一级菜单之后相应的二级菜单会对应删除，确定删除吗？')) {
                return false;
            }
            $(this).parents(".wechat-classify").remove();
        });

        //删除二级菜单
        $(".classify-tree").on("click", ".wechat-classify-two-del", function () {
            var number = $(this).parents(".wechat-classify").find(".wechat-classify-two").length;
            if (number <= 1) {
                $(this).parents(".wechat-classify").find(".wechat-classify-one .wechat-classify-one-setting").show();
            }
            $(this).parents(".wechat-classify-two").remove();
        });

        //设置菜单动作
        $(".classify-tree").on("click", ".wechat-classify-one-setting,.wechat-classify-two-setting", function () {
            $(".classify-tree .wechat-classify-two,.wechat-classify-one").removeClass("cur-classify");
            $(this).parent().parent().addClass("cur-classify");
            var name = $(".cur-classify").find(".first").val();
            var type = $(".cur-classify").find(".second").val();
            var content = $(".cur-classify").find(".third").val();

            $(".classify-layer .classify-name").text('选择菜单【' + name + '】要执行的操作');

            if (type == 'click') {
                $(".classify-layer .reply-view").hide().find("input").removeAttr("required", "required");
                $(".classify-layer .reply-miniprogram").hide().find("input").removeAttr("required", "required");
                $(".classify-layer .reply-click").show().find("select").val(content);
            } else if (type == 'view') {
                $(".classify-layer .reply-view").show().find("input").attr("required", "required").val(content);
                $(".classify-layer .reply-miniprogram").hide().find("input").removeAttr("required", "required");
                $(".classify-layer .reply-click").hide();
            } else if (type == 'miniprogram') {
                var content = content.split(' ');
                $(".classify-layer .reply-view").hide().find("input").removeAttr("required", "required");
                $(".classify-layer .reply-miniprogram").show().find("input").attr("required", "required");
                $(".classify-layer .reply-miniprogram").find("input[name=appid]").val(content[0]);
                $(".classify-layer .reply-miniprogram").find("input[name=pagepath]").val(content[1]);
                $(".classify-layer .reply-miniprogram").find("input[name=url]").val(content[2]);
                $(".classify-layer .reply-click").hide();
            } else {
                $(".classify-layer .reply-view").hide();
                $(".classify-layer .reply-click").hide();
                $(".classify-layer .reply-miniprogram").hide();
            }

            $(".classify-layer input[name=type]").each(function () {
                if ($(this).val() == type) {
                    $(this).prop("checked", true);
                } else {
                    $(this).prop("checked", false);
                }
            });

            $(".classify-layer").show();
        });

        //浮层关闭
        $(document).on("click", ".icon-remove,.layerer", function () {
            $(".classify-layer").hide();
        });

        //浮层切换类型
        $(document).on("click", ".classify-layer input[name=type]", function () {
            if ($(this).val() == 'click') {
                $(".classify-layer .reply-view,.classify-layer .reply-miniprogram").hide().find("input").removeAttr("required", "required");
                $(".classify-layer .reply-click").show();
            } else if ($(this).val() == 'view') {
                $(".classify-layer .reply-miniprogram").hide().find("input").removeAttr("required", "required");
                $(".classify-layer .reply-view").show().find("input").attr("required", "required");
                $(".classify-layer .reply-click").hide();
            } else if ($(this).val() == 'miniprogram') {
                $(".classify-layer .reply-view").hide().find("input").removeAttr("required", "required");
                $(".classify-layer .reply-miniprogram").show().find("input").attr("required", "required");
                $(".classify-layer .reply-click").hide();
            } else {
                $(".classify-layer .reply-view").hide();
                $(".classify-layer .reply-click").hide();
                $(".classify-layer .reply-miniprogram").hide();
            }
        });

        $('#reply-form').submit(function (e) {
            e.preventDefault();
            var type = $('#reply-form input[name=type]:checked').val();

            if (type == "click") {
                var content = $("#reply-form select[name=keywords_id]").val();
                $(".cur-classify").find(".third").val(content);
            } else if (type == "view") {
                var content = $("#reply-form input[name=name]").val();
                $(".cur-classify").find(".third").val(content);
            } else if (type == "miniprogram") {
                var content = $("#reply-form input[name=appid]").val() + ' ' + $("#reply-form input[name=pagepath]").val() + ' ' + $("#reply-form input[name=url]").val();
                $(".cur-classify").find(".third").val(content);
            }
            $(".cur-classify").find(".second").val(type);
            $(".classify-layer").hide();
        });

        $('#data-form').submit(function (e) {
            e.preventDefault();
            if (can_click != 1) {
                //return false;
            }
            can_click = 0;
            var ajaxdata = new Object();
            ajaxdata.name_0 = new Array();
            ajaxdata.type_0 = new Array();
            ajaxdata.content_0 = new Array();
            $(".classify-tree .wechat-classify-one input[name=name]").each(function () {
                ajaxdata.name_0.push($(this).val());
            });
            $(".classify-tree .wechat-classify-one input[name=type]").each(function () {
                ajaxdata.type_0.push($(this).val());
            });
            $(".classify-tree .wechat-classify-one input[name=content]").each(function () {
                ajaxdata.content_0.push($(this).val());
            });
            if ($(".classify-tree .wechat-classify:nth-child(1)").length) {
                if ($(".classify-tree .wechat-classify:nth-child(1) .wechat-classify-two").length) {
                    ajaxdata.name_1 = new Array();
                    ajaxdata.type_1 = new Array();
                    ajaxdata.content_1 = new Array();
                    $(".classify-tree .wechat-classify:nth-child(1) .wechat-classify-two input[name=name]").each(function () {
                        ajaxdata.name_1.push($(this).val());
                    });
                    $(".classify-tree .wechat-classify:nth-child(1) .wechat-classify-two input[name=type]").each(function () {
                        ajaxdata.type_1.push($(this).val());
                    });
                    $(".classify-tree .wechat-classify:nth-child(1) .wechat-classify-two input[name=content]").each(function () {
                        ajaxdata.content_1.push($(this).val());
                    });
                }
            }
            if ($(".classify-tree .wechat-classify:nth-child(2)").length) {
                if ($(".classify-tree .wechat-classify:nth-child(2) .wechat-classify-two").length) {
                    ajaxdata.name_2 = new Array();
                    ajaxdata.type_2 = new Array();
                    ajaxdata.content_2 = new Array();
                    $(".classify-tree .wechat-classify:nth-child(2) .wechat-classify-two input[name=name]").each(function () {
                        ajaxdata.name_2.push($(this).val());
                    });
                    $(".classify-tree .wechat-classify:nth-child(2) .wechat-classify-two input[name=type]").each(function () {
                        ajaxdata.type_2.push($(this).val());
                    });
                    $(".classify-tree .wechat-classify:nth-child(2) .wechat-classify-two input[name=content]").each(function () {
                        ajaxdata.content_2.push($(this).val());
                    });
                }
            }
            if ($(".classify-tree .wechat-classify:nth-child(3)").length) {
                if ($(".classify-tree .wechat-classify:nth-child(3) .wechat-classify-two").length) {
                    ajaxdata.name_3 = new Array();
                    ajaxdata.type_3 = new Array();
                    ajaxdata.content_3 = new Array();
                    $(".classify-tree .wechat-classify:nth-child(3) .wechat-classify-two input[name=name]").each(function () {
                        ajaxdata.name_3.push($(this).val());
                    });
                    $(".classify-tree .wechat-classify:nth-child(3) .wechat-classify-two input[name=type]").each(function () {
                        ajaxdata.type_3.push($(this).val());
                    });
                    $(".classify-tree .wechat-classify:nth-child(3) .wechat-classify-two input[name=content]").each(function () {
                        ajaxdata.content_3.push($(this).val());
                    });
                }
            }
            ajaxdata.id = $("#data-form input[name=id]").val();
            $.post($("#do_action").val(), ajaxdata, function (data) {
                if (data.ret == 1) {
                    if (data.msg) {
                        alert(data.msg);
                    }
                    window.location.reload();
                } else {
                    if (data.msg) {
                        alert(data.msg);
                        return false;
                    }
                }
            }, 'json');
        });
    },

    fileupload: function () {
        webuploader1_obj.webuploader_init('#picker', $("input[name=file]"), '', '', true, 1, function (path) {
            console.log(path);
            $('.img img').show();
            $("#data_form input[name=file]").val(path);
        }, '*.txt');
    },
};

var webuploader1_obj = {
    webuploader_init: function (file_input_obj, filepath_input_obj, img_detail_obj, size) {//file_input_obj 上传图片元素 filepath_input_obj图片路径元素 img_detail_obj 追加图片元素
        var multi = (typeof(arguments[4]) == 'undefined') ? false : arguments[4];	//是否多张
        var queueSizeLimit = (typeof(arguments[5]) == 'undefined') ? 5 : arguments[5];	//最多上传张数
        var callback = arguments[6];	//回调函数
        var fileExt = (typeof(arguments[7]) == 'undefined') ? '*.jpg;*.png;*.gif;*.jpeg;*.bmp' : arguments[7];//可上传格式
        //console.log(file_input_obj);console.log(filepath_input_obj);console.log(img_detail_obj);console.log(size);
        var uploader = WebUploader.create({
            auto: true,
            swf: './static/common/js/webuploader/Uploader.swf',// swf文件路径
            server: '/admin.php/Wechat/fileupload',// 文件接收服务端。

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
                    img_detail_obj.html(webuploader_obj.img_link(jsonData.imgpath));
                } else {
                    callback(jsonData.imgpath, file_input_obj);
                }
            } else {
                alert('文件上传失败，出现未知错误！');
            }
        });

        // 文件上传失败，显示上传出错。
        uploader.on('uploadError', function (file, response) {
            var jsonData = eval('(' + response + ')');
            if (jsonData.status == 1) {

            } else {
                alert('文件上传失败，出现未知错误！');
            }
        });

        // 完成上传完了，成功或者失败，先删除进度条。
        uploader.on('uploadComplete', function (file, response) {

        });
    },

    img_link: function (img) {
        if (!img) {
            return;
        }
        return '<a href="' + img + '" target="_blank"><img src="' + img + '"></a>';
    }
}