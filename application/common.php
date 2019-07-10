<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
#TODO
//******************************************封装函数*******************************************

/**
 * 将序列化的信息存入到数据库中，进行转换  2017-10-15
 * @param mixed $info 信息    $type 0为序列化，1为反序列化
 */
function serializeMysql($info, $type = 0)
{
    if ($type == 0) {
        return addslashes(serialize($info));
    } else {
        return unserialize(stripslashes($info));
    }
}

// 递归删除文件夹
function delFile($path, $delDir = FALSE)
{
    if (!is_dir($path))
        return FALSE;
    $handle = @opendir($path);
    if ($handle) {
        while (false !== ($item = readdir($handle))) {
            if ($item != "." && $item != "..")
                is_dir("$path/$item") ? delFile("$path/$item", $delDir) : unlink("$path/$item");
        }
        closedir($handle);
        if ($delDir) return rmdir($path);
    } else {
        if (file_exists($path)) {
            return unlink($path);
        } else {
            return FALSE;
        }
    }
}

/**
 * 把返回的数据集转换成Tree
 * @access public
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
{
    // 创建Tree
    $tree = array();
    if (is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}

/**
 * ajax成功返回 2017-10-15
 * @param string $data 数据数组 $msg 提示信息   $url 跳转url  $attach 附加数据
 */
function ajaxSuccess($data = '', $msg = '操作成功', $url = '', $attach = '')
{
    $data = array(
        'ret' => 1,
        'data' => $data,
        'msg' => $msg,
        'url' => $url,
        'attach' => $attach
    );
    return $data;
}

/**
 * ajax失败返回 2017-10-15
 * @param string $msg
 */
function ajaxFalse($msg = '操作失败', $url = '', $attach = '', $ret = 0)
{
    $data = array(
        'ret' => $ret,
        'msg' => $msg,
        'url' => $url,
        'attach' => $attach
    );
    return $data;
}


function xa_encrypt($str)
{
    return md5(config("AUTH_CODE") . $str);
}

/**
 * 将信息记录到文件中 2017-10-15
 * $info 需要打印的信息
 */
function logs($info)
{
    $path = RUNTIME_PATH . 'log/' . date("Ym") . '/' . date("d_H_i_s") . '.log';
    $f = fopen($path, 'a');
    $file = fwrite($f, print_r($info, true));
    $file = fwrite($f, print_r(PHP_EOL . PHP_EOL, true));
}

/**
 * API接口明细记录    2017-10-15
 * @param $classify 模块、$type 变动类型、$url 接口地址、$content 发送内容、$result 返回值
 */
function apilog($uid, $classify, $type, $url, $content, $result)
{
    $destination = RUNTIME_PATH . 'log' . DS . date("Ym") . DS . date('d') . 'api.log';
    $path = dirname($destination);
    !is_dir($path) && mkdir($path, 0755, true);

    if (is_file($destination) && floor(2097152) <= filesize($destination)) {
        rename($destination, dirname($destination) . DS . time() . '-' . basename($destination));
    }

    $now = '[ 创建时间 ] ' . date("Y-m-d H:i:s");
    $uid = '[ UID ] ' . $uid;
    $localurl = '[ 运行链接 ] ' . get_url();
    $classify = '[ 模块 ] ' . $classify;
    $type = '[ 类型 ] ' . $type;
    $url = '[ 接口链接 ] ' . $url;
    $message = "---------------------------------------------------------------\r\n{$now}\r\n{$uid}\r\n{$localurl}\r\n{$classify}\r\n{$type}\r\n{$url}\r\n";

    $f = fopen($destination, 'a');
    $file = fwrite($f, print_r($message . '[ 接口内容 ] ', true));
    $file = fwrite($f, print_r($content, true));
    $file = fwrite($f, print_r("\r\n[ 接口返回结果 ] ", true));
    $file = fwrite($f, print_r($result, true));
    $file = fwrite($f, print_r("\r\n\r\n", true));
    return true;
}

/**
 * 随机字符串生成    2017-10-15
 * @param $length 字符串长度
 */
function createnoncestr($length = 16)
{
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
}

/**
 * 生成验证码    2017-10-15
 */
function createverifycode($length = 4)
{
    $number = "";
    for ($i = 0; $i < $length; $i++) {
        $number .= rand(0, 9);
    }
    return $number;
}

/**
 * xml格式转数组    2017-10-15
 */
function xmltoarray($xml)
{
    //将XML转为array
    $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $array_data;
}

/**
 * 数据XML编码  不含CDATA 2017-10-15
 * @param  object $xml XML对象    mixed  $data 数据    string $item 数字索引时的节点名称
 */
function data2xml($xml, $data, $item = 'item')
{
    foreach ($data as $key => $value) {
        /* 指定默认的数字key */
        is_numeric($key) && $key = $item;

        /* 添加子元素 */
        if (is_array($value) || is_object($value)) {
            $child = $xml->addChild($key);
            $this->data2xml($child, $value, $item);
        } else {
            $child = $xml->addChild($key, $value);
        }
    }
}

/**
 * 根据经纬度计算距离    2017-10-15
 * @param $lat经度 $lng维度
 */
function getdistance($lat1, $lng1, $lat2, $lng2)
{
    $EARTH_RADIUS = 6378.137;
    $radLat1 = $this->rad($lat1);
    $radLat2 = $this->rad($lat2);
    $a = $radLat1 - $radLat2;
    $b = $this->rad($lng1) - $this->rad($lng2);
    $s = 2 * asin(sqrt(pow(sin($a / 2), 2) +
            cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
    $s = $s * $EARTH_RADIUS;
    $s = round($s, 3);//千米为单位，3位小数
    return $s;
}

/**
 * rad  2017-10-15
 */
function rad($d)
{
    return $d * 3.1415926535898 / 180.0;
}

/**
 * 转换周几    2017-10-15
 * @param $week 周几数字格式
 */
function toweek($week)
{
    switch ($week) {
        case 0:
            return '日';
        case 1:
            return '一';
        case 2:
            return '二';
        case 3:
            return '三';
        case 4:
            return '四';
        case 5:
            return '五';
        case 6:
            return '六';
        default:
            return false;
    }
}

/**
 * 时间细分    2017-10-15
 * @param $createtime 时间戳
 */
function time_refine($createtime)
{
    $time = time() - $createtime;
    $days = intval($time / 86400);
    //计算小时数
    $remain = $time % 86400;
    $hours = intval($remain / 3600);
    //计算分钟数
    $remain = $remain % 3600;
    $mins = intval($remain / 60);
    //计算秒数
    $secs = $remain % 60;
    if ($days != 0) {
        $time = $days . '天前';
    } elseif ($hours != 0) {
        $time = $hours . '小时前';
    } elseif ($mins != 0) {
        $time = $mins . '分钟前';
    } else {
        $time = $secs . '秒前';
    }
    return $time;
}

/**
 * 时间细分
 * @param $the_time   时间
 * @param int $type 1-时间戳，2-日期格式
 * @return bool|string
 */
function dayfast($the_time, $type = 1)
{
    $now_time = date("Y-m-d H:i:s", time());
    $now_time = strtotime($now_time);
    $show_time = $type == 1 ? $the_time : strtotime($the_time);
    $dur = $now_time - $show_time;
    if ($dur < 0) {
        return $the_time;
    } else {
        if ($dur < 60) {
            return $dur . '秒前';
        } else {
            if ($dur < 3600) {
                return floor($dur / 60) . '分钟前';
            } else {
                if ($dur < 86400) {
                    return floor($dur / 3600) . '小时前';
                } else {
                    if ($dur < 259200) {//3天内
                        return floor($dur / 86400) . '天前';
                    } else {
                        $the_time = date("Y-m-d", $show_time);
                        return $the_time;
                    }
                }
            }
        }
    }
}


/**
 * 获取当前全部url    2017-10-15
 */
function get_url()
{
    if (!input('?server.REQUEST_SCHEME')) {
        $protocol = input('server.SERVER_PORT') == 443 ? "https" : "http";
    } else {
        $protocol = input('server.REQUEST_SCHEME');
    }
    return $protocol . '://' . input('server.HTTP_HOST') . input('server.REQUEST_URI');
}

/**
 * 获取当前的网址加目录信息    2017-10-15
 */
function get_domain()
{
    $base = request()->root();
    $root = strpos($base, '.') ? ltrim(dirname($base), DS) : $base;
    if ('' != $root) {
        $root = '/' . ltrim($root, '/');
    }
    if (!input('?server.REQUEST_SCHEME')) {
        $protocol = input('server.SERVER_PORT') == 443 ? "https" : "http";
    } else {
        $protocol = input('server.REQUEST_SCHEME');
    }
    return $protocol . '://' . input('server.HTTP_HOST') . $root;
}

/**
 * 获取当前或者服务器端的IP    2017-10-15
 */
function get_client_ip($type = 1)
{
    if ($type == 1) {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    } else if ($type == 2) {
        return isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '0.0.0.0';
    }
}

/**
 * 系统非常规MD5加密方法    2017-10-15
 * @param  string $str 要加密的字符串
 */
function think_ucenter_md5($str, $key = 'ThinkUCenter')
{
    return '' === $str ? '' : md5(sha1($str) . $key);
}

/**
 * 检测手机号    2017-10-15
 */
function isMobilephone($mobilehone = '')
{
    if (preg_match("/1[345789]{1}\d{9}$/", $mobilehone)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 检测身份证号    2018-03-21
 */
function isCreditNo($vStr)
{
    $vCity = array(
        '11', '12', '13', '14', '15', '21', '22',
        '23', '31', '32', '33', '34', '35', '36',
        '37', '41', '42', '43', '44', '45', '46',
        '50', '51', '52', '53', '54', '61', '62',
        '63', '64', '65', '71', '81', '82', '91'
    );
    if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $vStr)) return false;
    if (!in_array(substr($vStr, 0, 2), $vCity)) return false;
    $vStr = preg_replace('/[xX]$/i', 'a', $vStr);
    $vLength = strlen($vStr);
    if ($vLength == 18) {
        $vBirthday = substr($vStr, 6, 4) . '-' . substr($vStr, 10, 2) . '-' . substr($vStr, 12, 2);
    } else {
        $vBirthday = '19' . substr($vStr, 6, 2) . '-' . substr($vStr, 8, 2) . '-' . substr($vStr, 10, 2);
    }
    if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) return false;
    if ($vLength == 18) {
        $vSum = 0;
        for ($i = 17; $i >= 0; $i--) {
            $vSubStr = substr($vStr, 17 - $i, 1);
            $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr, 11));
        }
        if ($vSum % 11 != 1) return false;
    }
    return true;
}


