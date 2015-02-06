<?php
namespace Shop\Model;
use Think\Model;

class OrderModel extends Model
{
    /**
     * 根据类型，获取总数
     * @param string $type
     *
     * @return mixed
     */
    public function getOrderTotal($type="list")
    {
        $cond['isDel'] = '0';
        $cond['confirm'] = '1';
        $cond['pay_info'] = '1';
        if($type == "post" )
        {
            $cond['posted'] = '1';
        }
        if($type == "unPost")
        {
            $cond['posted'] = '0';
        }
        if($type == "return")
        {
            $cond['return_status'] = '1';
        }
        return M('order')->where($cond)->count();
    }



    /**
     * 根据类型，查看不同信息
     * @param        $start
     * @param        $end
     * @param string $type
     *
     * @return mixed
     */
    public function getOrderInfoByPage($start,$end,$type="list")
    {
        $cond['confirm'] = '1';
        $cond['isDel'] = '0';
        $cond['pay_info'] = '1';
        if($type == "unPost")
        {
            $cond['posted'] = '0';
        }
        if($type == "post")
        {
            $cond['posted'] = '1';
        }
        if($type == "return")
        {
            $cond['return_status'] = '1';
        }

        return M('order')->where($cond)->limit($start.','.$end)->field('orderId,order_num,postId,pid,posted,order_desc,cTime,status,status_time,return_status')->select();
    }

    /**
     * 获取收货地址信息
     * @param $postId
     *
     * @return mixed
     */
    public function getPostInfoById($postId)
    {
        return M('post')->where(array('isDel'=>'0'))->find($postId);
    }

    /**
     * @param $orderId
     *
     * @return bool
     */
    public function getPostedNum($orderId)
    {
        if(empty($orderId))
        {
            return false;
        }

        return M('post_info')->where(array('orderId'=>intval($orderId)
            ))->field('num_info,cTime')->order('cTime desc')->find();
    }


