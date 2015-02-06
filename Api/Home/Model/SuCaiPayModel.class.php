<?php
/**
 *模型类
 */
namespace Home\Model;
use Think\Model;

class SuCaiPayModel extends Model
{

    /**
     * 构造函数，继承父类的构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function getSuCaiInfoByDevice($device,$sucai)
    {
        return M('sucai_pay')->where(array('device_info'=>$device,
                                           'sucai_id'=>$sucai))->field('sucai_id,payTime')->order('payTime desc')->find();
    }

    public function setPayInfo($device,$sucai,$type=0)
    {
        $data['device_info'] = $device;
        $data['sucai_id'] = $sucai;
        $data['payTime'] = time();
        if(empty($type))
        {
            return M('sucai_pay')->data($data)->add();
        }else{
            return M('sucai_pay')->where(array('device_info'=>$device,
                                               'sucai_id'=>$sucai))->setField('payTime',time());
        }


    }

    public function getSuCaiInfo($device)
    {
        return M('sucai_pay')->where(array('device_info'=>$device))->field('sucai_id,payTime')->select();
    }


}