/**
 * POST请求    2017-10-15
 */
function httpdata($url, $data)
{

    $data = json_encode($data, JSON_UNESCAPED_UNICODE);
    //$data = JSON($data, false);

    $ch = curl_init();
    //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
//    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $status = curl_exec($ch);
    curl_close($ch);
    $res = json_decode($status, true);
    return $res;
}

/**
 * GET请求    2017-10-15
 */
function https_request($url)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($curl);
    if (curl_errno($curl)) {
        return 'ERROR ' . curl_error($curl);
    }
    curl_close($curl);
    $data = json_decode($data, true);
    return $data;
}

//******************************************微信接口*******************************************
#TODO
//获取access_token  2017-10-15
function getaccesstoken($uid, $type = 'wechat')
{
    $config = db(\tname::weixin_config)->where(['uid' => $uid, 'type' => $type])->find();
    if (($config['access_token'] == null) || ($config['access_time'] + 7200 < time())) {
        $Token = new \wechat\Token();
        $result = $Token->gettoken($config['appid'], $config['appsecret'], array('uid' => $uid));

        if (isset($result['access_token'])) {
            $configdata = [
                'access_token' => $result['access_token'],
                'access_time' => time(),
            ];
            db(\tname::weixin_config)->where(['uid' => $uid])->update($configdata);
        }

        isset($result['access_token']) ? $access_token = $result['access_token'] : $access_token = '';
    } else {
        $access_token = $config['access_token'];
    }

    return $access_token;
}

/**
 * 获得jsapi_ticket 临时票据    2017-10-15
 * @param $uid
 */
function getjsapiticket($uid, $type = 'wechat')
{
    $filename = './static/wechat/common/json/jsapi_ticket' . $uid . '.json';
    file_exists($filename) && $data = json_decode(file_get_contents($filename));

    if (!isset($data) || $data->expire_time < time()) {
        $access_token = getaccesstoken($uid, $type);
        $Token = new \wechat\Token();
        $result = $Token->getjsapi($access_token, ['uid' => $uid]);
        if (isset($result['ticket'])) {
            $data = (object)array();
            $data->expire_time = time() + 7000;
            $data->jsapi_ticket = $result['ticket'];

            file_put_contents($filename, json_encode($data));
        }
        isset($result['ticket']) ? $ticket = $result['ticket'] : $ticket = '';
    } else {
        $ticket = $data->jsapi_ticket;
    }

    return $ticket;
}

/**
 * 获取微信生成永久二维码        2017-10-15
 * @param $sceneid 布景值 $type 1为字符串 2为整型
 */
function getticket($uid, $sceneid, $type = 1, $webtype = 'wechat')
{
    if ($type == 1) {
        $data = array(
            'action_name' => 'QR_LIMIT_STR_SCENE',
            'action_info' => array(
                'scene' => array(
                    'scene_str' => $sceneid,
                ),
            ),
        );
    } else {
        $data = array(
            'action_name' => 'QR_LIMIT_SCENE',
            'action_info' => array(
                'scene' => array(
                    'scene_id' => $sceneid,
                ),
            ),
        );
    }

    $access_token = getaccesstoken($uid, $webtype);
    $Ticket = new \wechat\Ticket();
    $res = $Ticket->getticket($data, $access_token, ['uid' => $uid]);

    return $res;
}

/**
 * 发送模板消息    2017-10-15
 * @param $data 特定格式数组
 */
function sendtemplate($uid, $data, $type = 'wechat')
{
    $template['touser'] = $data['openid'];
    $template['template_id'] = $data['tid'];
    $template['url'] = $data['url'];
    foreach ($data['content'] as $key => $value) {
        $template['data'][$key] = array(
            'value' => $value,
            'color' => '#173177'
        );
    }
    $access_token = getaccesstoken($uid, $type);
    $Message = new \wechat\Message();
    $Message->template($template, $access_token, array('uid' => $uid));
}
/**
 * 小程序发送模板消息
 */
function applet_template($template)
{
    $access_token = getaccesstoken(2, 'applet');
    $url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=' . $access_token;
    $result = httpdata($url, $template);
    return $result;
}


//微信支付  2018-02-06
//$openid 微信号 $money 入账金额 $ordernumber 订单号 $body 备注
function wxPay($uid, $openid, $money, $ordernumber, $url = [], $body, $trade_type, $type = 'wechat')
{
    $config = db(\tname::weixin_config)->where(['uid' => $uid, 'type' => $type])->find();
    $pay = db(\tname::weixin_pay)->where(['uid' => $uid, 'type' => $type])->find();
    $wxpay = serializeMysql($pay['wxpay'], 1);

    $body || $body = $config['name'];

    $param = array(
        'out_trade_no' => $ordernumber,
        'total_fee' => $money,
        'appid' => $config['appid'],
        'mch_id' => $wxpay['mchid'],
        'partnerkey' => $wxpay['partnerkey'],
        'body' => $body,
        'openid' => $openid,
        'uid' => $uid,
        'notify_url' => $url['notify_url'],
        'success_url' => $url['success_url'],
        'fail_url' => $url['fail_url'],
    );
    $Wxpay = new \wechat\Wxpay();
    $result = $Wxpay->getprepayid($param, $trade_type);
    $prepay_id = $result['prepay_id'];
    $ApiParameters = $Wxpay->getparameters($prepay_id, $param);
    if ($trade_type == 'JSAPI') {
        return [$param, $ApiParameters, $prepay_id];
    }
    if ($trade_type == 'NATIVE') {
        return $result;
    }


}

