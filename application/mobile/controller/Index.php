<?php
namespace app\wechat\controller;

class Index extends Base
{
    /**
     * 微信接口对接    2017-10-15
     */
    public function index()
    {
        $wxconfig = db(\tname::weixin_config)->where('uid', WID)->find();
        $Config = new \wechat\Config($wxconfig['token'], array('uid' => WID));
        $data = $Config->request(array('uid' => WID));

        $openid = $data['FromUserName'];
        session('openid', $openid);
        list($content, $type) = $this->reply($data);
        $Config->response($content, $type);
    }

    /**
     * 消息回复 2017-10-15
     */
    protected function reply($data)
    {
        if ($data['MsgType'] == 'event') {    //事件推送
            if ($data['Event'] == 'subscribe') {    //关注事件
                $reply = $this->replysubscribe($data['EventKey']);
            } else if ($data['Event'] == 'unsubscribe') {    //取消关注事件
                $reply = $this->replyunsubscribe();
            } else if ($data['Event'] == 'CLICK') {    //点击自定义菜单
                $reply = $this->replyclick($data['EventKey']);
            } else if ($data['Event'] == 'SCAN') {    //扫描二维码 用户已关注
                $reply = $this->replyscan($data['EventKey']);
            } else if ($data['Event'] == 'LOCATION') {    //上报地理位置事件
                $reply = $this->replyeventlocation($data['Latitude'], $data['Longitude']);
            } else if ($data['Event'] == 'VIEW') {    //点击菜单跳转链接时的事件推送
                $reply = $this->replyview($data['EventKey']);
            } else {
                //自定义菜单还有6个其他类型事件，不一一写出
                $reply = array("未知消息类型", 'text');
            }
        } else if ($data['MsgType'] == 'text') {    //接受的到的是文本消息
            $reply = $this->replykeywords($data['Content']);
        } else if ($data['MsgType'] == 'location') {    //接受的到的是地理位置消息
            $reply = $this->replylocation($data['Location_X'], $data['Location_Y']);
        } else if ($data['MsgType'] == 'image') {    //接受的到的是图片消息
            $reply = $this->replyimage();
        } else if ($data['MsgType'] == 'voice') {    //接受的到的是语音消息
            $reply = $this->replyvoice();
        } else if ($data['MsgType'] == 'video') {    //接受的到的是视频消息
            $reply = $this->replyvideo();
        } else if ($data['MsgType'] == 'shortvideo') {    //接受的到的是小视频消息
            $reply = $this->replyshortvideo();
        } else if ($data['MsgType'] == 'link') {    //接受的到的是链接消息
            $reply = $this->replylink();
        } else {
            $reply = array("未知消息类型", 'text');
        }

        //如果还是空的
        if (empty($reply)) {
            $where_event = array(
                'uid' => WID,
                'event' => subscribe
            );
            $event = db(\tname::weixin_event)->where($where_event)->field('id,arbitrarily')->find();

            if (!empty($event) && $event['arbitrarily'] == 1) {
                if ($event['msgtype'] == 'text') {
                    $reply = array($event['text'], 'text');
                } else if ($event['msgtype'] == 'news') {
                    $news = $this->news($event['msgid']);
                    $reply = array($news, 'news');
                }
            }
        }
        return $reply;
    }

    protected function replyclick($key)
    {

        $keywords = db(\tname::weixin_keywords)->find($key);

        if ($keywords['type'] == 'text') {
            $data = array($keywords['content'], 'text');
            return $data;
        } else if ($keywords['type'] == 'image') {
            $result = $this->image($keywords['content']);
            $data = array($result['media_id'], 'image');
        } else if ($keywords['type'] == 'news') {
            $news = $this->news($keywords['content']);
            $data = array($news, 'news');
        }

        return $data;
    }

    /**
     * 回复关键字    2017-10-15
     */
    protected function replykeywords($text)
    {
        $keyword = db(\tname::weixin_keywords)->where('uid', WID)->where('is_hidden',0)
            ->where('("' . $text . '" REGEXP name and matchingtype = 1) or ( name = "' . $text . '" and matchingtype =0) ')
            ->order('matchingtype')
            ->find();
        if ($keyword['matchingtype'] != 1) {
            if ($keyword['name'] != $text) {
                $keyword = array();
            }
        }
        if (!empty($keyword)) {
            if ($keyword['type'] == 'text') {
                $data = array($keyword['content'], 'text');
                return $data;
            } else if ($keyword['type'] == 'image') {
                $image = $this->image($keyword['content']);
                $data = array($image['media_id'], 'image');
            } else if ($keyword['type'] == 'news') {
                $news = $this->news($keyword['content']);
                $data = array($news, 'news');
            }
            return $data;
        } else {
            $data = array();
            $data[] = '';
            $data[] = 'transfer_customer_service';
            return $data;
        }
    }

