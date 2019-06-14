<?php
namespace app\admin\controller;

class Wechat extends Member
{

    //微信授权配置 2017-10-15
    public function auth()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;

            $res = dataUpdate(\tname::weixin_config, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $data = db(\tname::weixin_config)->where(['uid' => UID, 'type' => 'wechat'])->find();
            $url = get_domain() . '/wechat.php/Index/index/u/' . UID;

            $this->assign('data', $data);
            $this->assign('url', $url);
            return $this->fetch();
        }
    }

    //微信授权配置删除 2017-10-15
    public function authdel()
    {
        $data = [
            'name' => '',
            'weixinid' => '',
            'appid' => '',
            'appsecret' => '',
            'certified' => '',
            'token' => '',
            'access_token' => '',
            'access_time' => 0,
            'create_time' => 0
        ];
        $res = db(\tname::weixin_config)->where(['uid' => UID, 'type' => 'wechat'])->update($data);
        if ($res) {
            return array('ret' => 1, 'msg' => '解绑成功！');
        } else {
            return array('ret' => 0, 'msg' => '解绑失败！');
        }
    }

    //微信支付管理    2017-10-15
    public function pay()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;
            $wxpay = [
                'mchid' => $data['mchid'],
                'partnerkey' => $data['partnerkey']
            ];
            $data['wxpay'] = serializeMysql($wxpay);

            $res = dataUpdate(\tname::weixin_pay, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $data = db(\tname::weixin_pay)->where(['uid' => UID, 'type' => 'wechat'])->find();
            if (!empty($data)) {
                $data['wxpay'] = serializeMysql($data['wxpay'], 1);
            }


            $this->assign('data', $data);
            return $this->fetch();
        }
    }


    //小程序授权配置 2017-10-15
    public function appletauth()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;

            $res = dataUpdate(\tname::weixin_config, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $data = db(\tname::weixin_config)->where(['uid' => UID, 'type' => 'applet'])->find();
            $url = get_domain();

            $this->assign('data', $data);
            $this->assign('url', $url);
            return $this->fetch();
        }
    }

    //小程序授权配置删除 2017-10-15
    public function appletauthdel()
    {
        $data = [
            'name' => '',
            'weixinid' => '',
            'appid' => '',
            'appsecret' => '',
            'certified' => '',
            'token' => '',
            'access_token' => '',
            'access_time' => 0,
            'create_time' => 0
        ];
        $res = db(\tname::weixin_config)->where(['uid' => UID, 'type' => 'applet'])->update($data);
        if ($res) {
            return array('ret' => 1, 'msg' => '解绑成功！');
        } else {
            return array('ret' => 0, 'msg' => '解绑失败！');
        }
    }

    //小程序支付管理    2017-10-15
    public function appletpay()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;
            $wxpay = [
                'mchid' => $data['mchid'],
                'partnerkey' => $data['partnerkey']
            ];
            $data['wxpay'] = serializeMysql($wxpay);

            $res = dataUpdate(\tname::weixin_pay, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $data = db(\tname::weixin_pay)->where(['uid' => UID, 'type' => 'applet'])->find();
            if (!empty($data)) {
                $data['wxpay'] = serializeMysql($data['wxpay'], 1);
            }

            $this->assign('data', $data);
            return $this->fetch();
        }
    }


    //用户其他配置
    public function config()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;

            $res = dataUpdate(\tname::other_config, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $data = db(\tname::other_config)->where(['uid' => UID])->find();

            $this->assign('data', $data);
            return $this->fetch();
        }
    }


    /**
     * 文件上传 2017-10-15
     */
    public function fileupload()
    {
        if (request()->isPost()) {
            $files = request()->file('');

            foreach ($files as $file) {
                //上传到二级域名
                $info = $file->rule('uniqid')->move(ROOT_PATH . 'public', '');
                $filename = $info->getSaveName();

                //上传到一级域名 jucheng01.net
                $ftp = new \ftp\Ftp();
                $data['server'] = 'www.jucheng01.net';//服务器地址(IP or domain)
                $data['username'] = 'jucheng';//ftp帐户
                $data['password'] = 'jucheng0101';//ftp密码
                $data['port'] = 21;//ftp端口,默认为21
                $data['pasv'] = false;//是否开启被动模式,true开启,默认不开启
                $data['ssl'] = false;//ssl连接,默认不开启
                $data['timeout'] = 60;//超时时间,默认60,单位 s
                if ($ftp->start($data)) {// 远程连接成功;
                    $url = '../public/' . $filename;//保存文件路径
                    $filename = '/public_html/' . $filename;
                    if ($ftp->put($filename, $url)) {
                        //上传文件成功!
                    }
                } else {
                    $ftp->close(); //关闭ftp资源
                    return false;
                }
                $ftp->close(); //关闭ftp资源


                if ($info) {
                    $arr = array(
                        'imgpath' => '/' . $info->getSaveName(),
                        'status' => 1,
                    );

                    return json($arr);
                } else {
                    // 上传失败获取错误信息
                    return $file->getError();
                }
            }
        } else {
            return $this->fetch();
        }
    }

    /**
     * 系统链接 2017-10-25
     */
    public function url()
    {

        if (request()->isPost()) {
            $where = array(
                'uid' => UID,
                'is_hidden' => 0
            );
            $dataList = db(\tname::weixin_url)->where($where)->paginate(500);

            $this->assign('dataList', $dataList);
            $html = $this->fetch('wechat/form');

            return ajaxSuccess($html);
        }
        return $this->fetch();
    }

    /**
     * 系统链接添加 2017-10-15
     */
    public function urladd()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;

            $res = dataUpdate(\tname::weixin_url, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::weixin_url)->find($id);

            $this->assign('data', $data);
            return $this->fetch();
        }
    }

    /**
     * 图文素材列表 2017-10-15
     */
    public function news()
    {
        if (request()->isPost()) {
            $where = array(
                'uid' => UID,
                'is_hidden' => 0
            );
            $dataList = db(\tname::weixin_news)->where($where)->paginate(500);
            foreach ($dataList as $key => &$value) {
                $value['content'] = serializeMysql($value['content'], 1);
            }
            $this->assign('dataList', $dataList);
            $html = $this->fetch('wechat/form');

            return ajaxSuccess($html);
        }
        return $this->fetch();
    }

    /**
     * 图文素材列表添加 2017-10-15
     */
    public function newsadd()
    {
        if (request()->isPost()) {
            $data = input('post.');
            if (empty($data['title'])) {
                return array('ret' => 0, 'msg' => '请至少添加一条完整图文消息');
            }
            //数据信息
            foreach ($data['title'] as $k => $v) {
                if (!$v || !$data['picurl'][$k]) {
                    continue;
                }

                $data['content'][] = array(
                    'title' => $v,
                    'description' => $data['description'][$k],
                    'picurl' => $data['picurl'][$k],
                    'url' => $data['url'][$k],
                );
            }

            if (empty($data['content'])) {
                return array('ret' => 0, 'msg' => '请至少添加一条完整图文消息');
            }
            $data['uid'] = UID;
            $data['content'] = serializeMysql($data['content']);
            $res = dataUpdate(\tname::weixin_news, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::weixin_news)->find($id);
            if ($id) {
                $data['content'] = serializeMysql($data['content'], 1);
            }

            $urlList = db(\tname::weixin_url)->where(array('uid' => UID, 'is_hidden' => 0))->select();
            $this->assign('data', $data);
            $this->assign('urlList', $urlList);
            return $this->fetch();
        }
    }


    /**
     * 关键词回复设置 2017-10-25
     */
    public function keywords()
    {
        if (request()->isPost()) {
            $news = db(\tname::weixin_news)->column('id,name');
            $where = array(
                'uid' => UID,
                'is_hidden' => 0
            );
            $dataList = db(\tname::weixin_keywords)->where($where)->paginate(500)->each(function ($item, $key) {
                $news = db(\tname::weixin_news)->column('id,name');
                if ($item['type'] == 'news') {
                    $item['news_name'] = $news[$item['content']];
                }
                return $item;
            });

            $this->assign('dataList', $dataList);
            $html = $this->fetch('wechat/form');

            return ajaxSuccess($html);
        }
        return $this->fetch();
    }

    /**
     * 关键词回复设置添加 2017-10-15
     */
    public function keywordsadd()
    {
        if (request()->isPost()) {
            $data = input('post.');

            $data['uid'] = UID;
            if ($data['type'] == 'text') {
                $data['content'] = input('post.replytext');
            } else if ($data['type'] == 'image') {
                $data['content'] = input('post.replyimage');
            } else if ($data['type'] == 'news') {
                $data['content'] = input('post.replynewsid');
            }

            $res = dataUpdate(\tname::weixin_keywords, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::weixin_keywords)->find($id);

            $newsList = db(\tname::weixin_news)->where(array('uid' => UID))->select();

            $this->assign('data', $data);
            $this->assign('newsList', $newsList);
            return $this->fetch();
        }
    }

    /**
     * 微信事件设置 2017-10-15
     */
    public function wxevent()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;
            $res = dataUpdate(\tname::weixin_event, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $data = db(\tname::weixin_event)->where(array('uid' => UID, 'event' => 'subscribe'))->find();
            $keywordsList = db(\tname::weixin_keywords)->where(array('uid' => UID, 'is_hidden' => 0))->select();

            $this->assign('data', $data);
            $this->assign('keywordsList', $keywordsList);
            return $this->fetch();
        }
    }

    /**
     * 自定义菜单设置    2017-10-15
     */
    public function classify()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;

            if (isset($data['name_0'])) {
                $classify_one_name = $data['name_0'];
                foreach ($classify_one_name as $k => $v) {
                    $button = array();
                    $cur = $k + 1;
                    if (!isset($data['type_' . $cur])) {     //二级菜单不存在
                        if (!$data['type_0'][$k]) {
                            return array('ret' => 0, 'msg' => '请将自定义菜单填写完整');
                        }
                        $button = array('name' => $v, 'type' => $data['type_0'][$k], 'content' => $data['content_0'][$k]);
                    } else {    //二级菜单存在
                        $button['name'] = $v;
                        foreach ($data['type_' . $cur] as $k1 => $v1) {
                            if (!$v1) {
                                return array('ret' => 0, 'msg' => '请将自定义菜单填写完整');
                            } else {
                                $button['sub_button'][] = array('name' => $data['name_' . $cur][$k1], 'type' => $v1, 'content' => $data['content_' . $cur][$k1]);
                            }
                        }
                    }
                    $classify[] = $button;
                }

                $data['classify'] = serializeMysql($classify);
            } else {
                $data['classify'] = '';
            }

            $res = dataUpdate(\tname::weixin_classify, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $data = db(\tname::weixin_classify)->where(array('uid' => UID))->find();
            if (!empty($data)) {
                $data['classify'] = serializeMysql($data['classify'], 1);
            }

            $keywordsList = db(\tname::weixin_keywords)->where(array('uid' => UID, 'is_hidden' => 0))->select();
            $this->assign('data', $data);
            $this->assign('keywordsList', $keywordsList);
            return $this->fetch();
        }
    }

    /**
     * 发布菜单   2017-10-15
     */
    public function classifypublish()
    {
        $data = db(\tname::weixin_classify)->where(array('uid' => UID))->find();
        if (!empty($data)) {
            $data['classify'] = serializeMysql($data['classify'], 1);
        }
        foreach ($data['classify'] as $k => $v) {
            $button = array();
            if (isset($v['sub_button'])) {    //二级菜单存在
                $button['name'] = $v['name'];
                foreach ($v['sub_button'] as $vv) {
                    $button['sub_button'][] = $this->classifyturn($vv['name'], $vv['type'], $vv['content']);
                }
            } else {    //二级菜单不存在
                $button = $this->classifyturn($v['name'], $v['type'], $v['content']);
            }
            $classify['button'][] = $button;
        }
        $access_token = getaccesstoken(UID);

        $Classify = new \wechat\Classify();
        $res = $Classify->publish($classify, $access_token, array('uid' => UID));
        if ($res['errcode'] == 0) {
            return array('ret' => 1);
        } else {
            return array('ret' => 0, 'msg' => $res['errmsg']);
        }
    }

    /**
     * 转换菜单   2017-10-15
     */
    public function classifyturn($name, $type, $content)
    {

        if ($type == 'click') {
            return array('type' => $type, 'name' => $name, 'key' => $content);
        } else if ($type == 'view') {
            return array('type' => $type, 'name' => $name, 'url' => $content);
        } else if ($type == 'scancode_push') {
            return array('type' => $type, 'name' => $name, 'key' => 'rselfmenu_0_1');
        } else if ($type == 'scancode_waitmsg') {
            return array('type' => $type, 'name' => $name, 'key' => 'rselfmenu_0_0');
        } else if ($type == 'pic_sysphoto') {
            return array('type' => $type, 'name' => $name, 'key' => 'rselfmenu_1_0');
        } else if ($type == 'pic_photo_or_album') {
            return array('type' => $type, 'name' => $name, 'key' => 'rselfmenu_1_1');
        } else if ($type == 'pic_weixin') {
            return array('type' => $type, 'name' => $name, 'key' => 'rselfmenu_1_2');
        } else if ($type == 'location_select') {
            return array('type' => $type, 'name' => $name, 'key' => 'rselfmenu_2_0');
        } else if ($type == 'miniprogram') {

            $content = explode(' ', $content);

           
            return array('type' => $type, 'name' => $name, 'appid' => $content[0], 'pagepath' => $content[1], 'url' => $content[2]);
        }

    }

    /**
     * 删除菜单   2017-10-15
     */
    public function classifydel()
    {
        $access_token = getaccesstoken(UID);

        $Classify = new \wechat\Classify();
        $res = $Classify->del($access_token);
        if ($res['errcode'] == 0) {
            return array('ret' => 1);
        } else {
            return array('ret' => 0, 'msg' => $res['errmsg']);
        }
    }

}