//退款原路返回    2017-10-15
//$openid 微信号  $ordernumber 订单号 $total_fee 订单总金额 $refund_fee退款金额
function wxRefund($uid, $openid, $ordernumber, $total_fee, $refund_fee, $type = 'wechat')
{
    $config = db(\tname::weixin_config)->where(['uid' => $uid, 'type' => $type])->find();
    $pay = db(\tname::weixin_pay)->where(['uid' => $uid, 'type' => $type])->find();
    $wxpay = serializeMysql($pay['wxpay'], 1);

    $param = array(
        'uid' => $uid,
        'appid' => $config['appid'],
        'mch_id' => $wxpay['mchid'],
        'partnerkey' => $wxpay['partnerkey'],
        'openid' => $openid,
        'ordernumber' => $ordernumber,
        'total_fee' => $total_fee,
        'refund_fee' => $refund_fee,
        'path1' => '/static/common/apiclient/' . $uid . $type . '/apiclient_cert.pem',    //证书1路径
        'path2' => '/static/common/apiclient/' . $uid . $type . '/apiclient_key.pem'    //证书2路径
    );

    $Wxpay = new \wechat\Wxpay();
    $result = $Wxpay->refund($param);
    if ($result['result_code'] === 'SUCCESS') {
        return [1];
    } else {
        apilog(2, 'home', 'wxRefund', '', $result, []);
        return [0, $result['err_code_des']];
    }

}

//企业付款  2018-02-05
//$openid 微信号 $money 入账金额 $ordernumber 订单号 $desc 备注
function wxEnterprisePayment($uid, $openid, $money, $ordernumber, $desc = '企业付款', $type = 'wechat')
{
    $config = db(\tname::weixin_config)->where(['uid' => $uid, 'type' => $type])->find();
    $pay = db(\tname::weixin_pay)->where(['uid' => $uid, 'type' => $type])->find();
    $wxpay = serializeMysql($pay['wxpay'], 1);

    $parameter = [
        'mch_appid' => $config['appid'],    //公众平台APPID
        'mchid' => $wxpay['mchid'],        //商户号
        'partnerkey' => $wxpay['partnerkey'],    //密钥
        'money' => $money,    //商户平台密钥
        'openid' => $openid,    //用户OPENID
        'ordernumber' => $ordernumber,    //付款订单号，不能重复
        'desc' => $desc,
        'path1' => '/static/common/apiclient/' . $uid . $type . '/apiclient_cert.pem',    //证书1路径
        'path2' => '/static/common/apiclient/' . $uid . $type . '/apiclient_key.pem'    //证书2路径
    ];
    $Pay = new \wechat\Pay();
    $result = $Pay->pay($parameter);

    if ($result['result_code'] === 'SUCCESS') {
        return [1];
    } else {
        return [0, $result['err_code_des']];
    }
}

//现金红包  2018-02-06
//$openid 微信号 $money 入账金额 $ordernumber 订单号 $desc 备注
function wxCashRedPacket($uid, $openid, $money, $ordernumber, $act_name = '活动', $type = 'wechat')
{
    $config = db(\tname::weixin_config)->where(['uid' => $uid, 'type' => $type])->find();
    $pay = db(\tname::weixin_pay)->where(['uid' => $uid, 'type' => $type])->find();
    $wxpay = serializeMysql($pay['wxpay'], 1);

    $parameter = [
        'mch_appid' => $config['appid'],    //公众平台APPID
        'mchid' => $wxpay['mchid'],        //商户号
        'partnerkey' => $wxpay['partnerkey'],    //密钥
        'money' => $money,
        'openid' => $openid,    //用户OPENID
        'mch_billno' => $ordernumber,    //付款订单号，不能重复
        'send_name' => $config['name'],
        'wishing' => $config['name'],
        'act_name' => $act_name,
        'remark' => $config['name'],
        'path1' => '/static/common/apiclient/' . $uid . $type . '/apiclient_cert.pem',    //证书1路径
        'path2' => '/static/common/apiclient/' . $uid . $type . '/apiclient_key.pem'    //证书2路径
    ];

    $Pay = new \wechat\Pay();;
    $result = $Pay->redbagpay($parameter);

    if ($result['result_code'] === 'SUCCESS') {
        return [1];
    } else {
        return [0, $result['err_code_des']];
    }
}

//******************************************其他接口*******************************************
#TODO
/**
 * 发送短信 生成短信日志    2017-10-15
 * @param $mobilephone 手机号  $content 短信内容 $type短信类型
 */
function sendmessage($uid, $mobilephone, $content, $type)
{
    $Message = new \zzymessage\Message();
    $result = $Message->sendmessage($mobilephone, $content, array('uid' => WID));
    $logdata = array(
        'uid' => $uid,
        'type' => $type,
        'mobilephone' => $mobilephone,
        'content' => $content,
        'result' => $result,
        'create_time' => time()
    );
    dataUpdate(\tname::data_message, $logdata);
    return $result;
}


/**
 * 阿里支付申请退款
 * @param $uid
 * @param $openid
 * @param $ordernumber
 * @param $total_fee
 * @param $refund_fee
 * @param string $type
 */
function aliRefund($trade_no, $total_fee, $refund_fee, $type = 'alipay')
{
    $apipay = new \alipay\Refund();
    $params = array(
        'trade_no' => $trade_no,
        'refund_amount' => $refund_fee,
    );
    $result = $apipay::exec($params);
    if ($result['code'] === '10000') {
        return [1];
    } else {
        apilog(2, 'home', 'aliRefund', '', $result, []);
        return [0, $result['sub_msg']];
    }
}

/**
 * 佣金支付-退款
 */
function incomeRefund($order,$psy_type)
{
    $user = db(\tname::vip)->where(array('id'=>$order['vip_id']))->find();
    if($user['source']!=2){
        $user = db(\tname::vip)->where(array('mobile'=>$user['mobile'],'source'=>2))->find();
    }
    if(empty($user)){
        return [0, '参数错误'];
    }
    $res = dataChangeLog(2, 'income', 'distribution', $user['id'], $order['pay_money'], $order['order_id'], '订单退款');
    if ($res) {
        return [0, '退款失败'];
    }
    return [1, '退款成功'];

}

/**
 * 阿里短信
 * @param $uid
 * @param $mobilephone
 * @param $content
 * @param $type
 * @return mixed|string
 */
function sendSmsAli($uid, $mobilephone, $content, $code = null, $type = 0)
{
    set_time_limit(0);
    $Message = new \aliyundysms\api_demo\SmsDemo();
    $result = $Message->sendSms($mobilephone, $content, array('uid' => WID));

    $data = array(
        'mobile' => $mobilephone,
        'content' => $content,
    );
    apilog(2, 'magaliyun', 'sendSms', "", $data, $result);
    return $result;
}

/**
 * 聚合短信
 */
function sendSmsjh($mobile, $content = null, $code = null, $type = 1)
{
    $smsApi = new \juhe\JuheApi();
    $config = tpCache('sms');
    $tpl_ids = array(
        '1' => 166131,//模板ID
        '2' => 166131,//模板ID
        '3' => 166131,//模板ID
        '4' => 166131,//模板ID
        '5' => 166131,//模板ID
        '6' => 166131,//模板ID
    );
    $tpl_values = array(
        '1' => '#code#=' . $code . "&#company#={$config['signName']}",
        '2' => '#code#=' . $code . "&#company#={$config['signName']}",
        '3' => '#code#=' . $code . "&#company#={$config['signName']}",
        '4' => '#code#=' . $code . "&#company#={$config['signName']}",
        '5' => '#code#=' . $code . "&#company#={$config['signName']}",
        '6' => '#code#=' . $code . "&#company#={$config['signName']}",
    );
    $tpl_id = $tpl_ids[$type];
    $tpl_value = $tpl_values[$type];
    $params = array(
        'key'=>$config['apiKey'],
        'mobile' => $mobile, //接受短信的用户手机号码
        'tpl_id' => intval($tpl_id), //您申请的短信模板ID，根据实际情况修改
        'tpl_value' => $tpl_value,//您设置的模板变量，根据实际情况修改
    );
    $result = $smsApi->juhecurl($params, 1);
    $result1 = json_decode($result, true);
    $smslog = array(
        'type' => $type,
        'mobile' => $mobile,
        'content' => $tpl_value,
        'result' => $result,
        'create_time' => time(),
        'error_code' => $result1['error_code']
    );
    dataUpdate(\tname::data_message, $smslog);
    return $result1;
}


