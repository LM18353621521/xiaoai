 $(function () {
        //图片上传
            //选择要上传的所有文件
            fileList = [];
        //当前选择上传的文件
        var curFile;
        // 选择按钮change事件，实例化fileReader,调它的readAsDataURL并把原生File对象传给它，
        // 监听它的onload事件，load完读取的结果就在它的result属性里了。它是一个base64格式的，可直接赋值给一个img的src.
        $(".add-img input").on('change', function (e) {
            //上传过图片后再次上传时限值数量
            var numold =$(this).parent().prev('.file-list').find("li").length;
            console.log(numold)
            if(numold >= 9){
                alert('最多上传9张图片');
                return;
            }
            //限制单次批量上传的数量
            var num = e.target.files.length;
            var numall = numold + num;
            if(num >9 ){
               alert('最多上传9张图片');
               return;
            }else if(numall > 9){
                alert('最多上传9张图片');
                return;
            }
            //原生的文件对象，相当于$(".add-img input").get(0).files;//files[0]为第一张图片的信息;
            curFile = this.files;
            //将FileList对象变成数组
            fileList = fileList.concat(Array.from(curFile));
            //console.log(fileList);
            for (var i = 0, len = curFile.length; i < len; i++) {
                reviewFile(curFile[i],$(this));
            }
            $(this).parent().prev('.file-list').fadeIn(2500);
        })


        function reviewFile(file,_this) {
            //实例化fileReader,
            var fd = new FileReader();
            //获取当前选择文件的类型
            var fileType = file.type;
            //调它的readAsDataURL并把原生File对象传给它，
            fd.readAsDataURL(file);//base64
            //监听它的onload事件，load完读取的结果就在它的result属性里了
            fd.onload = function () {
                if (/^image\/[jpeg|png|jpg|gif]/.test(fileType)) {
                    _this.parent().prev('.file-list').append('<li class="file-item"><img class="img-pic" src="' + this.result + '" alt=""><span class="file-del">删除</span></li>').children(':last').hide().fadeIn(1000);
                } else {
                    _this.parent().prev('.file-list').append('<li class="file-item"><span class="file-name">' + file.name + '</span><span class="file-del">删除</span></li>')
                }

            }
        }

        //点击删除按钮事件：
        $(".file-list").on('click', '.file-del', function () {
            let $parent = $(this).parent();
            let index = $parent.index();
            fileList.splice(index, 1);
            $parent.fadeOut(850, function () {
                $parent.remove()
            });
        });
        //点击上传按钮事件：
        // $button.on('click', function () {
        //     var name = $('#name').val();
        //     if (name == '') {
        //         alert('请输入商品名称');
        //         return;
        //     }
        //      if(fileList.length > 6){
        //             alert('最多允许上传6张图片');
        //             return;
        //     } else {
        //         var formData = new FormData();
        //         for (var i = 0, len = fileList.length; i < len; i++) {
        //             formData.append('upfile[]', fileList[i]);
        //         }
        //         formData.append('name', name);
        //         $.ajax({
        //             url: '',
        //             type: 'post',
        //             data: formData,
        //             dataType: 'json',
        //             processData: false,
        //             contentType: false,
        //             success: function (data) {
                       
        //             }
        //         })
        //     }
        // })
    })