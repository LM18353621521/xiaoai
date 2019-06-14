<?php
namespace app\admin\controller;

class Money extends Member
{

    //余额设置 2017-10-15
    public function config()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;

            $res = dataUpdate(\tname::money_config, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $data = db(\tname::money_config)->where('uid', UID)->find();

            $this->assign('data', $data);
            return $this->fetch();
        }
    }

    //充值卡列表 2017-10-15
    public function card()
    {
        if (request()->isPost()) {
            $where = array(
                'uid' => UID,
                'is_hidden' => 0
            );
            $dataList = db(\tname::money_card)->where($where)->order('sort')->paginate(500);

            $this->assign('dataList', $dataList);
            $html = $this->fetch('money/form');

            return ajaxSuccess($html);
        }
        return $this->fetch();
    }

    //添加充值卡 2017-10-15
    public function cardadd()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;

            $res = dataUpdate(\tname::money_card, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::money_card)->find($id);

            $this->assign('data', $data);
            return $this->fetch();
        }
    }

    /**
     * 订单列表 2017-10-15
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
                $where['v.nickname|a.order_number'] = array('like', '%' . $search['keyword'] . '%');
            }

            $dataList = db(\tname::money_order)->alias('a')
                ->join('xa_' . \tname::vip . ' v', 'a.openid=v.openid', 'left')
                ->field('a.*,v.nickname nickname')
                ->where($where)->order('a.id desc')->paginate(50, false, array('page' => $search['page']));

            $this->assign('dataList', $dataList);
            $html = $this->fetch('money/form');
            $attach = array(
                'page' => $dataList->render()
            );
            return ajaxSuccess($html, '', '', $attach);
        }

        return $this->fetch();
    }


    //会员余额日志  2018-02-05
    public function log()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $where = [
                'a.uid' => UID,
                'a.classify' => 'money',
            ];
            if ($search['time']) {
                $search['time'] = explode('-', $search['time']);
                $where['a.create_time'] = array('between time', [$search['time'][0], $search['time'][1]]);
            }
            if ($search['keyword'] !== null && $search['keyword'] !== '') {
                $where['v.nickname|a.remark'] = array('like', '%' . $search['keyword'] . '%');
            }

            $dataList = db(\tname::data_changelog)->alias('a')
                ->join('xa_' . \tname::vip . ' v', 'a.main_id=v.openid', 'left')
                ->field('a.*,v.nickname nickname')
                ->where($where)->order('a.id desc')->paginate(50, false, array('page' => $search['page']));

            $this->assign('dataList', $dataList);
            $html = $this->fetch('money/form');
            $attach = array(
                'page' => $dataList->render()
            );
            return ajaxSuccess($html, '', '', $attach);
        }

        return $this->fetch();
    }

    //余额变动  2018-02-05
    public function moneyadd()
    {
        if (request()->isPost()) {
            $data = input('post.');

            $result = dataChangeLog(UID, 'money', 'system', $data['openid'], $data['field_change'] . $data['money'], 0, '系统更改');
            if (!$result[0]) {
                return ajaxFalse($result[1]);
            }
            return ajaxSuccess();
        } else {
            $where_vip = array(
                'uid' => UID,
                'subscribe' => 1
            );
            $vipList = db(\tname::vip)->where($where_vip)->select();

            $this->assign('vipList', $vipList);
            return $this->fetch();
        }
    }

    //提现列表    2018-02-05
    public function withdraw()
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
                $where['v.nickname|a.order_number'] = array('like', '%' . $search['keyword'] . '%');
            }
            if ($search['status'] !== null && $search['status'] !== '') {
                $where['a.status'] = $search['status'];
            }

            $dataList = db(\tname::money_withdraw)->alias('a')
                ->join('xa_' . \tname::vip . ' v', 'a.openid=v.openid', 'left')
                ->field('a.*,v.nickname nickname')
                ->where($where)->order('a.id desc')->paginate(50, false, ['page' => $search['page']]);

            $this->assign('dataList', $dataList);
            $html = $this->fetch('money/form');
            $attach = array(
                'page' => $dataList->render()
            );
            return ajaxSuccess($html, '', '', $attach);
        }

        return $this->fetch();
    }

    //提现审核  2018-02-05
    public function withdrawcheck()
    {
        $data = input('post.');
        $withdraw = db(\tname::money_withdraw)->find($data['id']);
        if ($withdraw['status'] != 0) {
            return ajaxFalse('您已经处理过该申请');
        }
        db()->startTrans();
        if ($data['afterchange'] == 1) {

            $updatedata = [
                'id' => $data['id'],
                'status' => 1
            ];
            $res1 = dataUpdate(\tname::money_withdraw, $updatedata);
            if (!$res1) {
                db()->rollback();
                return ajaxFalse();
            }
            $ordernumber = createOrdernumber(\tname::money_withdraw);

            $result = wxEnterprisePayment(UID, $withdraw['openid'], $withdraw['money'], $ordernumber, '提现金额到账');
            if ($result[0]) {
                $updatedata = [
                    'id' => $data['id'],
                    'is_arrival' => 1,
                    'check_time' => time(),
                    'order_number' => $ordernumber
                ];
                $res2 = dataUpdate(\tname::money_withdraw, $updatedata);
                if (!$res2) {
                    db()->rollback();
                    return ajaxFalse();
                }
            } else {
                db()->rollback();
                return ajaxFalse($result[1]);
            }

            db()->commit();
            return ajaxSuccess();
        } elseif ($data['afterchange'] == -1) {
            $res1 = dataChangeLog(UID, 'money', 'withdraw_fail', $withdraw['openid'], $withdraw['money'], $data['id'], '提现失败退还');
            if (!$res1) {
                db()->rollback();
                return ajaxFalse();
            }

            $updatedata = [
                'id' => $data['id'],
                'status' => -1,
                'check_time' => time()
            ];
            $res2 = dataUpdate($data['tablename'], $updatedata);
            if (!$res2) {
                db()->rollback();
                return ajaxFalse();
            }

            db()->commit();
            return ajaxSuccess();
        }

    }
}