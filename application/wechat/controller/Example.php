<?php
namespace app\wechat\controller;

class Example extends Base
{
    public function index()
    {
    }

    public function sendmail()
    {
        $tomail = '1798889348@qq.com';
        $subject = 'zhengshi';
        $body='body';

        $result = sendMail(WID,$tomail,$subject,$body);
    }

    //企业付款demo
    public function enterprisePayment()
    {
        $openid = 'okuRis8VoZgHXUfIFQKKWhEQmn4I';
        $ordernumber = createOrdernumber("MallOrder");

        wxEnterprisePayment(WID, $openid, 1, $ordernumber, '111');
    }

    //现金红包demo
    public function redbagpay()
    {
        $openid = '';
        $ordernumber = createOrdernumber("MallOrder");

        wxCashRedPacket(WID, $openid, 1, $ordernumber, '111');
    }

    //退款原路返回demo
    public function refund()
    {
        $openid = 'okuRis8VoZgHXUfIFQKKWhEQmn4I';
        $ordernumber = '201802060324';
        $total_fee = 0.01;
        $refund_fee = 0.01;
        wxRefund(2, $openid, $ordernumber, $total_fee, $refund_fee);
    }


    //七牛转码amr 转 mp3
    /* public function upchange($dir,$filename)
    {

        $filePath="$dir";

        $mediaid=$filename;
        $accessKey = trim('pQAu2-68H2LopLiTAMGFI7jyzMCquATRSPBYT2YC');      //七牛公钥
        $secretKey = trim('BIGVo3J7Ypj_8aKnF3jGPeQgR1iRFfN2_co9ZtnB');      //七牛私钥
        $auth = new Auth($accessKey, $secretKey);

        $bucket = trim('yuyinzhufu');
        //数据处理队列名称,不设置代表不使用私有队列，使用公有队列。

        //        $pipeline = trim('your_pipeline');

        //通过添加'|saveas'参数，指定处理后的文件保存的bucket和key
        //不指定默认保存在当前空间，bucket为目标空间，后一个参数为转码之后文件名
        $savekey =  \Qiniu\base64_urlSafeEncode($bucket . ':' . $mediaid . '.mp3');
        //设置转码参数
        $fops = "avthumb/mp3/ab/320k/ar/44100/acodec/libmp3lame";
        $fops = $fops . '|saveas/' . $savekey;
        if (!empty($pipeline)) {  //使用私有队列
            $policy = array(
                    'persistentOps' => $fops,
                    'persistentPipeline' => $pipeline
            );
        } else {                  //使用公有队列
            $policy = array(
                    'persistentOps' => $fops
            );
        }

        //指定上传转码命令
        $uptoken = $auth->uploadToken($bucket, null, 3600, $policy);


        $key = $mediaid . '.amr'; //七牛云中保存的amr文件名

        $uploadMgr = new \Qiniu\Storage\UploadManager();


        //上传文件并转码$filePath为本地文件路径
        //
        list($ret, $err)= $uploadMgr->putFile($uptoken, $key, $filePath);
        // 		        dump($ret);

        if ($err !== null) {

            return false;
        } else {

            //此时七牛云中同一段音频文件有amr和MP3两个格式的两个文件同时存在
            $bucketMgr = new \Qiniu\Storage\BucketManager($auth);
            //为节省空间,删除amr格式文件
            $bucketMgr->delete($bucket, $key);
            return $ret['key'];
        }
    }
     */


    public function index2()
    {
        $signPackage = $this->getsignpackage();
        $this->assign('signpackage', $signPackage);
        $this->display();
    }

    public function index1()
    {
        $mediaid = input('post.serverId');
        $access_token = getaccesstoken(WID);
        $path = './Uploads/Video/uid' . WID . '/wechat/' . date('Ymd') . '/';
        if (!file_exists($path)) {
            $savepath = '/Uploads/Video/uid' . WID . '/wechat/' . date('Ymd') . '/';
            mkdir(ROOT_PATH . $savepath, 0777, true);
        }
        $code = createverifycode();
        $filename = time() . $code;
        $dir = './Uploads/Video/uid' . WID . '/wechat/' . date("Ymd") . '/' . $filename . '.amr';
        $Material = controller('purewechat/Material');
        $img = $Material->getmedia($mediaid, $access_token);
        if (!empty($img)) {
            @file_put_contents($dir, $img);
        }
        return $dir;
    }


    //分享到朋友圈，朋友，腾讯微博，qq，qq空间
    public function share()
    {
        //控制器
        $signPackage = $this->getsignpackage();
        $this->assign('signpackage', $signPackage);
        $share = array(
            'title' => '我的二维码',
            'desc' => '加入我们，一起享受阅读的乐趣',
            'imgurl' => '',
            'link' => get_domain() . '/wechat.php?s=Vip/index',
        );
        $this->assign('share', $share);

        //页面
        /* <include file="Public/jssdk" />


        <script type="text/javascript">
        var share = {
            title:'{$share.title}',
            desc:'{$share.desc}',
            imgurl:'{$share.imgurl}',
            link:'{$share.link}'
        }
        $(document).ready(share_obj.share_init);
        </script> */

    }

    //模板消息发送
    public function template()
    {
        $data = array(
            'openid' => 'okuRis8VoZgHXUfIFQKKWhEQmn4I',
            'tid' => 'F-5qG-88OV0Yq-z7FxpjqOhm6z8BcjV-760QRCPiPFI',
            'url' => get_domain() . '/wechat.php?s=Vip/index',
            'content' => array(
                'first' => '您的订单已取消',
                'keyword1' => '付款失败',
                'keyword2' => '下午四点',
                'remark' => '点击会员中心，可查询会员卡剩余时间。'
            )
        );
        $result = sendtemplate(WID, $data);
    }

    //Qiniu
    public function qiniu()
    {
        //amrtomp3
        $dir = "./Public/Common/video/14997429461600.amr";
        $filename = '14997429461600';

        $Qiniu = controller('purewechat/Qiniu');
        $Qiniu->amrtomp3($dir, $filename);
    }

}