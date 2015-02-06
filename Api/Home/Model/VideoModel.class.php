<?php
/**
 * 视频模块的模型
 */
namespace Home\Model;
use Think\Model;

class VideoModel extends Model
{
    /**
     * 构造函数，继承父类构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取视频总数
     * @return mixed
     */
    public function getVideoTotal()
    {
        return M('video')->where(array(
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
    public function getVideoInfoByPage($start,$end)
    {
        $cond['isDel'] = '0';
        return M('video')->where($cond)->limit($start.','.$end)->field('vid,image,filename,content,top')->order('top desc','tTime desc','cTime desc')->select();
    }


}