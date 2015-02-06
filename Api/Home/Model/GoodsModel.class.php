<?php
namespace Home\Model;
use Think\Model;

class GoodsModel extends Model
{

    /**
     * 构造函数，继承父类的构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取商品图片信息
     * @param $hd_id
     * @param $bd_id
     *
     * @return bool
     */
    public function getModelImageInfoById($hd_id,$bd_id)
    {
        if(empty($hd_id) || empty($bd_id))
        {
            return false;
        }

        $res = M('decorations_model')->where(array('hd_id' => $hd_id,
                                 'bd_id' => $bd_id,
                                 'isDel' => 0))->field('murl,big_url')
                                               ->find();
        return $res;
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

    /**
     * 查询对应ID的收货人信息
     * @param $postId
     * @param $uid
     *
     * @return bool
     */
    public function getPostInfoById($postId,$uid)
    {
        if(empty($postId))
        {
            return false;
        }

        return M('post')->where(array('postId' => $postId,
                                      'isDel'  => 0,
                                      'uid'    => $uid
            ))->field('pName,province,city,zone,addr')
              ->select();
    }

    /**
     * 生成订单信息
     * @param $data
     *
     * @return bool
     */
    public function setOrderInfo($data)
    {
        if(empty($data))
        {
            return false;
        }

        return M('order')->data($data)->add();
    }

    /**
     * 生成订单同时，更新商品表的对应商品的确认信息
     * @param $pids
     *
     * @return bool
     */
    public function updataGoodsConfirm($pids)
    {
        if(empty($pids))
        {
            return false;
        }

        return M('goods')->where(array('pid' => array( 'IN' ,$pids),
                                       'isDel'=> 0
                       ))->setField('confirm',1);
    }

    /**
     * 获取单个商品id
     * @param $pid
     *
     * @return bool
     */
    public function getGoodsInfoById($pid)
    {
        if(empty($pid))
        {
            return false;
        }

        return M('goods')->where(array('pid'=>$pid,
                                       'isDel'=>0,
                                       'confirm'=>0

            ))->find();
    }

    public function updataGoodsNumInfo($data,$pid)
    {
        if(empty($pid) || empty($data))
        {
            return false;
        }

        $data['cTime'] = time();
        return M('goods')->where(array('pid'=>$pid,
                                       'isDel'=>0,
                                       'confirm'=>0
            ))->data($data)->save();
    }
}