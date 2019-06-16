
// 1、当有文件添加进来的时候
    // webuploader事件.当选择文件后，文件被加载到文件队列中，触发该事件。等效于 uploader.onFileueued = function(file){...} ，类似js的事件定义。
var imgs_num = [];
var $numLimit = uploader.options.fileNumLimit;

uploader.on( 'fileQueued', function( file ) {
   var $li = $(
            '<div id="' + file.id + '" class="file-item thumbnail">' +
            '<div class="img_box">' + 
            '<img>' +  '<div class="close_btn"></div>' + 
            '</div>' +
            '<div class="info">' + file.name + '</div>' +
            '</div>'
        ),
        $img = $li.find('img');
    // $list为容器jQuery实例
    $li.insertBefore($uploader_btn);

    // 1.1、创建缩略图
    // 如果为非图片文件，可以不用调用此方法。
    // thumbnailWidth x thumbnailHeight 为 100 x 100
    uploader.makeThumb( file, function( error, src ) {   //webuploader方法
        if ( error ) {
            $img.replaceWith('<span>不能预览</span>');
            return;
        }
        $img.attr( 'src', src );
    });
    imgs_num.push($('.file-item').attr('id'));
    if (imgs_num.length >= $numLimit) {
        $('.uploader_btn').hide();
    } 
    
    // 1.2、文件移除
    $li.on('click','.close_btn',function(){
        var $fileItem = $(this).parents('.thumbnail');
        uploader.removeFile(file);
        $fileItem.fadeOut(function(){
            $fileItem.remove();
        });
        imgs_num.pop($('.file-item').attr('id'));
        if (imgs_num.length < $numLimit) {
            $('.uploader_btn').show();
        }
    }) 
});

//2、文件上传过程中创建进度条实时显示。
uploader.on( 'uploadProgress', function( file, percentage ) {
    console.log(2222)
    var $li = $( '#'+file.id ),
        $percent = $li.find('.progress span');
    // 避免重复创建
    if ( !$percent.length ) {
        $percent = $('<p class="progress"><span></span></p>')
            .prependTo( $li )
            .find('span');
    }
    $percent.css( 'width', percentage * 100 + '%' );
    // var status_pre = file.size*percentage-arr_md5.length*2*1024*1024;
    // var speed = ((status_pre/1024)/times).toFixed(0);

});

// 3、文件上传错误信息提示
console.log(uploader)
console.log(uploader.options.fileNumLimit)
uploader.on('error',function(handler,file){
    // console.log($('.file-item'))
    var $numLimit = uploader.options.fileNumLimit;
    if (handler == 'Q_EXCEED_NUM_LIMIT') {
        alert('最多上传' + $numLimit + '份文件');
    } else if (handler == 'F_DUPLICATE') {
        alert('文件已在队列中');
    } else if (handler == 'Q_TYPE_DENIED') {
        alert('请上传' + uploader.options.accept[0].extensions + '格式的文件');
    } else if (handler == 'F_EXCEED_SIZE') {
        alert('文件大小不能超过' + (parseFloat(uploader.options.fileSingleSizeLimit / 1024).toFixed(2)) + 'KB');//保留2位小数，并且四舍五入
    }
})

// 4、图片上传成功