/**
 * 系统邮件发送函数
 * @param string $tomail 接收邮件者邮箱
 * @param string $name 接收邮件者名称
 * @param string $subject 邮件主题
 * @param string $body 邮件内容
 * @param string $attachment 附件列表
 * @return boolean
 * @author static7 <static7@qq.com>
 */
function sendMail($uid, $tomail, $subject = '', $body = '', $attachment = null)
{
    $config = db(tname::email_config)->where(['uid' => $uid])->find();

    $mail = new  PHPMailer\PHPMailer\PHPMailer();           //实例化PHPMailer对象
    $mail->CharSet = 'UTF-8';           //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP();                    // 设定使用SMTP服务
    $mail->SMTPDebug = 0;               // SMTP调试功能 0=关闭 1 = 错误和消息 2 = 消息
    $mail->SMTPAuth = true;             // 启用 SMTP 验证功能
    $mail->SMTPSecure = 'ssl';          // 使用安全协议
    $mail->Host = "smtp.qq.com"; // SMTP 服务器
    $mail->Port = 465;                  // SMTP服务器的端口号
    $mail->Username = $config['email'];    // SMTP服务器用户名
    $mail->Password = "eninesvxxsyeibai";     // SMTP服务器密码
    $mail->SetFrom($config['email']);
    $replyEmail = '';                   //留空则为发件人EMAIL
    $replyName = '';                    //回复名称（留空则为发件人名称）
    $mail->AddReplyTo($replyEmail);
    $mail->Subject = $subject;
    $mail->MsgHTML($body);
    $mail->AddAddress($tomail);
    if (is_array($attachment)) { // 添加附件
        foreach ($attachment as $file) {
            is_file($file) && $mail->AddAttachment($file);
        }
    }
    $result = $mail->Send() ? true : $mail->ErrorInfo;
    apilog($uid, 'email', 'sendemail', '',
        ['tomail' => $tomail, 'fromemail' => $config['email'], 'subject' => $subject, 'body' => $body],
        $result);
    return $result;
}

//******************************************grand  cut-off rule*******************************************
#TODO
/**
 * 导出至excel表格        2017-10-15
 * @param: $fileName表格名字   $headArr表头  $data导出数据  $msg批注
 */
function getExcel($fileName, $headArr, $data, $msg)
{
    //对数据进行检验
    if (empty($data) || !is_array($data)) {
        die("data must be a array");
    }

    //检查文件名
    if (empty($fileName)) {
        die("filename must be existed");
    }

    //获取总列数
    $totalColumn = count($headArr);
    $charColumn = chr($totalColumn + 64);
    $date = date("Y-m-d", time());
    $fileName .= "_{$date}.xls";

    //创建PHPExcel对象
    $objPHPExcel = new \PHPExcel();

    $objPHPExcel->setActiveSheetIndex(0);    //设置当前的sheet  操作第一个工作表
    $objActSheet = $objPHPExcel->getActiveSheet();    //添加数据
    $phpstyle = new \PHPExcel_Style_Color();

    //表头变颜色
    $objActSheet->getStyle('A1:' . $charColumn . '1')->getFont()->getColor()->setARGB($phpstyle::COLOR_BLUE);    //设置颜色

    //设置批注
    $objActSheet->getStyle('A2')->getFont()->getColor()->setARGB($phpstyle::COLOR_RED);
    $objActSheet->setCellValue('A2', $msg);    //给单个单元格设置内容
    $objActSheet->mergeCells('A2:' . $charColumn . '2');    //合并单元格

    //设置表头
    $key = ord("A");
    foreach ($headArr as $v) {
        $colum = chr($key);
        $objActSheet->setCellValue($colum . '1', $v);
        $objActSheet->getColumnDimension($colum)->setWidth(20);
        $key++;
    }

    //写入数据
    $column = 3;
    foreach ($data as $key => $rows) {     //行写入
        $span = ord("A");
        foreach ($rows as $keyName => $value) {    // 列写入
            $j = chr($span);
            if ($keyName !== 'img') {
                $objActSheet->setCellValue($j . $column, $value);
            } elseif ($keyName == 'img') {
                $objActSheet->getRowDimension($column)->setRowHeight(60);    //设置行高
                $objDrawing = new \PHPExcel_Worksheet_Drawing();
                $objDrawing->setPath($value);
                $objDrawing->setWidth(50);
                $objDrawing->setHeight(50);
                $objDrawing->setCoordinates($j . $column);
                $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
            }
            $span++;
        }
        $column++;
    }

    //处理中文输出问题
    $fileName = iconv("utf-8", "gb2312", $fileName);

    //接下来当然是下载这个表格了，在浏览器输出就好了
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=\"$fileName\"");
    header('Cache-Control: max-age=0');

    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output'); //文件通过浏览器下载
}

/**
 * 获取表格数据   2017-10-15
 * @param: $fileName表格名字   $headArr表头  $data导出数据  $msg批注
 */
function getData($file_dir)
{
//    vendor('PHPExcel.PHPExcel');
    $PHPReader = new \PHPExcel_Reader_Excel2007();
    $PHPReader = new \PHPExcel_Reader_Excel5();


    if (!$PHPReader->canRead($file_dir)) {
        $PHPReader = new \PHPExcel_Reader_Excel5();
        if (!$PHPReader->canRead($file_dir)) {
            return array(0, '请上传文档');
        }
    }

    //载入文件
    $PHPExcel = $PHPReader->load($file_dir);

    //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
    $currentSheet = $PHPExcel->getSheet(0);

    //获取总行数
    $allRow = $currentSheet->getHighestRow();
    $allColumn = $currentSheet->getHighestColumn();
    $allColumn = ord($allColumn);

    for ($j = 2; $j <= $allRow; $j++) {
        for ($i = 65; $i <= $allColumn; $i++) {
            $colum = chr($i);
            $data[$j][$colum] = $PHPExcel->getActiveSheet()->getCell($colum . $j)->getValue();
        }
    }
    return array(1, $data);
}


/**
 * 获得优惠券   2017-10-15
 * @param $openid 变动人员、$type 获得优惠券突降、$couponid 优惠券ID
 */
function getcoupon($uid, $openid, $type, $couponid = array())
{
    $couponList = db(\tname::coupon)->where(array('uid' => $uid, 'id' => array('in', $couponid)))->select();
    $vip = db(\tname::vip)->where(array('uid' => $uid, 'openid' => $openid))->find();

    foreach ($couponList as $key => $value) {
        $data[] = array(
            'uid' => $uid,
            'openid' => $openid,
            'nickname' => $vip['nickname'],
            'type' => $type,
            'couponid' => $value['id'],
            'classify' => $value['classify'],
            'category' => $value['category'],
            'name' => $value['name'],
            'discount' => $value['discount'],
            'price' => $value['price'],
            'limit' => $value['limit'],
            'notice' => $value['notice'],
            'stime' => $value['stime'],
            'etime' => $value['etime'],
            'createtime' => time(),
        );
    }
    //添加至我的优惠券
    $res = db(\tname::coupon_mycoupon)->insertAll($data);
    if ($res) {
        db(\tname::coupon)->where(array('uid' => $uid, 'id' => array('in', $couponid)))->setInc('actualsales');
        db(\tname::coupon)->where(array('uid' => $uid, 'id' => array('in', $couponid)))->setInc('configsales');
        db(\tname::coupon)->where(array('uid' => $uid, 'id' => array('in', $couponid)))->setDec('number');
        $result['result_code'] = $res;
        $result['msg'] = '发送成功';
    } else {
        $result['result_code'] = 0;
        $result['msg'] = '请联系管理员';
    }
    return $result;
}

