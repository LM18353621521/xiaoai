<?php
/**
 * Created by PhpStorm.
 * User: Lu
 * Date: 2018/7/2
 * Time: 22:41
 */
namespace app\mobile\controller;

class Test extends Base
{
    public function auto_order(){
        $BasicLogic = new \app\common\logic\BasicLogic();
//        $BasicLogic->cancel_notPayOrder();
        $BasicLogic->auto_confirm_order();

    }
    public function index(){
        echo get_domain().url('Special/indepNotify',array('uid'=>WID));
        return $this->fetch();
    }
    public function alipay(){
        $apipay = new \alipay\Wappay();
        $apipay = new \alipay\Pagepay();

        $params=array(
            'out_trade_no'=>201811261112,
            'subject'=>'晓爱科技',
            'total_amount'=>0.01,
            'return_url'=>get_domain().url('order/myorder'),
            'notify_url'=>get_domain().url('Special/indepNotify',array('uid'=>WID)),
        );
        $apipay::pay($params);
    }

    public function ali_refund(){
        $apipay = new \alipay\Refund();

        $params=array(
            'trade_no'=>"2018112622001439021009039100",
            'out_trade_no'=>201811261112,
            'refund_amount'=>0.01,
        );
        $apipay::exec($params);
    }


    public function jhSmsApi(){
        $mobile ="17695514618";
        $content="";
//        $code=createverifycode(6);
        $res=sendSmsjh($mobile,$content,$code);
        $res='{"reason":"操作成功","result":{"sid":"f9ba00e3fe78461ca41402313d60d09a","fee":1,"count":1},"error_code":0}';
        $res = json_decode($res,true);

        dump($res);

        echo $code = $res['error_code'];
    }


    public function ereg_email()
    {
        $eregstr ='/[a-zA-Z0-9_.]+@[a-zA-Z0-9]+\.[a-zA-Z0-9.]+$/';
        $emial="1064413915@qq.com";
        if(preg_match($eregstr,$emial,$a)){
            dump($a);
        }else{
            echo "This is not en emial";
        }
    }
    public function ereg_phone()
    {
        $eregstr ='/1[3|5|7|8|9]+[0-9]{9}/';
        $phone="18369651365";
        if(preg_match($eregstr,$phone,$a)){
            dump($a);
        }else{
            echo "This is not en phone number";
        }
    }
    public function  ereg_replace_url(){
        $url="这是百度网址：http://www.baidu.com 和 http://www.taobao.com";
        $eregstr="/(http:\/\/)([a-zA-Z0-9.\/-_]+)/";
        $newstr = "<a href=\"\\0\">\\0</a>";
        $result = preg_replace($eregstr,$newstr,$url);
        echo $result;
        echo "<br>";
        $newstr = "<a href=\"\\0\">\\2</a>";
        $result = preg_replace($eregstr,$newstr,$url);
        echo $result;
    }
    public function ereg_strtok(){
        $str = "Hello world, I am coming";
        $newstr = strtok($str," ");
        while($newstr !== false){
            echo $newstr."<br>";
            $newstr = strtok(" ");
        }
    }
    //多维数组排序
    public function sort_array(){
        $roomtypes = array(
            array(
                "type"=>"单床房",
                "info"=>"单人单间",
                "price_per_day"=>"200",
            ),
            array(
                "type"=>"标准间",
                "info"=>"两床标间",
                "price_per_day"=>"150",
            ),
            array(
                "type"=>"三床房",
                "info"=>"三张床",
                "price_per_day"=>"100",
            ),
            array(
                 "type"=>"海景房",
                 "info"=>"看大海",
                 "price_per_day"=>"300",
             )
        );

        usort($roomtypes,'compare');

        dump($roomtypes);



    }
    public function compare($x,$y){
        if($x['price_per_day']==$y['price_per_day']){
            return 0;
        }elseif($x['price_per_day'<$y['price_per_day']]){
            return -1;
        }else{
            return 1;
        }
    }




}