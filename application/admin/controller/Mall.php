<?php

namespace app\admin\controller;

use think\Config;

class Mall extends Member
{
    /**
     * 商品模型  2018-07-30 Lu
     * @return
     */
    public function goods_type()
    {
        if (request()->isPost()) {
            $dataList = db(\tname::goods_type)->alias('a')
                ->order('a.id desc')->select();
            $this->assign('dataList', $dataList);
            $html = $this->fetch('mall/form');
            return ajaxSuccess($html);
        }
        return $this->fetch();
    }

    /**
     * 添加商品模型  2018-07-30 Lu
     * @return array|mixed|string
     */
    public function goods_type_add()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $res = dataUpdate(\tname::goods_type, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::goods_type)->find($id);
            $this->assign('data', $data);
            return $this->fetch();
        }
    }

    /**
     * 删除商品类型
     */
    public function delGoodsType()
    {
        // 判断 商品规格
        $id = input('id');
        $count = db(\tname::goods_spec)->where("type_id = {$id}")->count("1");
        if($count>0){
            return ajaxFalse("该类型下有商品规格不得删除!");
        }
        // 删除分类
        db(\tname::goods_type)->where("id = {$id}")->delete();
        return ajaxSuccess();
    }

    /**
     * 商品规格列表
     */
    public function goods_spec()
    {
        if (request()->isPost()) {
            $search = input('post.');

            $where = array();
            if ($search['goods_type']) {
                $where['a.type_id'] = $search['goods_type'];
            }

            if ($search['keyword']) {
                $where['a.name'] = array('like', '%' . $search['keyword'] . '%');
            }
//            dump($where);

            $dataList = db(\tname::goods_spec)->alias('a')
                ->join(\tname::goods_type . ' gt', 'a.type_id=gt.id')
                ->field('a.*,gt.name as goods_type')
                ->where($where)->order('a.sort asc,a.id desc')->paginate(15, false, array('page' => $search['page']))
                ->each(function ($item, $key) {
                    $spec_item = db(\tname::goods_spec_item)->where(array('spec_id' => $item['id']))->column('item');
                    $item['spec_item'] = implode(' , ', $spec_item);
                    return $item;
                });

            $this->assign('dataList', $dataList);
            $html = $this->fetch('mall/form');
            $attach = array(
                'page' => $dataList->render()
            );
            return ajaxSuccess($html, '', '', $attach);
        }

        $goods_type = db(\tname::goods_type)->order('id desc')->select();
        $this->assign('goods_type', $goods_type);
        return $this->fetch();
    }

    /**
     * 添加修改编辑  商品规格
     */
    public function goods_spec_add()
    {

        if (request()->isPost()) {
            $data = input('post.');
            $res = dataUpdate(\tname::goods_spec, $data);
            if (!$res) {
                return ajaxFalse();
            }
            $spec_id = $data['id'] ? $data['id'] : $res;
            $this->spec_afterSave($spec_id);
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::goods_spec)->find($id);
            if ($data) {
                $items = db(\tname::goods_spec_item)->where(array('spec_id' => $data['id']))->column('item');
                $data['items'] = implode("\n", $items);
            }
            $goods_type = db(\tname::goods_type)->order('id desc')->select();
            $this->assign('data', $data);
            $this->assign('goods_type', $goods_type);
            return $this->fetch();
        }
    }

    /**
     * 后置操作方法
     * 自定义的一个函数 用于数据保存后做的相应处理操作, 使用时手动调用
     * @param int $id 规格id
     */
    public function spec_afterSave($id)
    {

        $model = db(\tname::goods_spec_item); // 实例化User对象
        $post_items = explode("\n", input('post.items'));
        foreach ($post_items as $key => $val)  // 去除空格
        {
            $val = str_replace('_', '', $val); // 替换特殊字符
            $val = str_replace('@', '', $val); // 替换特殊字符
            $val = trim($val);
            if (empty($val))
                unset($post_items[$key]);
            else
                $post_items[$key] = $val;
        }
        $db_items = $model->where("spec_id = $id")->column('id,item');
        // 两边 比较两次

        /* 提交过来的 跟数据库中比较 不存在 插入*/
        $dataList = [];
        foreach ($post_items as $key => $val) {
            if (!in_array($val, $db_items))
                $dataList[] = array('spec_id' => $id, 'item' => $val);
        }
        // 批量添加数据
        $dataList && $model->insertAll($dataList);

        /* 数据库中的 跟提交过来的比较 不存在删除*/
        foreach ($db_items as $key => $val) {
            if (!in_array($val, $post_items)) {
//                M("SpecGoodsPrice")->where("`key` REGEXP '^{$key}_' OR `key` REGEXP '_{$key}_' OR `key` REGEXP '_{$key}$' or `key` = '{$key}'")->delete(); // 删除规格项价格表
                $model->where('id=' . $key)->delete(); // 删除规格项
            }
        }
    }


    /**
     * 删除商品属性
     */
    public function goods_spec_del()
    {
        $data = input('post.');
        $ids = $data['id'];
        if (empty($ids)) {
            return ajaxFalse();
        }
        $aspec_ids = rtrim($ids, ",");
        // 判断 商品规格项
//        $count_ids = M("SpecItem")->whereIn('spec_id',$aspec_ids)->group('spec_id')->getField('spec_id',true);
        $count_ids = db(\tname::goods_spec_item)->whereIn('spec_id', $aspec_ids)->group('spec_id')->column('spec_id');
        if ($count_ids) {
            $count_ids = implode(',', $count_ids);
//            $this->ajaxReturn(['status' => -1,'msg' => "ID为【{$count_ids}】规格，清空规格项后才可以删除!"]);
            return ajaxFalse("ID为【{$count_ids}】规格，清空规格项后才可以删除!");
        }
        // 删除分类
//        M('Spec')->whereIn('id',$aspec_ids)->delete();
        $res = db(\tname::goods_spec)->whereIn('id', $aspec_ids)->delete();
        return ajaxSuccess();
    }

    public function widget()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;
            $data['imgpath'] = serializeMysql($data['imgpath']);

            $res = dataUpdate(\tname::mall_product, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::mall_product)->find($id);
            if ($id) {
                $data['imgpath'] = serializeMysql($data['imgpath'], 1);
            }

            $where = array(
                'uid' => UID,
                'is_hidden' => 0,
                'pid' => 0,
            );
            $categoryList = db(\tname::mall_category)->where($where)->order('sort')->select();

            $this->assign('categoryList', $categoryList);
            $this->assign('data', $data);
            return $this->fetch();
        }
    }

    //商城轮播     2017-10-15
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
            $data = db(\tname::carousel)->where(array('uid' => UID, 'type' => 'mall'))->find();
            if (!empty($data)) {
                $data['carousel'] = serializeMysql($data['carousel'], 1);
            }
            $urlList = db(\tname::weixin_url)->where(array('uid' => UID, 'is_hidden' => 0))->select();

            $this->assign('data', $data);
            $this->assign('urlList', $urlList);
            return $this->fetch();
        }
    }

    //产品分类     2017-10-15
    public function category()
    {
        if (request()->isPost()) {
            $where = [
                'a.uid' => UID,
                'a.is_hidden' => 0
            ];
            $dataList = db(\tname::mall_category)->alias('a')
                ->where($where)->order('a.sort,a.id desc')->select();
            $dataList = list_to_tree($dataList);

            $this->assign('dataList', $dataList);
            $html = $this->fetch('mall/form');

            return ajaxSuccess($html);
        }
        return $this->fetch();
    }

    //产品分类添加     2017-10-15
    public function categoryadd()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;
            if ($data['pid'] == $data['id']) {
                return ajaxFalse('您不能选择自己为下级');
            }
            $res = dataUpdate(\tname::mall_category, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::mall_category)->find($id);

            $where = [
                'uid' => UID,
                'is_hidden' => 0,
                'pid' => 0
            ];
            $categoryList = db(\tname::mall_category)->where($where)->select();

            $this->assign('data', $data);
            $this->assign('categoryList', $categoryList);
            return $this->fetch();
        }
    }

    //产品列表    2017-10-15
    public function product()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $where = array(
                'a.uid' => UID,
                'a.is_hidden' => 0
            );

            if ($search['stime'] && $search['etime']) {
                $where['a.create_time'] = array('between time', [$search['stime'], $search['etime']]);
            }

            if ($search['keyword']) {
                $where['a.name|c.name|a.brief'] = array('like', '%' . $search['keyword'] . '%');
            }
            if ($search['is_publish'] != '') {
                $where['a.is_publish'] = $search['is_publish'];
            }

            $dataList = db(\tname::mall_product)->alias('a')
                ->join(\tname::mall_category . ' c', 'a.category_id = c.id', 'left')
                ->field('a.*,c.name category_name')
                ->where($where)->order('a.id desc')->paginate(15, false, array('page' => $search['page']));

            $this->assign('dataList', $dataList);
            $html = $this->fetch('mall/form');
            $attach = array(
                'page' => $dataList->render()
            );
            return ajaxSuccess($html, '', '', $attach);
        }

        return $this->fetch();
    }

    //添加产品    2017-10-15
    public function productadd()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;
            if(isset($data['imgpath'])){
                $data['imgpath'] = serializeMysql($data['imgpath']);
            }
            if(isset($data['agent_less'])){
                $data['agent_less'] = serializeMysql($data['agent_less']);
            }
            $res = dataUpdate(\tname::mall_product, $data);
            if (!$res) {
                return ajaxFalse();
            }
            $goods_id = $data['id'] ? $data['id'] : $res;
            $this->afterSave($goods_id);
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::mall_product)->find($id);
            if ($id) {
                $data['imgpath'] = serializeMysql($data['imgpath'], 1);
            }
            if($data['agent_less']){
                $data['agent_less'] = serializeMysql($data['agent_less'], 1);
            }
            $where = array(
                'uid' => UID,
                'is_hidden' => 0,
                'pid' => 0,
            );
            $categoryList = db(\tname::mall_category)->where($where)->order('sort')->select();
            $goods_type = db(\tname::goods_type)->order('id desc')->select();

            //代理商等级
            $agentLevel = db(\tname::agent_level)->order('id asc')->select();
            $this->assign('agentLevel', $agentLevel);
            $this->assign('goods_type', $goods_type);
            $this->assign('categoryList', $categoryList);
            $this->assign('data', $data);
            return $this->fetch();
        }
    }
    /**
     * 上传规格图片
     */
    public  function upload_spec_img(){
        $file_name = input('file_name');
        $path = "uploads/spec";
        $image = base64_image_content($file_name, $path);
        if(empty($image)){
            return array('status'=>0,'msg'=>'上传失败','data'=>[]);
        }
        return array('status'=>1,'msg'=>'上传成功','data'=>['img'=>$image]);
    }


    /**
     * 后置操作方法
     * 自定义的一个函数 用于数据保存后做的相应处理操作, 使用时手动调用
     * @param int $goods_id 商品id
     */
    public function afterSave($goods_id)
    {
        // 商品规格价钱处理
        $item_data = input('post.');
        $goods_item = isset($item_data['item']) ? $item_data['item'] : array();
        $eidt_goods_id = $item_data['id'];
        if ($goods_item) {
            $keyArr = '';//规格key数组
            foreach ($goods_item as $k => $v) {
                $keyArr .= $k . ',';
                // 批量添加数据
                $v['price'] = trim($v['price']);
                $v['store_count'] = trim($v['store_count']); // 记录商品总库存
                $data = [
                    'goods_id' => $goods_id,
                    'key' => $k,
                    'key_name' => $v['key_name'],
                    'price' => $v['price'],
                    'store_count' => $v['store_count'],
                    'market_price' => $v['market_price'],
                    'spec_img'=>$v['spec_img'],
                ];
                $specGoodsPrice = db(\tname::goods_spec_price)->where(['goods_id' => $data['goods_id'], 'key' => $data['key']])->find();

                if ($specGoodsPrice) {
                    db(\tname::goods_spec_price)->where(['goods_id' => $goods_id, 'key' => $k])->update($data);
                } else {
                    db(\tname::goods_spec_price)->insert($data);
                }
            }
            if ($keyArr) {
                db(\tname::goods_spec_price)->where('goods_id', $goods_id)->whereNotIn('key', $keyArr)->delete();
            }
        } else {
            db(\tname::goods_spec_price)->where(['goods_id' => $goods_id])->delete();
        }
    }


    /**
     *获取商品模型对应的规格
     */
    public function get_goods_spec()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $where_s = array(
                'type_id' => $data['goods_type'],
            );
            $goods_spec = db(\tname::goods_spec)->where($where_s)->order('sort desc')->select();
            foreach ($goods_spec as $k => $v) {
                $where_si = array(
                    'spec_id' => $v['id'],
                );
                $goods_spec[$k]['spec_item'] = db(\tname::goods_spec_item)->where($where_si)->order('id')->column('id,item');
//                dump($goods_spec[$k]['spec_item']);
//                die;
            }

            $items_id = db(\tname::goods_spec_price)->where(array('goods_id' => $data['goods_id']))->value("GROUP_CONCAT(`key` SEPARATOR '_') AS items_id");