/**
 * 获取轮播图
 * @param $map 条件
 */
function getCarousel($type = 'mall', $uid = 2)
{
    $map['type'] = $type;
    $data = db(tname::carousel)->where(['uid' => $uid])->where($map)->find();
    $carousel = serializeMysql($data['carousel'], 1);
    foreach ($carousel as $k => &$v) {
        if (!$v['imgpath']) {
            unset($carousel[$k]);
        } else {
            $v['imgpath'] = imgurlToAbsolute($v['imgpath']);
        }
    }
    return $carousel;
}


/**
 * php完美实现下载远程图片保存到本地   2017-10-15
 * @param: 文件url,保存文件目录,保存文件名称，使用的下载方式    当保存文件名称为空时则使用远程文件原来的名称
 * @date: 2017-05-13
 */
function getimage($url, $save_dir = '', $filename = '', $type = 0)
{
    if (trim($url) == '') {
        return array('file_name' => '', 'save_path' => '', 'error' => 1);
    }
    if (trim($save_dir) == '') {
        $save_dir = './';
    }
    if (trim($filename) == '') {//保存文件名
        $ext = strrchr($url, '.');
        if ($ext != '.gif' && $ext != '.jpg') {
            return array('file_name' => '', 'save_path' => '', 'error' => 3);
        }
        $filename = time() . $ext;
    }
    if (0 !== strrpos($save_dir, '/')) {
        $save_dir .= '/';
    }
    //创建保存目录
    if (!file_exists($save_dir) && !mkdir($save_dir, 0777, true)) {
        return array('file_name' => '', 'save_path' => '', 'error' => 5);
    }

    //获取远程文件所采用的方法
    if ($type) {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $img = curl_exec($ch);
        curl_close($ch);

    } else {
        ob_start();
        readfile($url);
        $img = ob_get_contents();
        ob_end_clean();
    }
    //$size=strlen($img);
    //文件大小

    $fp2 = @fopen($save_dir . $filename, 'a');
    fwrite($fp2, $img);
    fclose($fp2);
    unset($img, $url);
    return array('file_name' => $filename, 'save_path' => $save_dir . $filename, 'error' => 0);
}

/**
 * 图片路径相对转绝对
 * @param $map 条件
 */
function imgurlToAbsolute($img)
{
    return get_domain() . $img;
}

//******************************************非常规接口*******************************************
#TODO

/**
 * 更新数据库    2017-10-15
 * @param: $tablename 表名    $map 查询 $fieldname 字段 $afterchange 改后值
 */
