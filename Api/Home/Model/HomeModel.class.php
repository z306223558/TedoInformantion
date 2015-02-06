<?php
/**
 * 主界面模块主界面模型类
 */
namespace Home\Model;
use Think\Model;

class HomeModel extends Model
{

    /**
     * 构造函数，继承父类的构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 根据类型来查询头部饰物的总数和下身饰物的总数
     * @param int $type
     *
     * @return mixed
     */
    public function getDecorationsNum($type,$t = 0)
    {
        if(empty($t))
        {
            return M('decorations_hd')->where(array('isDel'=>0,
                                                    'type'=>$type))->count();
        }
        else
        {
            return M('decorations_bd')->where(array('isDel'=>0,
                                                    'type'=>$type))->count();
        }
    }

    /**
     * 根据类型来返回头部饰物和下身饰物的列表信息
     * @param int $type
     *
     * @return mixed
     */
    public function getDecorationsList($type,$t = 0)
    {
        if(empty($t))
        {
            return M('decorations_hd')->where(array('isDel'=>0,
                                                     'type'=>$type))->field('hd_id,url,cTime')->select();
        }
        else
        {
            return M('decorations_bd')->where(array('isDel'=>0,
                                                     'type'=>$type))->field('bd_id,url,cTime')->select();
        }
    }

    public function setFeedBackInfo($content,$uid,$type,$info)
    {
        if(empty($content))
        {
            return false;
        }
        $data['content'] = $content;
        $data['uid'] = empty($uid) ? 0 : $uid;
        $data['type'] = $type;
        $data['info'] = htmlspecialchars($info);
        $data['cTime'] = time();
        return M('feedback')->data($data)->add();
    }

    public function addDownloadNum($type)
    {
        $num = M('download_num')->where(array('type' => $type))->find();
        return M('download_num')->where(array('type' => $type))->setField('num',$num['num']+1);
    }

    public function getShopList()
    {
        return M('shop')->where(array('isDel'=>0))->field('shop_image,title,top,updateTime,type,size')->order('sort desc','updateTime desc','cTime desc')->select();
    }
//
//    public function getDecorationsTypeList($id)
//    {
//        return M('disguise_type')->find($id);
//    }
}