//            echo db(\tname::goods_spec_price)->getLastSql();
//            echo  $items_id;
            $items_ids = explode("_", $items_id);
            $items_ids = implode(",", $items_ids);
//            dump($items_ids);

//            dump($goods_spec);
            $this->assign('goods_spec', $goods_spec);
            $this->assign('items_ids', $items_ids);
            $html = $this->fetch('get_goods_spec');
            return ajaxSuccess($html);
        }
    }

    /**
     * 动态获取商品规格输入框 根据不同的数据返回不同的输入框
     */
    public function get_spec_input()
    {
        $data = input('post.');

        $goods_id = $data['goods_id'] ? $data['goods_id'] : 0;
        $spec_arr = isset($data['spec_arr']) ? $data['spec_arr'] : [[]];
        $str = $this->getSpecInput($goods_id, $spec_arr);
//        echo $str;
        return ajaxSuccess($str);
    }


    /**
     * 获取 规格的 笛卡尔积
     * @param $goods_id 商品 id
     * @param $spec_arr 笛卡尔积
     * @return string 返回表格字符串
     */
    public function getSpecInput($goods_id, $spec_arr)
    {
        // <input name="item[2_4_7][price]" value="100" /><input name="item[2_4_7][name]" value="蓝色_S_长袖" />
        /*$spec_arr = array(
            20 => array('7','8','9'),
            10=>array('1','2'),
            1 => array('3','4'),

        );  */
        // 排序
        foreach ($spec_arr as $k => $v) {
            $spec_arr_sort[$k] = count($v);
        }
        asort($spec_arr_sort);
        foreach ($spec_arr_sort as $key => $val) {
            $spec_arr2[$key] = $spec_arr[$key];
        }


        $clo_name = array_keys($spec_arr2);
        $spec_arr2 = combineDika($spec_arr2); //  获取 规格的 笛卡尔积

        $spec = db(\tname::goods_spec)->column('id,name'); // 规格表
//        dump($spec);
        $specItem = db(\tname::goods_spec_item)->column('id,item,spec_id');//规格项
        $keySpecGoodsPrice = db(\tname::goods_spec_price)->where('goods_id = ' . $goods_id)->column('key,key_name,spec_img,price,store_count,bar_code,sku,market_price,cost_price,commission');//规格项

        $str = "<label class='control-label col-lg-2'>规格信息</label>";
        $str .= "<div class='col-lg-10'>";
        $str .= "<table class='table table-striped'>";
        $str .= "<thead>";
        $str_fill = "<tr>";
        // 显示第一行的数据
        foreach ($clo_name as $k => $v) {
            $str .= " <th>{$spec[+1]}</th>";
        }
        $str .= "<th>购买价</th>
                    <th>市场价</th>
                    <th>库存</th>
                    <th>图片</th>
                </tr>";
        $str .= "</thead>";
        $str .= "<tbody>";

//        dump($spec_arr2);

        // 显示第二行开始
        foreach ($spec_arr2 as $k => $v) {
            $str .= "<tr>";
            $item_key_name = array();
            foreach ($v as $k2 => $v2) {
                $str .= "<td>{$specItem[$v2]['item']}</td>";
                $item_key_name[$v2] = $spec[$specItem[$v2]['spec_id']] . ':' . $specItem[$v2]['item'];
            }
            ksort($item_key_name);
            $item_key = implode('_', array_keys($item_key_name));
            $item_name = implode(' ', $item_key_name);

            isset($keySpecGoodsPrice[$item_key]['price']) ? false : $keySpecGoodsPrice[$item_key]['price'] = 0; // 价格默认为0
            isset($keySpecGoodsPrice[$item_key]['store_count']) ? false : $keySpecGoodsPrice[$item_key]['store_count'] = 0; //库存默认为0
            isset($keySpecGoodsPrice[$item_key]['market_price']) ? false : $keySpecGoodsPrice[$item_key]['market_price'] = 0; //市场价默认为0
            isset($keySpecGoodsPrice[$item_key]['cost_price']) ? false : $keySpecGoodsPrice[$item_key]['cost_price'] = 0; //成本价默认为0
            isset($keySpecGoodsPrice[$item_key]['commission']) ? false : $keySpecGoodsPrice[$item_key]['commission'] = 0; //佣金默认为0
            if(isset($keySpecGoodsPrice[$item_key]['spec_img'])){
                $spec_img =  $keySpecGoodsPrice[$item_key]['spec_img'];
            }else{
                $spec_img = "";
            }
            isset($keySpecGoodsPrice[$item_key]['spec_img']) ? false : $keySpecGoodsPrice[$item_key]['spec_img'] = '/static/member/images/uploadimg.png'; //图片为上传图标
            $str .= "<td><input type='hidden' name='item[$item_key][key_name]' value='$item_name' /><input name='item[$item_key][price]' value='{$keySpecGoodsPrice[$item_key]['price']}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")' /></td>";
            $str .= "<td><input name='item[$item_key][market_price]' value='{$keySpecGoodsPrice[$item_key]['market_price']}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")' /></td>";
            $str .= "<td><input name='item[$item_key][store_count]' value='{$keySpecGoodsPrice[$item_key]['store_count']}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")'/></td>";
            $str .= "<td><a href='javascript:;' class='spec_img'><label for='up_file_{$item_key}'><img src='{$keySpecGoodsPrice[$item_key]['spec_img']}' alt=''></label><input type='hidden' class='spec_img_input' name='item[$item_key][spec_img]' value='{$spec_img}'><input type='file' class='up_file' id='up_file_{$item_key}' onchange='upload_spec_img(this)'></a></td>";
            $str .= "</tr>";
        }
        $str .= "</tbody>";
        $str .= "</table>";
        $str .= "</div>";
        return $str;
    }

    //订单列表 2017-10-15
    public function order()
    {
        $order_source = Config::get('order_source');
        $pay_type = Config::get('pay_type');
        $this->assign('order_source', $order_source);
        $this->assign('pay_type', $pay_type);
        if (request()->isPost()) {
            $search = input('post.');
            $where = array(
                'a.uid' => UID,
                'a.is_hidden' => 0
            );
            $where['status'] = array('gt', -2);

            if ($search['stime'] && $search['etime']) {
                $where['a.create_time'] = array('between time', [$search['stime'], $search['etime']]);
            }

            if ($search['keyword'] !== null && $search['keyword'] !== '') {
                $where['v,nickname|a.order_number|a.express_name|a.express_number|a.linkman|a.linktel|a.address'] = array('like', '%' . $search['keyword'] . '%');
            }
            if ($search['status'] !== null && $search['status'] !== '') {
                $where['a.status'] = $search['status'];
            }
            session('where', $where);
            $dataList = db(\tname::mall_order)->alias('a')
                ->join(\tname::vip . ' v', 'a.vip_id=v.id', 'left')
                ->field('a.*,v.nickname nickname')
                ->where($where)->order('a.id desc')->paginate(30, false, array('page' => $search['page']));

//            dump($dataList);

            //配送方式
            $expressList = db(\tname::express)->order('id')->column('id,express_name');
            //订单来源
            $this->assign('dataList', $dataList);
            $this->assign('expressList', $expressList);
            $html = $this->fetch('mall/form');
            $attach = array(
                'page' => $dataList->render()
            );
            return ajaxSuccess($html, '', '', $attach);
        }

        return $this->fetch();
    }

    //修改订单    2017-10-15
    public function orderdetail()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;
            $order = db(\tname::mall_order)->where(array('id' => $data['id']))->find();
            if ($data['status'] == 2 && $order['status'] != 2) {
                if ($data['express_id'] <= 0) {
                    return ajaxFalse('请选择快递公司');
                }
                if (!$data['express_number']) {
                    return ajaxFalse('请填写快递单号');
                }

            }

            $res = dataUpdate(\tname::mall_order, $data);
            if (!$res) {
                return ajaxFalse();
            }
            if ($data['status'] == 2 && $order['status'] != 2) {
                //订单操作记录
                orderActionLog($order['id'], '', '订单发货', $order['status'], 0);
            }


            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::mall_order)->alias('a')
                ->join(\tname::vip . ' v', 'a.vip_id=v.id', 'left')
                ->field('a.*,v.nickname nickname')
                ->find($id);

            if ($id) {
                $data['orderlog'] = db(\tname::mall_orderlog)->where(array('order_id' => $id))->select();
                foreach ($data['orderlog'] as $k => &$v) {
                    $v['product'] = serializeMysql($v['product'], 1);
                }
            }

            $orderactionlog = db(\tname::mall_orderact)->where(array('order_id' => $id))->alias('a')
                ->join(\tname::vip . ' v', 'a.action_user=v.id', 'left')
                ->field('a.*,v.nickname')
                ->order('id desc')
                ->select();

            //配送方式
            $expressList = db(\tname::express)->order('id')->select();
            $this->assign('data', $data);
            $this->assign('expressList', $expressList);
            $this->assign('orderactionlog', $orderactionlog);
            return $this->fetch();
        }
    }


    //申请退款订单列表 2018-11-14
    public function order_refund()
    {
        error_reporting(E_ERROR | E_PARSE);
        $order_source = Config::get('order_source');
        $pay_type = Config::get('pay_type');
        $this->assign('order_source', $order_source);
        $this->assign('pay_type', $pay_type);
        if (request()->isPost()) {
            $search = input('post.');
            $where = array(
                'a.uid' => UID,
                'a.is_hidden' => 0
            );
            $where['status'] = array('lt', -1);

            if ($search['stime'] && $search['etime']) {
                $where['a.create_time'] = array('between time', [$search['stime'], $search['etime']]);
            }

            if ($search['keyword'] !== null && $search['keyword'] !== '') {
                $where['v,nickname|a.order_number|a.express_name|a.express_number|a.linkman|a.linktel|a.address'] = array('like', '%' . $search['keyword'] . '%');
            }
            if ($search['status'] !== null && $search['status'] !== '') {
                $where['a.status'] = $search['status'];
            }
            session('where', $where);
            $dataList = db(\tname::mall_order)->alias('a')
                ->join(\tname::vip . ' v', 'a.vip_id=v.id', 'left')
                ->field('a.*,v.nickname nickname')
                ->where($where)->order('a.id desc')->paginate(30, false, array('page' => $search['page']));

            //配送方式
            $expressList = db(\tname::express)->order('id')->column('id,express_name');
            //订单来源
            $this->assign('dataList', $dataList);
            $this->assign('expressList', $expressList);;
            $html = $this->fetch('mall/form');
            $attach = array(
                'page' => $dataList->render()
            );

            return ajaxSuccess($html, '', '', $attach);
        }

        return $this->fetch();
    }

    //退款订单详情
    public function order_refund_detail()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;
            $res = dataUpdate(\tname::mall_order, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::mall_order)->alias('a')
                ->join(\tname::vip . ' v', 'a.vip_id=v.id', 'left')
                ->field('a.*,v.nickname nickname')
                ->find($id);

            if ($id) {
                $data['orderlog'] = db(\tname::mall_orderlog)->where(array('order_id' => $id))->select();
                foreach ($data['orderlog'] as $k => &$v) {
                    $v['product'] = serializeMysql($v['product'], 1);
                }
            }

            $orderactionlog = db(\tname::mall_orderact)->where(array('order_id' => $id))->alias('a')
                ->join(\tname::vip . ' v', 'a.action_user=v.id', 'left')
                ->field('a.*,v.nickname')
                ->order('id desc')
                ->select();

            //配送方式
            $expressList = db(\tname::express)->order('id')->select();
            $this->assign('data', $data);
            $this->assign('expressList', $expressList);
            $this->assign('orderactionlog', $orderactionlog);
            return $this->fetch();
        }
    }

    /**
     * 订单操作
     */
    public function order_handle()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;
            $order = db(\tname::mall_order)->where(array('id' => $data['id']))->find();
            if ($order['status'] != -2) {
                return ajaxFalse('该订单不允许执行此操作');
            }
            if ($data['operation'] == 1) {
                if ($order['pay_type'] == 'wxpay') {
                    $vip = db(\tname::vip)->where(array('id' => $order['vip_id']))->find();
//                    $order['pay_money'] = 0.01;
                    $result = wxRefund(UID, $vip['openid'], $order['order_number'], $order['pay_money'], $order['pay_money'], $type = 'applet');
                }
                if ($order['pay_type'] == 'alipay') {
                    $vip = db(\tname::vip)->where(array('id' => $order['vip_id']))->find();
//                    $order['pay_money'] = 0.01;
                    $result = aliRefund($order['trade_no'], $order['pay_money'], $order['pay_money'], $type = 'alipay');
                }
                if ($order['pay_type'] == 'income') {
                    $vip = db(\tname::vip)->where(array('id' => $order['vip_id']))->find();
                    $result = incomeRefund($order, $type = 'alipay');
                }
                if (!$result[0]) {
                    return ajaxFalse("操作失败，[".$result[1]."]");
                }
                $data_up = array(
                    'id' => $data['id'],
                    'status' => -3,
                );
                $res = dataUpdate(\tname::mall_order, $data_up);
                //订单操作记录
                orderActionLog($data['id'], '您提同意订单退款申请', '同意退款', $order['status'], 0);
                if (!$res) {
                    return ajaxFalse();
                }
                return ajaxSuccess();

            }

            //拒绝退款
            if ($data['operation'] == 2) {
                $orderaction = db(\tname::mall_orderact)->where(array('order_id' => $data['id'], 'order_status' => -2))->order('id desc')->find();
                $data_up = array(
                    'id' => $data['id'],
                    'status' => $orderaction['before_status'],
                );
                $res = dataUpdate(\tname::mall_order, $data_up);
                //订单操作记录
                orderActionLog($data['id'], '您提拒绝订单退款申请', '拒绝退款', $order['status'], 0);
            }
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        }
    }


    //订单列表导出    2017-10-15
    public function orderexport()
    {
        $where = session('where');
        $dataList = db(\tname::mall_order)->alias('a')
            ->join(\tname::vip . ' v', 'a.vip_id=v.id', 'left')
            ->field('a.*,v.nickname nickname')
            ->where($where)->order('id desc')->select();

        $order_source = Config::get('order_source');
        $pay_type = Config::get('pay_type');
        //配送方式
        $expressList = db(\tname::express)->order('id')->column('id,express_name');
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
                case -1:
                    $value['status_'] = '已取消';
                    break;
            }
            $order_source_s = empty($value['order_source'])?"其他":$order_source[$value['order_source']];
            $pay_type_s = empty($value['pay_type'])?"其他":$pay_type[$value['pay_type']];
            $express_name = empty($value['express_id'])?"":$expressList[$value['express_id']];
            $data[$key] = array(
                $value['order_number'],//订单编号
                $value['nickname'],//昵称
                $value['linkman'],//联系人
                $value['linktel'],//联系电话
                $value['address'],//地址
                $value['total_number'],//数量
                $value['total_price'],//价钱
                $value['pay_money'],//实际付款
                $value['status_'],//状态
                $order_source_s,
                $pay_type_s,
                $express_name,
                $value['express_number'],//快递单号
                date('Y-m-d H:i:s', $value['create_time']),//时间
                $value['remark'],//备注
            );
        };