function dataUpdate($tablename, $data)
{
    if (!isset($data['id']) || !$data['id']) {
        $data['create_time'] = time();
        $res = db($tablename)->insertGetId($data);
        if ($res !== false) {
            return $res;
        } else {
            return false;
        }
    } else {
        $res = db($tablename)->update($data);
        if ($res !== false) {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * 积分/余额变动    2018-02-03
 * @param $classify 模块、$type 变动类型、$where 条件、$money 变动数量、$infoid 关联信息ID、$change 1为加积分 2为减积分、$remark 预留字段
 */
function dataChangeLog($uid, $classify, $type, $main_id, $number = 0, $info_id, $remark)
{
    if ($number == 0) {
        return [0, '不能为0'];
    }

    $otherParam = DATA_CHANGE_PARAM[$classify];
    $where = [
        'uid' => $uid,
        $otherParam['operate_where'] => $main_id
    ];
    if ($number > 0) {
        $change = '+';
    } else {
        $change = '-';
        $number = -$number;
    }

    $data_before = db($otherParam['operate_table'])->where($where)->find();
    if ($change == '-' && $data_before[$otherParam['operate_field']] < $number) {
        return [0, '您现在只有' . $data_before[$otherParam['operate_field']]];
    }

    $changeData = [
        $otherParam['operate_field'] => array('exp', "`" . $otherParam['operate_field'] . "`" . $change . $number)
    ];
    $res = db($otherParam['operate_table'])->where($where)->update($changeData);
    if (!$res) {
        return [0, '操作失败'];
    }

    $data_cur = db($otherParam['operate_table'])->where($where)->find();
    $data = array(
        'uid' => $uid,
        'main_id' => $main_id,
        'info_id' => $info_id,
        'classify' => $classify,
        'type' => $type,
        'change' => $change,
        'before' => $data_before[$otherParam['operate_field']],
        'number' => $number,
        'after' => $data_cur[$otherParam['operate_field']],
        'remark' => $remark
    );
    $res1 = dataUpdate(\tname::data_changelog, $data);
    if ($res1) {
        return [1];
    } else {
        return [0, '操作失败'];
    }
}

/**
 * 批量订单操作记录
 * @param $order_id
 * @param $action_note 备注
 * @param $status_desc 状态描述
 * @param $action_user
 * @return mixed
 */
function orderActionLog($order_id, $action_note, $status_desc, $before_status, $action_user = 0)
{
    $order = db(\tname::mall_order)->where(['id' => $order_id])->find();
    $data = [
        'order_id' => $order_id,
        'action_user' => $action_user,
        'action_note' => $action_note,
        'order_status' => $order['status'],
        'pay_status' => $order['is_pay'],
        'before_status' => $before_status,
        'create_time' => time(),
        'status_desc' => $status_desc,
    ];
    return dataUpdate(\tname::mall_orderact, $data);//订单操作记录
}

/**
 * 交互记录    2017-10-15
 * @param $openid 浏览人 $classify模块    $type 类型  $infoid 关联ID $remark 备注
 */
function dataInteractive($uid, $openid, $classify, $type, $infoid, $remark = '')
{
    $data = array(
        'uid' => $uid,
        'openid' => $openid,
        'classify' => $classify,
        'type' => $type,
        'info_id' => $infoid,
        'create_time' => time(),
        'remark' => $remark
    );
    $res = db(\tname::data_interactive)->insert($data);
    if ($res) {
        return $res;
    } else {
        return 0;
    }
}

/**
 * 生成随机订单编号    2018-03-03
 * @param $length 字符串长度
 */
function createOrdernumber($tablename, $field = "order_number", $prefix = "", $length = 4)
{
    $number = date('Ymd');
    for ($i = 0; $i < $length; $i++) {
        $number .= rand(0, 9);
    }
    $number = $prefix . $number;
    $res = db($tablename)->where(array($field => $number))->count();   //保证生成的订单编号不会重复
    if ($res) {
        createOrdernumber($tablename, $length);
    } else {
        return $number;
    }
}


/**
 * 建立三级分销关系   2017-10-15
 * @param $myopenid 我的微信号 $lastvipid 我的上一级的会员ID
 */
function distributionBuildrelation($uid, $my_vip_id, $first_vip_id = 0)
{
    $mytrader = db(\tname::distribution_mytrader)->where(array('uid' => $uid, 'id' => $my_vip_id))->find();

    if (!empty($mytrader)) {    //如果已经建立过，不会重复建立
        return array(0, '已经建立过关系');
    }
    if (!$first_vip_id) {
        $first_vip_id = 0;
    }

    $data = array(
        'uid' => $uid,
        'vip_id' => $my_vip_id,
        'create_time' => time(),
    );
    if ($first_vip_id) {
        $first_vip = db(\tname::vip)->where(array('uid' => $uid, 'id' => $first_vip_id))->find();

        $second_vip = db(\tname::distribution_mytrader)->where(array('uid' => $uid, 'vip_id' => $first_vip['id']))->find();
        $data['first_vip_id'] = $first_vip_id;
        $data['second_vip_id'] = $second_vip['first_vip_id'];
        $data['third_vip_id'] = $second_vip['second_vip_id'];
        if ($second_vip['all_prev_id']) {
            $data['all_prev_id'] = $second_vip['all_prev_id'] . ',' . $first_vip_id;
        } else {
            $data['all_prev_id'] = $first_vip_id;
        }
    }
    $res = dataUpdate(\tname::distribution_mytrader, $data);
    return $res;
}

/**
 * 建立三级金钱关系   2017-10-15
 * @param $myopenid 我的微信号 $ordernumber 产生关系的订单号  $money 产生关系的金钱 $takemoney 给上三级的金钱数组
 */
function distributionBuildmoney($uid, $vip_id, $orderid, $order_number, $money, $takemoney = array())
{
    $mytrader = db(\tname::distribution_mytrader)->where(array('uid' => $uid, 'vip_id' => $vip_id))->find();
    $vip = db(\tname::vip)->where(array('id' => $vip_id))->find();

    if ($mytrader['first_vip_id'] != 0) {
        $data[] = array(
            'uid' => $uid,
            'distributor_vip_id' => $mytrader['first_vip_id'],
            'vip_id' => $vip_id,
            'nickname' => $vip['nickname'],
            'order_id' => $orderid,
            'order_number' => $order_number,
            'money' => $money,
            'take_money' => $takemoney[0],
            'rank' => 1,
            'create_time' => time(),
        );
    }
    if ($mytrader['second_vip_id'] != 0) {
        $data[] = array(
            'uid' => $uid,
            'distributor_vip_id' => $mytrader['second_vip_id'],
            'vip_id' => $vip_id,
            'nickname' => $vip['nickname'],
            'order_id' => $orderid,
            'order_number' => $order_number,
            'money' => $money,
            'take_money' => $takemoney[1],
            'rank' => 2,
            'create_time' => time(),
        );
    }
    if ($mytrader['third_vip_id'] != 0) {
        $data[] = array(
            'uid' => $uid,
            'distributor_vip_id' => $mytrader['third_vip_id'],
            'vip_id' => $vip_id,
            'nickname' => $vip['nickname'],
            'order_id' => $orderid,
            'order_number' => $order_number,
            'money' => $money,
            'take_money' => $takemoney[2],
            'rank' => 3,
            'create_time' => time(),
        );
    }
    if (!empty($data)) {
        $res = db(\tname::distribution_income)->insertAll($data);

        foreach ($data as $k => $v) {
            $distributor = db(tname::vip)->find($v['distributor_vip_id']);
//            dataChangeLog($uid, 'income', 'distribution', $distributor['id'], $v['take_money'], $orderid, '分销佣金');
        }
    }
    return true;
}

/**
 * 添加数据统计数据
 * @param $uid
 * @param $key
 * @param int $number
 * @return int|string|true
 */
function addStatistic($uid, $key, $source = null, $number = 1)
{
    $where = ['uid' => $uid, 'type' => $key, 'time' => strtotime(date('Y-m-d 00:00:00', time()))];
    $count = db(tname::data_count)->where($where)->count();
    if (!$count) { //为空添加数据
        $where['source'] = $source;
        $where['count'] = $number;
        $res = db(tname::data_count)->insertGetId($where);
    } else { //不为空修改数据
        $res = db(tname::data_count)->where($where)->setInc('count', $number);
    }
    return $res;
}

//******************************************项目接口*******************************************
#TODO

/**
 * 数组 转 对象
 *
 * @param array $arr 数组
 * @return object
 */
function array_to_object($arr)
{
    if (gettype($arr) != 'array') {
        return;
    }
    foreach ($arr as $k => $v) {
        if (gettype($v) == 'array' || getType($v) == 'object') {
            $arr[$k] = (object)array_to_object($v);
        }
    }

    return (object)$arr;
}

/**
 * 对象 转 数组
 *
 * @param object $obj 对象
 * @return array
 */
function object_to_array($obj)
{
    $obj = (array)$obj;
    foreach ($obj as $k => $v) {
        if (gettype($v) == 'resource') {
            return;
        }
        if (gettype($v) == 'object' || gettype($v) == 'array') {
            $obj[$k] = (array)object_to_array($v);
        }
    }

    return $obj;
}

function compare($x, $y)
{
    if ($x['price_per_day'] == $y['price_per_day']) {
        return 0;
    } elseif ($x['price_per_day'] < $y['price_per_day']) {
        return 1;
    } else {
        return -1;
    }
}

/**
 * 判断当前访问的用户是  PC端  还是 手机端  返回true 为手机端  false 为PC 端
 * @return boolean
 */
/**
 * 　　* 是否移动端访问访问
 * 　　*
 * 　　* @return bool
 * 　　*/
function isMobile()
{
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
        return true;

    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA'])) {
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile');
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
            return true;
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}


/**
 * 多个数组的笛卡尔积
 *
 * @param unknown_type $data
 */
function combineDika()
{
    $data = func_get_args();
    $data = current($data);
    $cnt = count($data);
    $result = array();
    $arr1 = array_shift($data);
    foreach ($arr1 as $key => $item) {
        $result[] = array($item);
    }

    foreach ($data as $key => $item) {
        $result = combineArray($result, $item);
    }
    return $result;
}

/**
 * 两个数组的笛卡尔积
 * @param unknown_type $arr1
 * @param unknown_type $arr2
 */
function combineArray($arr1, $arr2)
{
    $result = array();
    foreach ($arr1 as $item1) {
        foreach ($arr2 as $item2) {
            $temp = $item1;
            $temp[] = $item2;
            $result[] = $temp;
        }
    }
    return $result;
}


function create_goods_poster($goods_id, $vip_id, $source, $imgInfo, $textInfo, $font = './msyhbd.ttf')
{
    $date = 'uploads/goodsshare/' . date('Ymd') . time() . '/';
    $img = $date . md5(get_domain() . $source . $goods_id . $vip_id) . '.jpg';
    if (file_exists('./' . $img)) {
        return "/" . $img;
    }
    $img_type = strrchr($source, '.');
    switch ($img_type) {
        case ".jpg":
            $main = imagecreatefromjpeg($source);
            break;
        case ".png":
            $main = imagecreatefrompng($source);
            break;
        case ".gif":
            $main = imagecreatefromgif($source);
            break;
        case ".wbmp":
            $main = imagecreatefromwbmp($source);
            break;
    }

    $width = imagesx($main);
    $height = imagesy($main);

    $target = imagecreatetruecolor($width, $height);

    $white = imagecolorallocate($target, 255, 255, 255);
    imagefill($target, 0, 0, $white);
    imagecopyresampled($target, $main, 0, 0, 0, 0, $width, $height, $width, $height);

    $fontColor = imagecolorallocate($target, 255, 0, 0);//字的RGB颜色
//    $fontBox = imagettfbbox($fontSize, 0, $font, $text2);
//    imagettftext ( $target, $fontSize, 0, ceil(($width - $fontBox[2]) / 2), 370, $fontColor, $font, $text2 );

    foreach ($textInfo as $val) {
        draw_txt_to($target, $val);
    }

//    imageantialias($target, true);//抗锯齿，有些PHP版本有问题，谨慎使用

//    imageline($target, 100, 360, 200, 360, $fontColor);//画线
//    imagefilledpolygon($target, array(10 + 0, 0 + 142, 0, 12 + 142, 20 + 0, 12 + 142), 3, $fontColor);//画三角形
    $tanglefontColor = imagecolorallocate($target, 200, 200, 200);
    imagefilledrectangle($target, 12.5, 490, 362.5, 491, $tanglefontColor);//画矩形
//    imagefilledrectangle ( $target, 10, 420, 275, 450, $fontColor );//画矩形

    //bof of 合成图片

    foreach ($imgInfo as $val) {
        $img_type = strrchr($val['img'], '.');
        switch ($img_type) {
            case ".jpg":
                $child1 = imagecreatefromjpeg($val['img']);
                break;
            case ".png":
                $child1 = imagecreatefrompng($val['img']);
                break;
            case ".gif":
                $child1 = imagecreatefromgif($val['img']);
                break;
            case ".wbmp":
                $child1 = imagecreatefromwbmp($val['img']);
                break;
        }
        imagecopymerge($target, $child1, $val['left'], $val['top'], 0, 0, imagesx($child1), imagesy($child1), 100);
    }


    //eof of 合成图片
    @mkdir('./' . $date);
    imagejpeg($target, './' . $img, 95);
    imagedestroy($main);
    imagedestroy($target);
    imagedestroy($child1);
    return "/" . $img;
}

function create_user_poster($vip_id, $source, $imgInfo, $textInfo, $font = './msyhbd.ttf')
{
    $date = 'uploads/userposter/' . date('Ymd') . '/';
    $img = $date . md5(get_domain() . $vip_id) . '.jpg';
    if (file_exists('./' . $img)) {
        return "/" . $img;
    }
    $img_type = strrchr($source, '.');
    switch ($img_type) {
        case ".jpg":
            $main = imagecreatefromjpeg($source);
            break;
        case ".png":
            $main = imagecreatefrompng($source);
            break;
        case ".gif":
            $main = imagecreatefromgif($source);
            break;
        case ".wbmp":
            $main = imagecreatefromwbmp($source);
            break;
    }

    $width = imagesx($main);
    $height = imagesy($main);

    $target = imagecreatetruecolor($width, $height);

    $white = imagecolorallocate($target, 255, 255, 255);
    imagefill($target, 0, 0, $white);
    imagecopyresampled($target, $main, 0, 0, 0, 0, $width, $height, $width, $height);

    $fontColor = imagecolorallocate($target, 255, 0, 0);//字的RGB颜色
//    $fontBox = imagettfbbox($fontSize, 0, $font, $text2);
//    imagettftext ( $target, $fontSize, 0, ceil(($width - $fontBox[2]) / 2), 370, $fontColor, $font, $text2 );

    foreach ($textInfo as $val) {
        draw_txt_to($target, $val);
    }


//    imageantialias($target, true);//抗锯齿，有些PHP版本有问题，谨慎使用

//    imageline($target, 100, 360, 200, 360, $fontColor);//画线
//    imagefilledpolygon($target, array(10 + 0, 0 + 142, 0, 12 + 142, 20 + 0, 12 + 142), 3, $fontColor);//画三角形
    $tanglefontColor = imagecolorallocate($target, 200, 200, 200);
    imagefilledrectangle($target, 20, 365, 355, 366, $tanglefontColor);//画矩形
//    imagefilledrectangle ( $target, 10, 420, 275, 450, $fontColor );//画矩形

    //bof of 合成图片

    foreach ($imgInfo as $val) {
        $img_type = strrchr($val['img'], '.');
        switch ($img_type) {
            case ".jpg":
                $child1 = imagecreatefromjpeg($val['img']);
                break;
            case ".png":
                $child1 = imagecreatefrompng($val['img']);
                break;
            case ".gif":
                $child1 = imagecreatefromgif($val['img']);
                break;
            case ".wbmp":
                $child1 = imagecreatefromwbmp($val['img']);
                break;
        }
        imagecopymerge($target, $child1, $val['left'], $val['top'], 0, 0, imagesx($child1), imagesy($child1), 100);
    }


    //eof of 合成图片

    @mkdir('./' . $date);
    imagejpeg($target, './' . $img, 95);
    imagedestroy($main);
    imagedestroy($target);
    imagedestroy($child1);
    return "/" . $img;
}

function draw_txt_to($card, $textInfo)
{
    $font_color = imagecolorallocate($card, $textInfo['color'][0], $textInfo['color'][1], $textInfo['color'][2]);
    $font_file = './static/msyhbd.ttf';
    $_string = '';
    $__string = '';
    $string = $textInfo['text'];


    for ($i = 0; $i < mb_strlen($string); $i++) {
        $box = imagettfbbox($textInfo['fontsize'], 0, $font_file, $_string);
        $_string_length = $box[2] - $box[0];
        $box = imagettfbbox($textInfo['fontsize'], 0, $font_file, mb_substr($string, $i, 1));

        if ($_string_length + $box[2] - $box[0] < $textInfo['width']) {
            $_string .= mb_substr($string, $i, 1);
        } else {
            $__string .= $_string . "\n";
            $_string = mb_substr($string, $i, 1);
        }
    }


    $__string .= $_string;
    $box = imagettfbbox($textInfo['fontsize'], 0, $font_file, mb_substr($__string, 0, 1));


    imagettftext(
        $card,
        $textInfo['fontsize'],
        0,
        $textInfo['left'],
        $textInfo['top'] + ($box[3] - $box[7]),
        $font_color,
        $font_file,
        $__string);

}


/**
 * 图片压缩，比例不够部分留白
 * @param  [type]  $src          图片地址
 * @param  integer $width_value 压缩后的图片宽度
 * @param  integer $height_value 压缩后的图片高度
 * @return str 生成的图片地址
 */
function edit_img($src, $width_value = 375, $height_value = 667, $save_file = null)
{
    $temp = pathinfo($src);
    $filename = time() . '.jpg';
    if ($save_file) {
        $date = $save_file . date('Ymd') . '/';
    } else {
        $date = 'uploads/' . date('Ymd') . '/';
    }

    $savepath = $date . md5(get_domain() . $src . $width_value . $height_value) . '.jpg';
    if (file_exists('./' . $savepath)) {
        return "/" . $savepath;
    }

    //获取图片的基本信息
    $info = getimagesize($src);
    $width = $info[0];      //获取图片宽度
    $height = $info[1];     //获取图片高度
    if (($width / $height) >= ($width_value / $height_value)) { //宽度优先
        $w_mid = $width_value;                          //压缩后图片的宽度
        $h_mid = intval($width_value * $height / $width);//等比缩放图片高度
        $mid_x = 0;
        $mid_y = intval(($height_value - $h_mid) / 2);
    } else {                                            //高度优先
        $w_mid = intval($height_value * $width / $height);                            //压缩后图片的宽度
        $h_mid = $height_value;//等比缩放图片高度
        $mid_x = intval(($width_value - $w_mid) / 2);
        $mid_y = 0;
    }

    $temp_img = imagecreatetruecolor($width_value, $height_value);        //创建画布
    $white = imagecolorallocate($temp_img, 255, 255, 255);
    imagefill($temp_img, 0, 0, $white);
    $im = create($src);
    imagecopyresampled($temp_img, $im, $mid_x, $mid_y, 0, 0, $w_mid, $h_mid, $width, $height);
    @mkdir('./' . $date);
    imagejpeg($temp_img, $savepath, 100);
    imagedestroy($im);
    return "/" . $savepath;
}

/**
 * 创建图片，返回资源类型
 * @param  string $src 图片路径
 * @return resource $im 返回资源类型
 */
function create($src)
{
    $info = getimagesize($src);
    switch ($info[2]) {
        case 1:
            $im = imagecreatefromgif($src);
            break;
        case 2:
            $im = imagecreatefromjpeg($src);
            break;
        case 3:
            $im = imagecreatefrompng($src);
            break;
    }
    return $im;

}

/**
 * 获取缓存或者更新缓存
 * @param string $config_key 缓存文件名称
 * @param array $data 缓存数据  array('k1'=>'v1','k2'=>'v3')
 * @return array or string or bool
 */
function tpCache($config_key, $data = array())
{
    $param = explode('.', $config_key);
    if (empty($data)) {
        //如$config_key=shop_info则获取网站信息数组
        //如$config_key=shop_info.logo则获取网站logo字符串
        $config = F($param[0], '', TEMP_PATH);//直接获取缓存文件
        if (empty($config)) {
            //缓存文件不存在就读取数据库
            $res = db(\tname::config)->where("inc_type", $param[0])->select();
            if ($res) {
                foreach ($res as $k => $val) {
                    $config[$val['name']] = $val['value'];
                }
                F($param[0], $config, TEMP_PATH);
            }
        }
        if (count($param) > 1) {
            return $config[$param[1]];
        } else {
            return $config;
        }
    } else {
        //更新缓存
        $result = db(\tname::config)->where("inc_type", $param[0])->select();
        if ($result) {
            foreach ($result as $val) {
                $temp[$val['name']] = $val['value'];
            }
            foreach ($data as $k => $v) {
                $newArr = array('name' => $k, 'value' => trim($v), 'inc_type' => $param[0]);
                if (!isset($temp[$k])) {
                    db(\tname::config)->insert($newArr);//新key数据插入数据库
                } else {
                    if ($v != $temp[$k])
                        db(\tname::config)->where("name", $k)->update($newArr);//缓存key存在且值有变更新此项
                }
            }
            //更新后的数据库记录
            $newRes = db(\tname::config)->where("inc_type", $param[0])->select();
            foreach ($newRes as $rs) {
                $newData[$rs['name']] = $rs['value'];
            }
        } else {
            foreach ($data as $k => $v) {
                $newArr[] = array('name' => $k, 'value' => trim($v), 'inc_type' => $param[0]);
            }
            db(\tname::config)->insertAll($newArr);
            $newData = $data;
        }
        return F($param[0], $newData, TEMP_PATH);
    }
}

/**
 * 查询快递
 * @param $postcom  快递公司编码
 * @param $getNu  快递单号
 * @return array  物流跟踪信息数组
 */
function queryExpress($postcom, $getNu)
{
    $config =tpCache('express');
    $key = $config['kd100_key'];//客户授权key
    $customer =$config['kd100_customer'];//查询公司编号
    $param = array (
        'com' => $postcom,			//快递公司编码
        'num' => $getNu,	//快递单号
        'phone' => '',				//手机号
        'from' => '',				//出发地城市
        'to' => '',					//目的地城市
        'resultv2' => '1'			//开启行政区域解析
    );
    //请求参数
    $post_data = array();
    $post_data["customer"] = $customer;
    $post_data["param"] = json_encode($param);
    $sign = md5($post_data["param"].$key.$post_data["customer"]);
    $post_data["sign"] = strtoupper($sign);
    $url = 'http://poll.kuaidi100.com/poll/query.do';	//实时查询请求地址
    $resp = httpRequest($url, "POST",$post_data);
//    dump($resp);
    return json_decode($resp, true);
}

/**
 * CURL请求
 * @param $url string 请求url地址
 * @param $method string 请求方法 get post
 * @param mixed $postfields post数据数组
 * @param array $headers 请求header信息
 * @param bool|false $debug 调试开启 默认false
 * @return mixed
 */
function httpRequest($url, $method = "GET", $postfields = null, $headers = array(), $debug = false)
{
    $method = strtoupper($method);
    $ci = curl_init();
    /* Curl settings */
    curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60); /* 在发起连接前等待的时间，如果设置为0，则无限等待 */
    curl_setopt($ci, CURLOPT_TIMEOUT, 7); /* 设置cURL允许执行的最长秒数 */
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    switch ($method) {
        case "POST":
            curl_setopt($ci, CURLOPT_POST, true);
            if (!empty($postfields)) {
                $tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
                curl_setopt($ci, CURLOPT_POSTFIELDS, $tmpdatastr);
            }
            break;
        default:
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method); /* //设置请求方式 */
            break;
    }
    $ssl = preg_match('/^https:\/\//i', $url) ? TRUE : FALSE;
    curl_setopt($ci, CURLOPT_URL, $url);
    if ($ssl) {
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE); // 不从证书中检查SSL加密算法是否存在
    }
    //curl_setopt($ci, CURLOPT_HEADER, true); /*启用时会将头文件的信息作为数据流输出*/
    if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {
        curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
    }
    curl_setopt($ci, CURLOPT_MAXREDIRS, 2);/*指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的*/
    curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ci, CURLINFO_HEADER_OUT, true);
    /*curl_setopt($ci, CURLOPT_COOKIE, $Cookiestr); * *COOKIE带过去** */
    $response = curl_exec($ci);
    $requestinfo = curl_getinfo($ci);
    $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    if ($debug) {
        echo "=====post data======\r\n";
        var_dump($postfields);
        echo "=====info===== \r\n";
        print_r($requestinfo);
        echo "=====response=====\r\n";
        print_r($response);
    }
    curl_close($ci);
    return $response;
    //return array($http_code, $response,$requestinfo);
}

