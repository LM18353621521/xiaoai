<?php

namespace app\admin\controller;
error_reporting(E_ERROR | E_PARSE);
use think\Cache;

class Config extends Member
{
    /*
 * 配置入口
 */
    public function index()
    {
        /*配置列表*/
        $group_list = [
            'shop_info' => '网站信息',
            'basic' => '基本设置',
            'cash' => '提现设置',
            'sms' => '短信设置',
            'shopping' => '购物流程设置',
            'smtp' => '邮件设置',
            'water' => '水印设置',
            'distribut' => '分销设置',
            'push' => '推送设置',
            'oss' => '对象存储',
            'express' => '物流设置'
        ];
        $this->assign('group_list', $group_list);
        $inc_type = I('get.inc_type', 'shop_info');
        $this->assign('inc_type', $inc_type);
        $config = tpCache($inc_type);
        if ($inc_type == 'shop_info') {
            $province = M('region')->where(array('parent_id' => 0))->select();
            $city = M('region')->where(array('parent_id' => $config['province']))->select();
            $area = M('region')->where(array('parent_id' => $config['city']))->select();
            $this->assign('province', $province);
            $this->assign('city', $city);
            $this->assign('area', $area);
        }
        $this->assign('config', $config);//当前配置项
        //C('TOKEN_ON',false);
        return $this->fetch($inc_type);
    }

    /*
     * 新增修改配置
     */
    public function handle()
    {
        $param = I('post.');
        $inc_type = $param['inc_type'];
        //unset($param['__hash__']);
        unset($param['inc_type']);
        tpCache($inc_type, $param);
        // 设置短信商接口
        if ($param['sms_platform'] == 2 && !empty($param['sms_appkey']) && !empty($param['sms_secretKey'])) {
            $sms_appkey = trim($param['sms_appkey']);
            $sms_secretKey = trim($param['sms_secretKey']);
            $url = 'http://open.1cloudsp.com:8090/api/admin/setParentId?parentId=14257&accesskey=' . urlencode($sms_appkey) . '&secret=' . urlencode($sms_secretKey);
            httpRequest($url);
        }
        $this->success("操作成功", U('System/index', array('inc_type' => $inc_type)));
    }

    //基础设置 2017-10-15
    public function config()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $inc_type = $data['inc_type'];
            //unset($param['__hash__']);
            unset($data['inc_type']);
            if ($inc_type == 'base') {
                $config = tpCache($inc_type);
                if ($data['goods_poster_bg'] && $config['goods_poster_bg'] != $data['goods_poster_bg']) {
                    $goods_poster_bg = edit_img(imgurlToAbsolute($data['goods_poster_bg']));
                    $data['goods_poster_bg'] = imgurlToAbsolute($goods_poster_bg);
                }
                if ($data['user_poster_bg'] && $config['user_poster_bg'] != $data['user_poster_bg']) {
                    $user_poster_bg = edit_img(imgurlToAbsolute($data['user_poster_bg']));
                    $data['user_poster_bg'] = imgurlToAbsolute($user_poster_bg);
                }
            }
            if ($inc_type == 'slogen') {
                $data['slogen_text'] = serialize($data['slogen_text']);
            }
            tpCache($inc_type, $data);
            $res = 1;
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $inc_type = input('param.inc_type', 'web');
            $inc_type_list = array(
                'web' => '网站配置',
                'base' => '基本设置',
                'shopping' => '购物流程设置',
                'express' => '物流配置',
                'sms' => '短信设置',
                'slogen' => 'PC底部标签',
            );
            $config = tpCache($inc_type);
            if ($inc_type == 'shop_info') {
                $province = M('region')->where(array('parent_id' => 0))->select();
                $city = M('region')->where(array('parent_id' => $config['province']))->select();
                $area = M('region')->where(array('parent_id' => $config['city']))->select();
                $this->assign('province', $province);
                $this->assign('city', $city);
                $this->assign('area', $area);
            }
            if ($inc_type == 'slogen') {
                $config['slogen_text'] = unserialize($config['slogen_text']);
            }


