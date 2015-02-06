<?php
/**
 * 订单模块的订单模型类
 */
namespace Home\Model;
use Think\Model;

class OrderModel extends Model
{
    /**
     * 构造函数，继承父类构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取用户订单总数，根据type确定已完成或未完成类别
     * @param $uid
     * @param $type
     *
     * @return bool
     */
    public function getUserOrderNum($uid,$type)
    {
        if(empty($uid))
        {
            return false;
        }

        $cond['isDel'] = 0;
        $cond['uid'] = $uid;
        $cond['confirm'] = 1;

        if(empty($type))
        {
            $cond['pay_info'] = 0;
        }
        else
        {
            $cond['pay_info'] = 1;
        }

        return M('order')->where($cond)->count();
    }

    public function getUserOrderInfoByPage($start,$end,$type,$uid,$num)
    {
        if(empty($uid))
        {
            return false;
        }
        $cond['isDel'] = 0;
        $cond['uid'] = $uid;
        $cond['confirm'] = 1;

        if(empty($type))
        {
            $cond['pay_info'] = 0;
        }
        else
        {
            $cond['pay_info'] = 1;
        }
        if($num > 5)
        {
            if($start+$end > $num)
            {
                $end = $start+$end - $num;
            }
        }
        return M('order')->where($cond)->order('cTime desc')->field('orderId,order_num,total,postId,order_desc,status,pid,goods_num,cTime')->limit($start,$end)->select();
    }

    public function getUserInfoByUid($uid)
    {
        if(empty($uid))
        {
            return false;
        }

        return M('users')->find($uid);
    }

    public function getUserOrderInfoByOrderId($uid,$orderId)
    {
        if(empty($uid) || empty($orderId))
        {
            return false;
        }

        return M('order')->where(array('uid'=>$uid,
                                       'orderId'=>$orderId,
                                       'isDel'=>0
            ))->count();
    }

    public function delOrderInfo($orderId)
    {
        return M('order')->where(array('orderId'=>$orderId))->setField('isDel',1);
    }

    /**
     * 获取商品列表信息
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
                                       'confirm' => 1,
            ))->order('cTime DESC')->field('num,price,size,user_model,pid,hd_id,bd_id')
            ->select();
    }

    public function getGoodsImageModel($hd_id,$bd_id)
    {
        if(empty($hd_id) || empty($bd_id))
        {
            return false;
        }

        $res = M('decorations_model')->where(array('hd_id' => $hd_id,
                                                   'bd_id' => $bd_id,
                                                   'isDel' => 0))->field('murl')
            ->find();
        return $res;

    }

    public function getPostInfo($postId)
    {
        if(empty($postId))
        {
            return false;
        }

        return M('post')->find($postId);
    }

    public function getOrderPostInfo($orderId)
    {
        if(empty($orderId))
        {
            return false;
        }

        return M('post_info')->where(array('orderId'=>$orderId,
                                        ))->order('cTime desc')->field('num_info')->find();
    }
}