/**
 *  post提交数据
 * @param  string $url 请求Url
 * @param  array $datas 提交的数据
 * @return url响应返回的html
 */
function sendPost($url, $datas)
{
    $temps = array();
    foreach ($datas as $key => $value) {
        $temps[] = sprintf('%s=%s', $key, $value);
    }
    $post_data = implode('&', $temps);
    $url_info = parse_url($url);
    if (empty($url_info['port'])) {
        $url_info['port'] = 80;
    }
    $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
    $httpheader .= "Host:" . $url_info['host'] . "\r\n";
    $httpheader .= "Content-Type:application/x-www-form-urlencoded\r\n";
    $httpheader .= "Content-Length:" . strlen($post_data) . "\r\n";
    $httpheader .= "Connection:close\r\n\r\n";
    $httpheader .= $post_data;
    $fd = fsockopen($url_info['host'], $url_info['port']);
    fwrite($fd, $httpheader);
    $gets = "";
    $headerFlag = true;
    while (!feof($fd)) {
        if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
            break;
        }
    }
    while (!feof($fd)) {
        $gets .= fread($fd, 128);
    }
    fclose($fd);

    return $gets;
}


/**
 * [将Base64图片转换为本地图片并保存]
 * @E-mial wuliqiang_aa@163.com
 * @TIME   2017-04-07
 * @WEB    http://blog.iinu.com.cn
 * @param  [Base64] $base64_image_content [要保存的Base64]
 * @param  [目录] $path [要保存的路径]
 */
