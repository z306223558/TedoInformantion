<?php
/**
 * 公告新闻模块的模型
 */
namespace Home\Model;
use Think\Model;

class NewsModel extends Model
{
    /**
     * 构造函数，继承父类构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取新闻总数
     * @return mixed
     */
    public function getNewsTotal()
    {
        return M('news')->where(array(
                'isDel'=>0
            ))->count('vid');
    }

    /**
     * 分页加载模块
     * @param $start
     * @param $end
     *
     * @return mixed
     */
    public function getNewsInfoByPage($start,$end)
    {
        $cond['isDel'] = '0';
        return M('news')->where($cond)->limit($start.','.$end)->field('nid,image,url,content,top')->order(array('top'=>'desc',
                                                                                                                'tTime'=>'desc'))->select();
    }
}