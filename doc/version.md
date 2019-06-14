2017/12/21
version 1.0

2017/12/28
version 1.1
1.后台目录菜单方式更迭，采用模块配置文件中的menu.php配置，并增加一键生成权限模块功能(生成后需要修改个别title字段)

2018/01/15
version 1.2
1.后台新增权限分配模块
2.后台增加一键同步后台会员和公众平台上会员数据功能
3.增加数据统计功能

2018/02/05
version 1.3
1.积分、余额日志合成1个方法dataChangeLog，适用于所有变动明细，数据库表更改为wechat_data_changelog
2.去掉interactivedel、interactivehas、interactivecount方法，interactive更名为dataInteractive
3.增加微信支付wxPay、订单金额原路退还wxRefund、企业付款wxEnterprisePayment、现金红包底层封装wxCashRedPacket

2018/03/01
version 1.3.1
1.增加小程序底层代码，在quickstart文件夹和applet控制器。
2.增加获取轮播图getCarousel、图片路径绝对路径转相对路径imgurlToAbsolute方法。

2018/03/03
version 1.3.2
1.生成订单编号createOrdernumber微修

2018/03/04
version 1.4
1.增加自定义菜单拖动功能

2018/03/05
version 1.4.1
1.邮件发送功能sendMail

2018/03/08
version 1.4.2
1.多选操作和列表页排序功能

2018/03/13
version 1.4.3
1.多选操作和排序应用到所有列表页并且数据库ishidden等字段更新为is_hidden
2.不继承微信环境的方法取消在构造函数中判断，方法前面加indep字段底层会自动判断。

2018/03/14
version 1.4.4
1.自定义菜单增加链接到小程序
2.小程序和公众号在同一框架下融合。
3.微信支付采用在当页支付，避免返回空白页。

2018/03/21
version 1.4.5
1.增加严格检测身份证号方法（isCreditNo），检测手机号方法名称修改（isMobilephone）
