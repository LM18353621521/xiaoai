UEditor 富文本
<script type="text/javascript" charset="utf-8" src="__STATIC__/common/js/ueditor/ueditor.config.js"></script>
<script type="text/javascript" charset="utf-8" src="__STATIC__/common/js/ueditor/ueditor.all.min.js"> </script>
<script type="text/javascript" charset="utf-8" src="__STATIC__/common/js/ueditor/lang/zh-cn/zh-cn.js"></script>

$(document).ready(function () {
    var ue = UE.getEditor('editor');
});

<textarea id="editor"  class="myEditor" name="privatestep" style="width:1024px;height:500px;">{$data.fuwenben}</textarea>