<?php
namespace app\admin\controller;

class Integral extends Member
{

    /**
     * 积分设置 2017-10-15
     */
    public function config()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;

            $res = dataUpdate(\tname::integral_config, $data);
            if(!$res){
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $data = db(\tname::integral_config)->where('uid', UID)->find();

            $this->assign('data', $data);
            return $this->fetch();
        }
    }

    //积分日志  2018-02-05
    public function log()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $where =[
                'a.uid' => UID,
                'a.classify' => 'integral',
            ];
            if ($search['time']) {
                $search['time'] = explode('-', $search['time']);
                $where['a.create_time'] = array('between time', [$search['time'][0], $search['time'][1]]);
            }
            if ($search['keyword'] !== null && $search['keyword'] !== '') {
                $where['v.nickname|a.remark'] = array('like', '%' . $search['keyword'] . '%');
            }

            $dataList = db(\tname::data_changelog)->alias('a')
                ->join('xa_'.\tname::vip.' v','a.main_id=v.openid','left')
                ->field('a.*,v.nickname nickname')
                ->where($where)->order('a.id desc')->paginate(50, false,['page' => $search['page']]);

            $this->assign('dataList', $dataList);
            $html = $this->fetch('integral/form');
            $attach = array(
                'page' => $dataList->render()
            );
            return ajaxSuccess($html, '', '', $attach);
        }

        return $this->fetch();
    }

    //积分变动  2018-02-05
    public function integraladd()
    {
        if (request()->isPost()) {
            $data = input('post.');

            $result = dataChangeLog(UID, 'integral', 'system', $data['openid'], $data['field_change'] . $data['integral'], 0, '系统更改');
            if (!$result[0]) {
                return ajaxFalse($result[1]);
            }
            return ajaxSuccess();
        } else {
            $where_vip = [
                'uid' => UID,
                'subscribe' => 1
            ];
            $vipList = db(\tname::vip)->where($where_vip)->select();

            $this->assign('vipList', $vipList);
            return $this->fetch();
        }
    }


    /**
     * 积分商城轮播     2017-10-15
     */
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
            if(!$res){
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $data = db(\tname::carousel)->where(array('uid' => UID, 'type' => 'integral'))->find();
            if (!empty($data)) {
                $data['carousel'] = serializeMysql($data['carousel'], 1);
            }
            $urlList = db(\tname::weixin_url)->where(array('uid' => UID, 'is_hidden' => 0))->select();

            $this->assign('data', $data);
            $this->assign('urlList', $urlList);
            return $this->fetch();
        }
    }

    /**
     * 产品列表    2017-10-15
     */
    public function product()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $where = array(
                'uid' => UID,
                'is_hidden' => 0
            );
            if ($search['keyword']) {
                $where['name'] = array('like', '%' . $search['keyword'] . '%');
            }

            $dataList = db(\tname::integral_product)->where($where)->order('id desc')->paginate(50, false, array('page' => $search['page']));
            $this->assign('dataList', $dataList);
            $html = $this->fetch('integral/form');
            $attach = array(
                'page' => $dataList->render()
            );
            return ajaxSuccess($html, '', '', $attach);
        }
        return $this->fetch();
    }

    /**
     * 添加产品    2017-10-15
     */
    public function productadd()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;
            $data['imgpath'] = serializeMysql($data['imgpath']);

            $res = dataUpdate(\tname::integral_product, $data);
            if(!$res){
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::integral_product)->find($id);
            if ($id) {
                $data['imgpath'] = serializeMysql($data['imgpath'], 1);
            }

            $this->assign('data', $data);
            return $this->fetch();
        }
    }

    /**
     * 积分商城订单列表 2017-10-15
     */
    public function order()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $where = array(
                'a.uid' => UID,
                'a.is_hidden' => 0
            );
            if ($search['time']) {
                $search['time'] = explode('-', $search['time']);
                $where['a.create_time'] = array('between time', [$search['time'][0], $search['time'][1]]);
            }
            if ($search['keyword'] !== null && $search['keyword'] !== '') {
                $where['v.nickname|a.order_number|a.product_name|a.linkman|a.linktel|a.address'] = array('like', '%' . $search['keyword'] . '%');
            }
            if ($search['status'] !== null && $search['status'] !== '') {
                $where['a.status'] = $search['status'];
            }
            session('where', $where);
            $dataList = db(\tname::integral_order)->alias('a')
                ->join('xa_vip'.' v','a.openid=v.openid','left')
                ->field('a.*,v.nickname nickname')
                ->where($where)->order('a.id desc')->paginate(50, false, array('page' => $search['page']));

            $this->assign('dataList', $dataList);
            $html = $this->fetch('integral/form');
            $attach = array(
                'page' => $dataList->render()
            );
            return ajaxSuccess($html, '', '', $attach);
        }

        return $this->fetch();
    }

    /**
     * 修改订单    2017-10-15
     */
    public function orderdetail()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;

            $res = dataUpdate(\tname::integral_order, $data);
            if(!$res){
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::integral_order)->alias('a')
                ->join('xa_vip'.' v','a.openid=v.openid','left')
                ->field('a.*,v.nickname nickname')
                ->find($id);

            if ($id) {
                $data['product'] = serializeMysql($data['product'], 1);
            }

            $this->assign('data', $data);
            return $this->fetch();
        }
    }


    /**
     * 订单列表导出    2017-10-15
     */
    public function orderexport()
    {
        $where = session('where');
        $dataList = db(\tname::integral_order)->where($where)->order('id desc')->select();
        foreach ($dataList as $key => $value) {
            switch ($value['status']) {
                case 0:
                    $value['status_'] = '待付款';
                    break;
                case 1:
                    $value['status_'] = '待发货';
                    break;
                case 2:
                    $value['status_'] = '已发货';
                    break;
                case 3:
                    $value['status_'] = '已完成';
                    break;
            }
            $data[$key] = array(
                $value['nickname'],//昵称
                $value['order_number'],//订单编号
                $value['product_name'],//名称
                $value['pay_integral'],//积分
                $value['pay_money'],//实际付款
                $value['linkman'],//联系人
                $value['linktel'],//联系电话
                $value['address'],//地址
                date('Y-m-d H:i:s', $value['create_time']),//时间
                $value['status_'],//状态
            );
        };

        $fileName = "订单表";
        $headArr = array("昵称", "订单编号", "名称", "积分", "实际付款", "联系人", "联系电话", "地址", "时间", "状态");
        $msg = '状态：0代表未处理 1代表已处理';
        $res = getExcel($fileName, $headArr, $data, $msg);
        return $res;
    }
}