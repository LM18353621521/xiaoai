<?php
namespace app\wechat\controller;

class News extends Wechat
{
    //新闻列表    2017-10-15
    public function index()
    {
        if (request()->isPost()) {
            $pdata = input('post.');
            $where_n = array(
                'uid' => WID,
                'category_id' => $pdata['category_id'],
                'is_hidden' => 0,
                'is_publish' => 1
            );
            $count = db(\tname::news)->where($where_n)->count();
            $dataList = db(\tname::news)->where($where_n)->order('id desc')->page($pdata['page'], 10)->select();
            foreach ($dataList as $key => &$value) {
                $value['imgpath'] = serializeMysql($value['imgpath'], 1);
            }
            $attach = array(
                'total' => $count
            );

            $this->assign('dataList', $dataList);
            $html = $this->fetch('news/ajaxdata');
            return ajaxSuccess($html, '', '', $attach);
        }

        $categoryList = db(\tname::news_category)->where(array('is_hidden' => 0))->order('sort asc')->select();

        $this->assign('categoryList', $categoryList);
        return $this->fetch();
    }

    //详情页    2017-10-15
    public function detail()
    {
        $userinfo = session('userinfo');
        $id = input('param.id', 0);

        $data = array(
            'id' => $id,
            'browse_actual' => array('exp', "`browse_actual`+1")
        );
        dataUpdate(\tname::news, $data);
        $news = db(\tname::news)->find($id);

        $this->assign('news', $news);
        return $this->fetch();
    }

    //评论列表   2017-10-15
    public function reviewlist()
    {
        $userinfo = session('userinfo');
        $pdata = input('post.');

        $where_r = array(
            'news_id' => $pdata['news_id'],
            'status' => 1,
            'is_hidden' => 0
        );
        $count = db(\tname::news_review)->where($where_r)->count();
        $dataList = db(\tname::news_review)->alias('a')
            ->join('wechat_' . \tname::vip . ' v', 'a.openid = v.openid', 'left')
            ->field('a.*,v.headimgurl headimgurl,v.nickname nickname')
            ->where($where_r)->order('id desc')->select();
        foreach ($dataList as $key => &$value) {
            $map[$key] = array(
                'uid' => WID,
                'openid' => $userinfo['openid'],
                'classify' => 'news_review',
                'type' => 'thumb',
                'info_id' => $value['id']
            );
            $hasinteractive = db(\tname::data_interactive)->where($map[$key])->find();
            empty($hasinteractive) ?  $value['has_thumb'] = 0 : $value['has_thumb'] = 1;

            $map0[$key] = array(
                'uid' => WID,
                'classify' => 'news_review',
                'type' => 'thumb',
                'info_id' => $value['id']
            );
            $value['thumb'] = db(\tname::data_interactive)->where($map0[$key])->count();
        }
        $attach = array(
            'total' => $count
        );
        $this->assign('dataList', $dataList);
        $html = $this->fetch('news/ajaxdata');
        return ajaxSuccess($html, '', '', $attach);
    }

    //评论   2017-10-15
    public function review()
    {
        $userinfo = session('userinfo');
        $pdata = input('post.');

        $vip = db(\tname::vip)->where(array('openid' => $userinfo['openid']))->find();
        $pdata['uid'] = WID;
        $pdata['openid'] = $userinfo['openid'];
        $pdata['nickname'] = $userinfo['nickname'];
        $res = dataUpdate(\tname::news_review, $pdata);
        if (!$res) {
            return ajaxFalse();
        }
        return ajaxSuccess();
    }

    //点赞  2017-10-15
    public function thumb()
    {
        $userinfo = session('userinfo');
        $review_id = input('post.review_id');

        $res = dataInteractive(WID, $userinfo['openid'], 'news_review', 'thumb', $review_id);
        if (!$res) {
            return ajaxFalse();
        }
        return ajaxSuccess();
    }
}
