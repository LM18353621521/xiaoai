<?php
namespace app\admin\controller;

class News extends Member
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

    //新闻列表    2017-10-15
    public function news()
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

            if ($search['keyword']) {
                $where['a.title|c.name'] = ['like', '%' . $search['keyword'] . '%'];
            }
            if ($search['is_publish'] != '') {
                $where['a.is_publish'] = $search['is_publish'];
            }

            $dataList = db(\tname::news)
                ->alias('a')
                ->join('xa_' . \tname::news_category . ' c', 'a.category_id = c.id', 'left')
                ->field('a.*,c.name category_name')
                ->where($where)->order('id desc')->paginate(50, false, ['page' => $search['page']]);

            $this->assign('dataList', $dataList);

            $html = $this->fetch('news/form');
            $attach =[
                'page' => $dataList->render()
            ];
            return ajaxSuccess($html, '', '', $attach);
        }
        return $this->fetch();
    }

    //添加新闻    2017-10-15
    public function newsadd()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['uid'] = UID;
            if(!empty($data['imgpath'])){
                $data['imgpath'] = serializeMysql($data['imgpath']);
            }else{
                $data['imgpath'] = '';
            }
            $res = dataUpdate(\tname::news, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::news)->find($id);

            if ($id) {
                $data['imgpath'] = serializeMysql($data['imgpath'], 1);
            }

            //新闻分类
            $where = [
                'uid' => UID,
                'is_hidden' => 0
            ];
            $categoryList = db(\tname::news_category)->where($where)->order('sort')->select();

            $this->assign('categoryList', $categoryList);
            $this->assign('data', $data);
            return $this->fetch();
        }
    }

    //专题列表
    public function topic()
    {
        if (request()->isPost()) {
            $search = input('post.');
            $where = [
                'is_hidden'=>0,
            ];

            if($search['time']){
                $search['time'] = explode('-',$search['time']);
                $where['a.create_time'] = ['between time', [$search['time'][0], $search['time'][1]]];
            }

            if ($search['keyword']) {
                $where['a.title|c.name'] = ['like', '%' . $search['keyword'] . '%'];
            }

            $dataList = db(\tname::topic)
                ->where($where)->order('id desc')->paginate(30, false, ['page' => $search['page']]);
            $this->assign('dataList', $dataList);

            $html = $this->fetch('news/form');
            $attach =[
                'page' => $dataList->render()
            ];
            return ajaxSuccess($html, '', '', $attach);
        }
        return $this->fetch();
    }

    //添加专题
    public function topicadd()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $res = dataUpdate(\tname::topic, $data);
            if (!$res) {
                return ajaxFalse();
            }
            return ajaxSuccess();
        } else {
            $id = input('param.id', 0);
            $data = db(\tname::topic)->find($id);
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