//        dump($data);
        $fileName = "订单表";
        $headArr = array( "订单编号", "用户昵称","联系人","联系电话",  "收货地址", "商品数量", "订单总额","实付金额","订单状态", "订单来源","支付方式","配送方式", "快递单号", "下单时间","备注");
        $msg = '$val[\'id\']';
        $res = getExcel($fileName, $headArr, $data, $msg);
        return $res;
    }

    //商城评价 2017-10-15
    public function comment()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $where = array(
                'a.is_hidden' => 0
            );

            if ($search['stime'] && $search['etime']) {
                $where['a.create_time'] = array('between time', [$search['stime'], $search['etime']]);
            }

            if ($search['keyword'] !== null && $search['keyword'] !== '') {
                $where['v.nickname|a.content|p.name'] = array('like', '%' . $search['keyword'] . '%');
            }

            $dataList = db(\tname::mall_comment)->alias('a')
                ->join('xa_' . \tname::mall_product . ' p', 'a.product_id = p.id', 'left')
                ->join('xa_' . \tname::vip . ' v', 'a.vip_id = v.id', 'left')
                ->field('a.*,p.name product_name')
                ->where($where)->order('a.id desc')->paginate(30, false, array('page' => $search['page']));

            $this->assign('dataList', $dataList);
            $html = $this->fetch('mall/form');
            $attach = array(
                'page' => $dataList->render()
            );
            return ajaxSuccess($html, '', '', $attach);
        }

        return $this->fetch();
    }

    //修改评价    2017-10-15
    public function commentdetail()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;

            $res = dataUpdate(\tname::mall_comment, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::mall_comment)->find($id);
            if ($id) {
                $data['imgpath'] = serializeMysql($data['imgpath'], 1);
                $data['product'] = db(\tname::mall_product)->find($data['product_id']);
            }

            $this->assign('data', $data);
            return $this->fetch();
        }
    }


    //新增评价    2017-10-15
    public function comment_add()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;

            $res = dataUpdate(\tname::mall_comment, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {

            $where = array(
                'is_hidden' => 0
            );
            $good_list = db(\tname::mall_product)->where($where)->select();

            $this->assign('good_list', $good_list);
            return $this->fetch();
        }
    }


    //门店列表     2017-10-15
    public function store()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $where = array(
                'uid' => UID,
                'is_hidden' => 0
            );
            if ($search['time']) {
                $search['time'] = explode('-', $search['time']);
                $where['create_time'] = array('between time', [$search['time'][0], $search['time'][1]]);
            }
            if ($search['keyword']) {
                $where['name|address|linktel'] = array('like', '%' . $search['keyword'] . '%');
            }
            if ($search['is_publish'] != '') {
                $where['is_publish'] = $search['is_publish'];
            }


            $dataList = db(\tname::mall_store)->where($where)->order('id desc')->paginate(30, false, array('page' => $search['page']));

            $this->assign('dataList', $dataList);
            $html = $this->fetch('mall/form');
            $attach = array(
                'page' => $dataList->render()
            );
            return ajaxSuccess($html, '', '', $attach);
        }
        return $this->fetch();
    }

    //门店添加     2017-10-15
    public function storeadd()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;
            $data['imgpath'] = serializeMysql($data['imgpath']);
            $res = dataUpdate(\tname::mall_store, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::mall_store)->find($id);
            if ($id) {
                $data['imgpath'] = serializeMysql($data['imgpath'], 1);
            }
            $this->assign('data', $data);
            return $this->fetch();
        }
    }


    //品牌列表     2018-08-28
    public function brandlist()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $where = array(
                'is_hidden' => 0
            );
            if ($search['stime'] && $search['etime']) {
                $where['a.create_time'] = array('between time', [$search['stime'], $search['etime']]);
            }
            if ($search['keyword']) {
                $where['name|desc'] = array('like', '%' . $search['keyword'] . '%');
            }

            $dataList = db(\tname::goods_brand)->where($where)->order('sort asc,id desc')->paginate(10, false, array('page' => $search['page']));

            $this->assign('dataList', $dataList);
            $html = $this->fetch('mall/form');
            $attach = array(
                'page' => $dataList->render()
            );
            return ajaxSuccess($html, '', '', $attach);
        }
        return $this->fetch();
    }

    /**
     * 品牌添加  2018-08-28
     * @return array|mixed|string
     */
    public function brand_add()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $res = dataUpdate(\tname::goods_brand, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::goods_brand)->find($id);
            $this->assign('data', $data);
            return $this->fetch();
        }
    }


}