    /**
     * 回复关注事件    2017-10-15
     */
    protected function replysubscribe($eventkey)
    {
        empty($eventkey) && $eventkey = '';
        $key = explode('_', $eventkey);
        $openid = session('openid');

        //获取用户信息
        $access_token = getaccesstoken(WID);
        $User = new \wechat\User();
        $vipData = $User->getbyopenid($openid, $access_token, array('uid' => WID));
        $vipData['uid'] = WID;
        $vipData['source'] = 1;
        $vipData['create_time'] = time();
        $vip = db(\tname::vip)->where('openid', $openid)->find();

        if (!empty($vip)) {
            db(\tname::vip)->where('openid', $openid)->update($vipData);
        } else {
            db(\tname::vip)->insert($vipData);
        }

        //建立三级分销关系
        if (isset($key[1]) && $key[1]) {
            $first_vip = db(\tname::vip)->where(array('sceneid' => $key[1]))->find();
        }else{
            $first_vip['id'] = 0;
        }

        distributionBuildrelation(WID, $openid, $first_vip['id']);

        //首次关注送积分
        /*$log = db(\tname::integral_log) -> where(array('openid'=>$openid,'type'=>'firstsubscribe')) -> find();
        if(empty($log)){
            $config = db(\tname::integral_config) -> where('uid',WID) -> find();
            dataChangeLog(WID, 'integral', 'firstsubscribe', $openid, $config['firstsubscribe'], 0, '首次关注送积分');
        }*/

        $where_event = array(
            'uid' => WID,
            'event' => 'subscribe'
        );
        $event = db(\tname::weixin_event)->where($where_event)->find();
        $keyword = db(\tname::weixin_keywords)->find($event['keywords_id']);
        if ($keyword['type'] == 'text') {
            $data = array($keyword['content'], 'text');
            return $data;
        } else if ($keyword['type'] == 'news') {
            $news = $this->news($keyword['content']);
            $data = array($news, 'news');
        } else if ($keyword['type'] == 'image') {
            $image = $this->image($keyword['content']);
            $data = array($image['media_id'], 'image');
        }
        return $data;
    }

    /**
     * 取消关注事件    2017-10-15
     */
    protected function replyunsubscribe()
    {
        $openid = session('openid');
        $vip = db(\tname::vip)->where('openid', $openid)->find();
        if (!empty($vip)) {
            $data = array(
                'subscribe' => 0,
                'subscribe_time' => 0,
            );
            db(\tname::vip)->where('openid', $openid)->update($data);
        }
    }

    protected function replyscan($eventkey)
    {
        return ' ';
    }

    protected function replyeventlocation($lat, $lng)
    {
        return ' ';
    }

    protected function replyview($eventkey)
    {
        return ' ';
    }

    protected function replylocation($lat, $lng)
    {
        return ' ';
    }

    public function replyimage()
    {

        //return array('mediaid','image');	//被动回复图片消息
        //return array('mediaid','voice');	//被动回复语音消息
        //return array(array('mediaid'=>$mediaid,'title'=>$title,'description'=>$description),'video');	//被动回复视频消息
        //return array(array('title','description','musicurl','hqmusicurl'),'music');	//被动回复音乐消息
        return ' ';
    }

    public function replyvoice()
    {
        return ' ';
    }

    public function replyvideo()
    {
        return ' ';
    }

    public function replyshortvideo()
    {
        return ' ';
    }

    public function replylink()
    {
        return ' ';
    }

    /**
     * 转换图文消息    2017-10-15
     */
    public function news($id)
    {
        $news = db(\tname::weixin_news)->find($id);
        $news = serializeMysql($news['content'], 1);

        $config = db(\tname::weixin_config)->where('uid', WID)->find();
        $openid = session('openid');
        foreach ($news as $key => &$value) {
            $value['picurl'] = get_domain() . $value['picurl'];
            if ($config['certified'] == 2 && strpos($value['url'], '/openid/')) {
                $value['url'] = $value['url'] . $openid;
            }
        }
        return $news;
    }

    /**
     * 转换图片消息    2017-10-15
     */
    public function image($image)
    {
        $Material = new \wechat\Material();
        $access_token = getaccesstoken(WID);
        $image = '.' . $image;
        $result = $Material->upload($image, $access_token, 'image', array('uid' => WID));

        return $result;
    }
}
