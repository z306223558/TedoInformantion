<?php
namespace Home\Controller;
use Home\Model\OrderModel;
use Think\Controller;
use Think\Page;

class OrderController extends  Controller
{
    private $result = array();

    private $model = NULL;

    private $data = NULL;

    public function __construct()
    {
        parent::__construct();
        $this->result['status'] = 0;
        $this->result['message'] = '非法操作';
        $this->result['emessage'] = 'Illegal operation';
        $this->model = new OrderModel();
    }

    public function index()
    {
        $this->R($this->result);
    }

    /**
     * 用户订单信息获取
     */
    public function getUserOrder()
    {
        if(!IS_POST) $this->R($this->result);
        $type = intval(I('type'));
        $uid = intval(I('uid'));
        $start = intval(I('start'));
        $num = intval(I('num'));
        if(empty($uid))
        {
            $this->result['message'] = '缺少必要信息';
            $this->result['emessage'] = 'The necessary information can not be empty';
            $this->R($this->result);
        }
        $orderNum = $this->model->getUserOrderNum($uid,$type);
        $page = new Page($orderNum,5);
        $page->firstRow = 0;
        $page->listRows = 5;
        if(!empty($start))
        {
            $page->firstRow = $start;
        }
        if(!empty($num))
        {
            $page->listRows = $num;
        }
        $list = $this->model->getUserOrderInfoByPage($page->firstRow,$page->listRows,$type,$uid,$orderNum);
        $rest = array();
        if(empty($list))
        {
            $this->result['status'] = 1;
            $this->result['message'] = '未找到订单信息';
            $this->result['emessage'] = 'Has No Order Info';
            $this->result['type'] = empty($type) ? '未完成订单' : '已完成订单';
            $this->result['orderList'] = $rest;
            $this->R($this->result);
        }
        foreach($list as $key => $value)
        {
            $list[$key]['goodsList'] = $this->model->getGoodsListByPids(implode(',',explode('-',$value['pid'])));
            foreach($list[$key]['goodsList'] as $k=>$v)
            {
                $image = $this->model->getGoodsImageModel($v['hd_id'],$v['bd_id']);
                $list[$key]['goodsList'][$k]['goodsImage'] = $image['murl'];
            }
            $post = $this->model->getPostInfo($value['postId']);
            $list[$key]['postAddr'] = $post['province'].$post['city'].$post['zone'].$post['addr'];
            $num_info = $this->model->getOrderPostInfo($value['orderId']);
            $list[$key]['post_num'] = empty($num_info['num_info']) ? 0 : $num_info['num_info'];
        }
        $this->result['status'] = 1;
        $this->result['message'] = 'OK';
        $this->result['emessage'] = 'OK';
        $this->result['type'] = empty($type) ? '未完成订单' : '已完成订单';
        $this->result['orderList'] = $list;
        $this->R($this->result);
    }

    public function delOrderInfo()
    {
        if(!IS_POST) $this->R($this->result);
        $uid = intval(I('uid'));
        $orderId = intval(I('orderId'));
        if(empty($uid) || empty($orderId))
        {
            $this->result['message'] = '缺少必要信息';
            $this->result['emessage'] = 'The necessary information can not be empty';
            $this->R($this->result);
        }
        $user = $this->model->getUserInfoByUid($uid);
        if(empty($user))
        {
            $this->result['message'] = '用户不存在';
            $this->result['emessage'] = 'The user information error';
            $this->R($this->result);
        }
        $order = $this->model->getUserOrderInfoByOrderId($uid,$orderId);
        if(empty($order))
        {
            $this->result['message'] = '无此订单信息';
            $this->result['emessage'] = 'has no orderInfo';
            $this->R($this->result);
        }
        if(!($this->model->delOrderInfo($orderId)))
        {
            $this->result['message'] = '操作失败';
            $this->result['emessage'] = 'update failed';
            $this->R($this->result);
        }
        $this->result['status'] = 1;
        $this->result['message'] = 'OK';
        $this->result['emessage'] = 'OK';
        $this->R($this->result);
    }
}