<?php
namespace Home\Model;
use Think\Model;

class PaymentModel extends Model
{

    /**
     * 构造函数，继承父类的构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 订单号获取订单信息
     * @param $num
     *
     * @return bool
     */
    public function getOrderInfo($num)
    {
        if(empty($num))
        {
            return false;
        }

        return M('order')->where(array('order_num'=>$num,
                                       'isDel'=>0,
                                       'confirm'=>1,
                                       'pay_info'=>0,
            ))->find();
    }

    /**
     * 订单编号获取订单信息
     * @param $id
     *
     * @return bool
     */
    public function getOrderInfoById($id)
    {
        if(empty($id))
        {
            return false;
        }

        return M('order')->where(array('orderId'=>$id,
                                       'isDel'=>0,
                                       'confirm'=>1,
                                       'pay_info'=>0,
            ))->find();
    }

    public function updateOrderPayInfo($id,$pay_num,$type=0)
    {
        if(empty($id) || empty($pay_num))
        {
            return false;
        }
        if(empty($type))
        {
            $data['pay_info'] = 1;
            $data['pay_time'] = time();
        }
        $data['pay_num'] = $pay_num;

        return M('order')->where(array('orderId'=>intval($id),
                                       'isDel'=>0,
                                       'confirm'=>1,
                                       'pay_info'=>0,))->data($data)->save();
    }

    public function updateOrderPayStatus($id,$status)
    {
        if(empty($id) || empty($status))
        {
            return false;
        }
        $data['pay_info'] = 1;
        $data['pay_time'] = time();
        return M('order')->where(array('orderId'=>intval($id),
                                       'isDel'=>0,
                                       'confirm'=>1,
                                       'pay_info'=>0,))->data($data)->save();
    }
}