            $this->assign('config', $config);//当前配置项
            $this->assign('data', $config);
            $this->assign('inc_type', $inc_type);
            $this->assign('inc_type_list', $inc_type_list);
            return $this->fetch($inc_type);
        }
    }

    //地区列表
    public function freight_list()
    {
        if (request()->isPost()) {
            $dataList = db(\tname::config_freight)->alias('a')
                ->join(\tname::region . ' r', 'a.region_id=r.id')
                ->field('a.*,r.name')
                ->order('a.id asc')->select();
            $this->assign('dataList', $dataList);
            $html = $this->fetch('config/form');
            return ajaxSuccess($html);
        }
        return $this->fetch();
    }

    /**
     * 编辑运费
     * @return array|mixed|string
     */
    public function freight_add()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $res = dataUpdate(\tname::config_freight, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::config_freight)->alias('a')
                ->join(\tname::region . ' r', 'a.region_id=r.id')
                ->field('a.*,r.name')
                ->where(array('a.id' => $id))
                ->find();
            $this->assign('data', $data);
            return $this->fetch();
        }
    }

    public function carousel()
    {
        if (request()->isPost()) {
            $data = input('post.');
            foreach ($data['imgpath'] as $key => $value) {
                $carousel[] = array(
                    'imgpath' => $value,
                    'url' => $data['url'][$key]
                );
            }
            $data['carousel'] = serializeMysql($carousel);
            $data['uid'] = UID;
            $res = dataUpdate(\tname::carousel, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $type = input('type', 'pc');
            $data = db(\tname::carousel)->where(array('uid' => UID, 'type' => $type))->find();
            if (!empty($data)) {
                $data['carousel'] = serializeMysql($data['carousel'], 1);
            }
            $this->assign('data', $data);
            $this->assign('type', $type);
            return $this->fetch();
        }
    }

    //地区列表
    public function area()
    {
        if (request()->isPost()) {
            $where = [
                'a.uid' => UID,
                'a.is_hidden' => 0,
            ];
            $dataList = db(\tname::area)->alias('a')
                ->where($where)->order('a.sort,a.id desc')->select();
            $dataList = list_to_tree($dataList);
            $this->assign('dataList', $dataList);
            $html = $this->fetch('config/form');
            return ajaxSuccess($html);
        }
        return $this->fetch();
    }

    //地区添加
    public function areaadd()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;
            if ($data['pid'] == $data['id']) {
                return ajaxFalse('您不能选择自己为下级');
            }
            $res = dataUpdate(\tname::area, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::area)->find($id);
            $where = [
                'uid' => UID,
                'is_hidden' => 0,
                'pid' => 0
            ];
            $areaList = db(\tname::area)->where($where)->select();
            $this->assign('data', $data);
            $this->assign('areaList', $areaList);
            return $this->fetch();
        }
    }

    //导入页面  导入的excel数据必须为纯文本格式	2017-10-15
    public function import()
    {
        if (request()->isPost()) {
            $pdata = input('post.');
            $data = getData('.' . $pdata['excelpath']);
            if (!$data[0]) {
                return ajaxFalse($data[1]);
            }
            foreach ($data[1] as $key => $value) {
                $list[] = $value;
            }
            session('list', $list);
            return ajaxSuccess();
        } else {
            return $this->fetch();
        }
    }

    /**
     * 清空系统缓存
     */
    public function cleancache(){
        delFile(RUNTIME_PATH);
        Cache::clear();
        $quick = input('quick',0);
        if($quick == 1){
            $script = "<script>parent.layer.msg('缓存清除成功', {time:3000,icon: 1});window.parent.location.reload();</script>";
        }else{
            $script = "<script>parent.layer.msg('缓存清除成功', {time:3000,icon: 1});window.location='/index.php?m=Admin&c=Index&a=welcome';</script>";
        }
        echo $script;
        return $this->fetch();
    }

}