function base64_image_content($base64_image_content, $path)
{
    //匹配出图片的格式
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
        $type = $result[2];
        $new_file = $path . "/" . date('Ymd', time()) . "/";
        if (!file_exists($new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0700);
        }
        $new_file = $new_file . time() . rand(0, 9) . rand(100, 10000) . ".png";
        if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))) {
            return '/' . $new_file;
        } else {
            return false;
        }
    } else {
        return false;
    }
}


function comment_num_show($num)
{
    if ($num >= 10000) {
        $text = ((int)($num / 10000) * 10000) . "+";
    } elseif ($num >= 1000) {
        $text = ((int)($num / 1000) * 1000) . "+";
    } elseif ($num >= 100) {
        $text = ((int)($num / 100) * 100) . "+";
    } else {
        $text = $num;
    }
    return $text;
}

/**
 * @param $phone
 * @return null|string|string[]屏蔽电话号码中间四位：
 */
function hidtel($phone)
{
    $IsWhat = preg_match('/(0[0-9]{2,3}[\-]?[2-9][0-9]{6,7}[\-]?[0-9]?)/i', $phone); //固定电话
    if ($IsWhat == 1) {
        return preg_replace('/(0[0-9]{2,3}[\-]?[2-9])[0-9]{3,4}([0-9]{3}[\-]?[0-9]?)/i', '$1****$2', $phone);
    } else {
        return preg_replace('/(1[358]{1}[0-9])[0-9]{4}([0-9]{4})/i', '$1****$2', $phone);
    }
}

/**
 *   实现中文字串截取无乱码的方法
 */
function getSubstr($string, $start, $length)
{
    if (mb_strlen($string, 'utf-8') > $length) {
        $str = mb_substr($string, $start, $length, 'utf-8');
        return $str . '...';
    } else {
        return $string;
    }
}

/**
 * 收集form_id
 */
function getFromId($vip_id,$form_id=null){
    if(empty($form_id)){
        $form_id = input('formId',null);
        if(empty($form_id)){
            return false;
        }
    }
    if($form_id=="the formId is a mock one"){
        return false;
    }
    $data = array(
        'vip_id'=>$vip_id,
        'form_id'=>$form_id,
        'create_time'=>time(),
    );
    $res = dataUpdate(\tname::weixin_formid,$data);
}

/**
 * 获取绑定手机号的所有ID
 */
 function getVipIds($vip_id,$vip=[]){
     if(empty($vip)){
         $vip = db(\tname::vip)->where(array('id'=>$vip_id))->find();
     }
     if ($vip['mobile']) {
         $ids = db(\tname::vip)->where(array('mobile' => $vip['mobile']))->column('id');
     } else {
         $ids = [$vip['id']];
     }
     return $ids;
 }

