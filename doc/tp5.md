1.application/common.php
serializeMysql()    //将序列化的信息存入到数据库中，进行转换
ajaxSuccess()       //ajax成功返回
ajaxFalse()         //ajax失败返回
logs()              //将信息记录到文件中
apilog()            //API接口明细记录

createnoncestr()    //随机字符串生成
createverifycode()  //生成验证码
xmltoarray()        //xml格式转数组
data2xml()          //数据XML编码
getdistance()       //根据经纬度计算距离
toweek()            //转换周几
time_refine()       //时间细分
get_url()           //获取当前全部url
get_domain()        //获取当前的网址加目录信息
get_client_ip()     //获取当前或者服务器端的IP
think_ucenter_md5() //系统非常规MD5加密方法
isMobilephone()     //检测手机号
isCreditNo()        //严格检测身份证号
httpdata()          //POST请求
https_request()     //GET请求

getaccesstoken()    //获取access_token
getjsapiticket()    //获得jsapi_ticket 临时票据
getticket()         //获取微信生成永久二维码
sendtemplate()      //发送模板消息
wxRefund()          //退款原路返回
wxEnterprisePayment()   //企业付款
wxCashRedPacket()       //现金红包

sendmessage()       //发送短信 生成短信日志
getExcel()          //导出至excel表格
getData()           //获取表格数据
account()           //数据统计
getcoupon()         //获得优惠券
getimage()          //php完美实现下载远程图片保存到本地

dataUpdate()        //更新数据库
dataChangeLog()     //积分/余额变动
dataInteractive()       //交互记录
interactivedel()    //交互记录删除
interactivehas()    //判断是否有此交互
interactivecount()  //计算给定交互数量
createOrdernumber() //生成随机订单编号
distributionBuildrelation() //建立三级分销关系
distributionBuildmoney()    //建立三级金钱关系

array_to_object()       //数组转对象
object_to_array()       //对象转数组

3.前台异步请求
<div class="hidden">
	<input type="hidden" name="page" value="1"/>					分页数
	<input type="hidden" name="url" value="{:url('News/index')}"/>	访问url
</div>
<ul class="content">												返回html赋值位置
</ul>
<form id="form_ajax">												异步请求附带数据
    <input type="hidden" name="category_id" value="1"/>
</form>

共性模块
{assign name="nodata" value="nothing_img"}		无内容样式 带图片
{include file="public/nodata"/}

{assign name="nodata" value="nothing"}			无内容样式 无图片
{include file="public/nodata"/}

{assign name="tool" value="address_pca"}
{include file="public/tool"/}					省市区滚动选择

{assign name="tool" value="address_select"}
{include file="public/tool"/}					订单收货地址
3.
后台保存页面
<form id="data-form">
    <input class="btn btn-danger" type="submit" value="提交保存">
</form>
<input type="hidden" id="do_action" value="{:url('News/newsadd')}">
<input type="hidden" id="do_jump" value="{:url('News/newsadd')}">
form 的id必须为data-form作为标识
submit 提交按钮
id=“do_action” 表示提交地址
id=“do_jump” 表示跳转地址，没有时返回上一页

后台列表分页，列表数据在相应的模板下的form.html中，
例：http://www.tp5.com/member.php/news/news.html
分页数据在 http://www.tp5.com/member.php/news/form.html文件中

<a href="#deal" data-url="{:url('Member/updatefield')}" data-tablename="news_category" data-fieldname="ishidden" data-value="1" data-text="确定删除吗？删除后将不可恢复">
	<button class="btn btn-danger btn-xs" title="删除"><i class="icon-trash"></i></button>
</a>
data_url：跳转url 一般固定为此链接，直接修改数据
data-tablename：要修改的表名
data-fieldname：要修改的字段名
data-value：要修改成的数值
data-text：点击时弹出确认框的文字，若无则没有确认框直接访问
title：鼠标悬停时显示的文字









