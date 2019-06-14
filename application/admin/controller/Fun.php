<?php
namespace app\admin\controller;

class Fun extends Member
{
    // 新闻分类     2017-10-15
    public function category()
    {
        if (request()->isPost()) {
            $where = [
                'uid' => UID,
                'is_hidden' => 0
            ];
            $dataList = db(\tname::news_category)->where($where)->order('sort')->paginate(500);

            $this->assign('dataList', $dataList);
            $html = $this->fetch('news/form');

            return ajaxSuccess($html);
        }
        return $this->fetch();
    }

    //添加新闻分类     2017-10-15
    public function categoryadd()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;

            $res = dataUpdate(\tname::news_category, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::news_category)->find($id);

            $this->assign('data', $data);
            return $this->fetch();
        }
    }

    //开心一刻列表    2018-08-29
    public function funlist()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $where = [
                'a.uid' => UID,
                'a.is_hidden' => 0
            ];

            if ($search['stime']&&$search['etime']) {
                $where['a.create_time'] = array('between time', [$search['stime'], $search['etime']]);
            }

            if ($search['keyword']) {
                $where['a.title'] = ['like', '%' . $search['keyword'] . '%'];
            }
            if ($search['is_publish'] != '') {
                $where['a.is_publish'] = $search['is_publish'];
            }
            if ($search['type'] != '') {
                $where['a.type'] = $search['type'];
            }

            $dataList = db(\tname::fun_moment)
                ->alias('a')
                ->field('a.* ')
                ->where($where)->order('sort asc,id desc')->paginate(25, false, ['page' => $search['page']]);

            $this->assign('dataList', $dataList);

            $html = $this->fetch('fun/form');
            $attach =[
                'page' => $dataList->render()
            ];
            return ajaxSuccess($html, '', '', $attach);
        }
        return $this->fetch();
    }

    /**
     * 添加开心一刻素材 2018-08-29
     * @return array|mixed|string
     */
    public function fun_add()
    {
        if (request()->isPost()) {
            $data = input('post.');

            $data['uid'] = UID;
            $res = dataUpdate(\tname::fun_moment, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::fun_moment)->find($id);

            //类型
            $categoryList = array('video'=>'视频','image'=>'图片','text'=>'文字');
            $this->assign('categoryList', $categoryList);
            $this->assign('data', $data);
            return $this->fetch();
        }
    }

    //新闻评价 2017-10-15
    public function review()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $where = [
                'a.uid' => UID,
                'a.is_hidden' => 0
            ];
            if($search['time']){
                $search['time'] = explode('-',$search['time']);
                $where['a.create_time'] = ['between time', [$search['time'][0], $search['time'][1]]];
            }
            if ($search['keyword'] !== null && $search['keyword'] !== '') {
                $where['n.title|a.content|v.nickname'] = ['like', '%' . $search['keyword'] . '%'];
            }

            $dataList = db(\tname::news_review)->alias('a')
                ->join('xa_' . \tname::news . ' n', 'a.news_id = n.id', 'left')
                ->join('xa_' . \tname::vip . ' v', 'a.openid = v.openid', 'left')
                ->field('a.*,n.title news_title,v.nickname nickname')
                ->where($where)->order('id desc')->paginate(50, false, ['page' => $search['page']]);

            $this->assign('dataList', $dataList);
            $html = $this->fetch('news/form');
            $attach = [
                'page' => $dataList->render()
            ];
            return ajaxSuccess($html, '', '', $attach);
        }

        return $this->fetch();
    }

}