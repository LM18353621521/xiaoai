二维码生成插件

demo：（单二维码）
var qrcode = new QRCode(document.getElementById("qrcode"), {
    width : 150,//设置宽高
    height : 150
});
qrcode.makeCode(linkurl);


demo：（二维码列表，多用于后台）
<td align="center">
	<span id="qrcode_{$vo.id}" class="qrcode" linkurl="{$vo.linkurl}"></span>
</td>
				
$(".qrcode").each(function(){
	var qrcode = new QRCode(document.getElementById($(this).attr("id")), {
	    width : 150,//设置宽高
	    height : 150
	});
	qrcode.makeCode($(this).attr("linkurl"));
});
