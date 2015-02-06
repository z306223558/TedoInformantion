<?php
/**
 * 应用娱乐模块的模型
 */
namespace Home\Model;
use Think\Model;

class YuleModel extends Model
{
    /**
     * 构造函数，继承父类构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function getYuleCount()
    {
        return M('yule')->count();
    }

    public function getYuleInfoByPage($start,$end)
    {
        $cond['isDel'] = '0';
        return M('yule')->where($cond)->limit($start.','.$end)->field('id,type,title,logo,size,short_desc,download,content,image')->order('sort desc','updateTime desc','cTime desc')->select();
    }

    public function getTypeInfoById($type)
    {
        if(empty($type))
        {
            return false;
        }

        return M('yule_type')->find(intval($type));
    }
}