    /**
     * 获取回退的商品信息
     * @param $orderId
     *
     * @return bool
     */
    public function getBackGoodsInfo($orderId)
    {
        if(empty($orderId))
        {
            return false;
        }

        return M('return_info')->where(array('orderId'=>$orderId,
                                             'confirm'=> '0',
                                             'isDel'=>'0',
                                              'pay_info'=>1
            ))->field('return_good')->find();
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
            ))->order('cTime DESC')
            ->select();
    }

    /**
     * 获取所有的商品信息
     * @param $pid
     *
     * @return bool
     */
    public function getGoodsList($pid)
    {
        if(empty($pid))
        {
            return false;
        }

        return M('goods')->where(array('pid' => array( 'IN' ,$pid),
                                       'isDel' => 0,
                                       'confirm' => 1,
            ))->order('cTime DESC')
            ->select();
    }

    /**
     * 获取订单信息
     * @param $id
     *
     * @return bool
     */
    public function getOrderInfoById($id,$type = false)
    {
        if(empty($id))
        {
            return false;
        }

        if(!empty($type))
        {
            return M('order')->where(array('order_num'=> trim($id),'confirm'=>'1','isDel'=>'0','pay_info'=>'1'))->find();
        }
        return M('order')->where(array('orderId'=>intval($id),'isDel'=>'0','confirm'=>'1','pay_info'=>1))->find();
    }

    /**
     * 获取用户图片列表
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

        return M('users')->field('user_image')->find(intval($uid));
    }

    /**
     * 获取头饰图片
     * @param $hd_id
     *
     * @return bool
     */
    public function getHdImage($hd_id)
    {
        if(empty($hd_id))
        {
            return false;
        }

        return M('decorations_hd')->field('url')->find(intval($hd_id));
    }

    /**
     * 获取身体图片
     * @param $bd_id
     *
     * @return bool
     */
    public function getBdImage($bd_id)
    {
        if(empty($bd_id))
        {
            return false;
        }

        return M('decorations_bd')->field('url')->find(intval($bd_id));
    }

    public function getOrderInfoByCond($info)
    {
        $cond['isDel'] = '0';
        $cond['confirm'] = '1';
        $cond['pay_info'] = '1';
        $cond['orderId'] = intval($info);
        return M('order')->where($cond)->field('orderId,uid,postId')->find();
    }

    /**
     * 添加快递单号信息
     * @param $data
     *
     * @return bool
     */
    public function setPostNumInfo($data,$type=0,$orderId)
    {
        if(empty($data))
        {
            return false;
        }

        $post_info = M('post_info');

        if(empty($type))
        {
            return $post_info->data($data)->add();
        }
        else
        {
            return M('post_info')->where(array('orderId' => intval($orderId)))->data($data)->save();
        }
    }

    /**
     * 更新订单的发送状态
     * @param $id
     *
     * @return bool
     */
    public function updateOrderPostInfo($id)
    {
        if(empty($id))
        {
            return false;
        }

        $data['posted'] = 1;
        $data['status'] = 4;
        $data['status_time'] = time();
        return M('order')->where(array(
                'orderId' => $id,
                'isDel'=> 0,
                'confirm'=> 1,
                'pay_info'=>1
            ))->data($data)->save();
    }

    /**
     * 根据分类查询订单信息
     * @param $info
     * @param $type
     *
     * @return bool
     */
    public function getSearchOrder($info,$type,$start,$end)
    {
        $cond['isDel'] = '0';
        $cond['confirm'] = '1';
        $cond['pay_info'] = '1';
        switch($type)
        {
            case 0 : $cond['orderId'] = intval($info); break;
            case 1 : $cond['status'] = 1; break;
            case 2 : $cond['status'] = 2; break;
            case 3 : $cond['status'] = 8; break;
            case 4 : $cond['posted'] = 1; break;
            case 9 : $cond['order_num'] = array('like','%'.$info.'%'); break;
        }
        return M('order')->where($cond)->limit($start.','.$end)->field('orderId,order_num,postId,pid,posted,order_desc,cTime,status,status_time,return_status')->select();
    }

    /**
     * 获取搜索的结果总数
     * @param $info
     * @param $type
     *
     * @return bool
     */
    public function getSearchOrderTotal($info,$type)
    {
        if(empty($type))
        {
            return false;
        }
        $cond['isDel'] = '0';
        $cond['confirm'] = '1';
        $cond['pay_info'] = '1';
        if($type == "id")
        {
            $cond['order_num'] = array('like','%'.$info.'%');
        }
        if($type == "num")
        {
            $cond['orderId'] = intval($info);
        }
        if($type == "name")
        {
            $order = M('order');
            $condEx['dob_post.pName'] = array('like','%'.$info.'%');
            $condEx['dob_order.isDel'] = '0';
            $condEx['dob_order.confirm'] = '1';
            $condEx['dob_order.pay_info'] = '1';
            return $order->join('dob_post ON dob_order.postId = dob_post.postId')->where($condEx)->count();
        }
        return M('order')->where($cond)->count();
    }

    /**
     * 获取退货信息
     * @param $id
     *
     * @return bool
     */
    public function getBackInfo($id)
    {
        if(empty($id))
        {
            return false;
        }

        return M('return_info')->where(array('isDel'=>0,'orderId'=>$id))->find();
    }

    /**
     * 更新退货信息，完成退货操作
     * @param $bid
     * @param $data
     *
     * @return bool
     */
    public function updateReturnOrderInfo($bid,$data,$orderId,$return_type)
    {
        if(empty($bid) || empty($orderId))
        {
            return false;
        }
        $cond['orderId'] = $orderId;
        $cond['confirm'] = 1;
        $cond['pay_info'] = 1;
        //开启事务
        $this->startTrans();
        //根据退回的类型更新订单的状态
        if(empty($return_type))
        {
            $res = M('order')->where($cond)->setField('status',5);
        }
        else
        {
            $res = M('order')->where($cond)->setField('status',6);
        }

        //如果是所有的商品都已经退换完成则更新退换状态为完结状态1
        if(empty($data['return_good']))
        {
            $data['return_good'] = null;
            $data['confirm'] = 1;
            $re_res = M('return_info')->where(array('rid'=>$bid,
                                                 'confirm'=>0,
                                                 'isDel'=>0
                ))->data($data)->save();
        }
        //如果只是部分退货则只是去掉退回的货物的信息
        else
        {
            $re_res = M('return_info')->where(array('rid'=>$bid,
                                                 'confirm'=>0,
                                                 'isDel'=>0
                ))->data($data)->save();
        }
        //如果2者都执行成功则返回成功
        if(empty($res) || empty($re_res))
        {
            $this->commit();
            return true;
        }
        else
        {
            $this->rollback();
            return false;
        }

    }

    /**
     * 获取订单状态
     * @param $id
     *
     * @return bool
     */
    public function getOrderStatus($id)
    {
        if(empty($id))
        {
            return false;
        }

        return M('order')->where(array(
                                'orderId'=>$id,
                                'confirm'=>1,
                                'pay_info'=>1,
                                'isDel'=>0
            ))->field('status')->find();
    }

    /**
     * 更新订单状态
     * @param $status
     * @param $orderId
     *
     * @return bool
     */
    public function updateOrderStatus($status,$orderId)
    {
        if(empty($orderId))
        {
            return false;
        }
        $data['status'] = intval($status);
        $data['status_time'] = time();

        return M('order')->where(array('orderId'=>intval($orderId),
                                       'confirm'=>1,
                                       'pay_info'=>1,
                                       'isDel'=>0
            ))->data($data)->save();
    }

    public function userMobile($uid)
    {
        if(empty($uid))
        {
            return false;
        }

        return M('user')->where(array(
                'uid'=>intval($uid),
                'isDel'=>0
            ))->field('mobile')->find();
    }

}