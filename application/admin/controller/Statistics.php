<?php
namespace app\admin\controller;

class Statistics extends Member
{
    public function statistics()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $search=$data;
            $start = strtotime($search['stime'] . ' 00:00:00');
            $end = strtotime($search['etime'] . ' 23:59:59');
            $time = [];
            while ($start <= $end) {
                $time[] = date('Y-m-d', $start);
                $start = $start + 24 * 3600;
            }
            $dataList = [];
            $dataList[0]['name'] = STATISTIC_TYPE_LIST[$data['type']].'统计';

            foreach ($time as $key => $value) {
                $where = [
                    'FROM_UNIXTIME(`time`,\'%Y%m%d\')' => date('Ymd', strtotime($value)),
                    'type' => $data['type']
                ];
                $count=db(\tname::data_count)->where($where)->value('count');
//                dump($count);
                $dataList[0]['data'][] = empty($count) ? 0 : $count;
            }
            $this->assign('time', json_encode($time, JSON_UNESCAPED_UNICODE));
            $this->assign('dataList', json_encode($dataList, JSON_UNESCAPED_UNICODE));
            $this->assign('title', '标题');
            $this->assign('subtitle', '副标题');
            $this->assign('ytext', 'y轴显示文字');
            $html = $this->fetch('statistics/form');
            return ajaxSuccess($html);
        }

        $time = array(
            'stime'=>date('Y-m-d',time()-30*86400),
            'etime'=>date('Y-m-d',time()),
        );
        $this->assign('time', $time);
        $this->assign('typeList', STATISTIC_TYPE_LIST);
        return $this->fetch();
    }
}