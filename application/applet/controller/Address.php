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
        $city=[];
        $district=[];
        $addressLogic = new AddressLogic();
        $addressInfo =$addressLogic->getAddress($pdata['address_id']);
        $province = db(\tname::region)->where(array('level'=>1))->order('code asc')->select();

        $sureIndex = [0,0,0];
        $selectIndex=[0, 0, 0];
        if($addressInfo){
            $province_id=db(\tname::region)->where(array('code'=>$addressInfo['province_code']))->value('id');
            $city = db(\tname::region)->where(array('parent_id'=>$province_id))->order('code asc')->select();
            $city_id=db(\tname::region)->where(array('code'=>$addressInfo['city_code']))->value('id');
            $district = db(\tname::region)->where(array('parent_id'=>$city_id))->order('code asc')->select();

            foreach ($province as $key_p=>$val_p){
                if($val_p['code']==$addressInfo['province_code']){
                    $sureIndex[0]=$key_p;
                    $selectIndex[0]=$key_p;
                }
            }
            foreach ($city as $key_c=>$val_c){
                if($val_c['code']==$addressInfo['city_code']){
                    $sureIndex[1]=$key_c;
                    $selectIndex[1]=$key_c;
                }
            }
            foreach ($district as $key_d=>$val_d){
                if($val_p['code']==$addressInfo['district_code']){
                    $sureIndex[2]=$key_d;
                    $selectIndex[2]=$key_d;
                }
            }
        }else{
            $city = db(\tname::region)->where(array('parent_id'=>$province[0]['id']))->order('code asc')->select();
            $district = db(\tname::region)->where(array('parent_id'=>$city[0]['id']))->order('code asc')->select();
        }

        $returndata = array(
            'addressInfo'=>$addressInfo,
            'province'=>$province,
            'city'=>$city,
            'district'=>$district,
            'sureIndex'=>$sureIndex,
            'selectIndex'=>$selectIndex,
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