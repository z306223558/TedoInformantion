<?php
namespace Home\Model;
use Think\Model;

class TrolleyModel extends Model
{

    /**
     * 构造函数，继承父类的构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 生成商品信息，返回新生产的商品pid
     * @param $data
     *
     * @return bool
     */
    public function setGoodsInfo($data)
    {
        if(empty($data))
        {
            return false;
        }

        return M('goods')->data($data)->add();
    }

    /**
     * 用户购物车商品数量
     * @param $uid
     *
     * @return bool
     */
    public function getTrolleyCount($uid)
    {
        if(empty($uid))
        {
            return false;
        }

        return M('goods')->where(array(
                'uid'=> intval($uid),
                'isDel' => 0,
                'confirm' => 0
            ))->count();
    }

    /**
     * 根据uid查询选择了但是没有下单的商品
     * @param $uid
     *
     * @return bool
     */
    public function getGoodsListByUid($uid)
    {
        if(empty($uid))
        {
            return false;
        }

        return M('goods')->where(array('confirm' => 0,
                                       'uid'       => $uid,
                                       'isDel'     => 0
                       ))->field('pid,hd_id,bd_id,price,num,size')
                         ->order('cTime DESC')
                         ->select();
    }

    public function getGoodsImage($hd_id,$bd_id)
    {
        if(empty($bd_id) || empty($hd_id))
        {
            return false;
        }

        return M('decorations_model')->where(array('hd_id' => $hd_id,
                                                   'bd_id' => $bd_id,
                                                   'isDel' => 0
            ))->field('murl')
            ->find();
    }

    /**
     * 根据uid来获取收货地址信息
     * @param $uid
     *
     * @return bool
     */
    public function getPostDefaultByUid($uid)
    {
        if(empty($uid))
        {
            return false;
        }

        return M('post')->where(array('uid' => $uid,
                                      'isDefault'=>1,
                                      'isDel' => 0
            ))->field('postId,pName,province,city,zone,addr')
              ->find();
    }

    /**
     * 根据pid数字获取多条商品的信息
     * @param $pid
     *
     * @return bool
     */
    public function getGoodsListByPids($pid)
    {
        if(empty($pid))
        {
            return false;
        }

        return M('goods')->where(array('pid' => array( 'IN' ,$pid),
                                       'isDel' => 0,
                                       'confirm' => 0
                       )) ->order('cTime DESC')
                         ->select();
    }

    /**
     * 根据uid获取用户图片信息
     * @param $uid
     *
     * @return bool
     */
    public function getUserImageList($uid)
    {
        if(empty($uid))
        {
            return false;
        }
        return M('users')->where(array('uid'=>intval($uid)))->field('user_image')->find();
    }

    public function getGoodsInfoById($pid)
    {
        if(empty($pid))
        {
            return false;
        }

        return M('goods')->where(array('pid' => array( 'IN' ,$pid),
                                       'isDel' => 0,
                                       'confirm' => 0,
            ))->field('pid')->select();
    }

    public function delTrolleyGoods($pid)
    {
        if(empty($pid))
        {
            return false;
        }

        return M('goods')->where(array(
                                    'pid'=>intval($pid),
                                    'isDel'=>0,
                                    'confirm'=>0
            ))->setField('isDel',1);
    }
}