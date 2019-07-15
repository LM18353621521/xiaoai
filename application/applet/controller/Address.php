<?php
namespace app\applet\controller;
use app\common\logic\AddressLogic;
class Address extends Applet
{
    /**
     * 我的收货地址
     * @return \think\response\Json
     */
    public function addressList(){
        $pdata = input('');
        $addressLogic = new AddressLogic();
        $dataList =$addressLogic->getAddressList($pdata['vip_ids']);
        $returndata = array(
            'addressList'=>$dataList,
        );
        return json(ajaxSuccess($returndata));
    }

    /**
     * 地址详情
     * @return \think\response\Json
     */
    public function addressInfo(){
        $pdata = input('');
        $addressLogic = new AddressLogic();
        $addressInfo =$addressLogic->getAddress($pdata['address_id']);
        $returndata = array(
            'addressInfo'=>$addressInfo,
        );
        return json(ajaxSuccess($returndata));
    }

    /**
     * 添加编辑地址
     * @return \think\response\Json
     */
    public function addEditAddress(){
        $pdata = input('');
        $addressLogic = new AddressLogic();
        $result =$addressLogic->addEditAddress($pdata['vip_id'],$pdata);
        return json($result);
    }
    /**
     * 设置默认地址
     * @return \think\response\Json
     */
    public function setDefault(){
        $pdata = input('');
        $addressLogic = new AddressLogic();
        $result =$addressLogic->addressdefault($pdata['vip_id'],$pdata['address_id']);
        return json($result);
    }

    /**
     * 删除地址
     * @return \think\response\Json
     */
    public function address_del(){
        $pdata = input('');
        $addressLogic = new AddressLogic();
        $result =$addressLogic->addressdefault($pdata['address_id']);
        return json($result);
    }
    /**
     * 获取省市区下级
     */
    public function getNextRegion(){
        $pdata = input('');
        $dataList = db(\tname::region)->where(array('parent_id'=>$pdata['parent_id']))->order('code asc')->select();
        $list_next=[];
        if($dataList){
            $list_next = db(\tname::region)->where(array('parent_id'=>$dataList[0]['id']))->order('code asc')->select();
        }

        $returndata = array(
            'list'=>$dataList,
            'list_next'=>$list_next
        );
        return json(ajaxSuccess($returndata));
    }


}