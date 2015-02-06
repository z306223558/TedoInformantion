<?php
namespace Home\Controller;
use Home\Model\GoodsModel;
use Think\Controller;

class GoodsController extends Controller
{
    /**
     * @var array 私有变量，用来返回信息
     */
    private $result = array();

    private $model = NULL;

    private $data = NULL;

    /**
     * 构造函数，继承父类的构造函数，初始化变量
     */
    public function __construct()
    {
        parent::__construct();
        $this->result = array();
        $this->model = new GoodsModel();
        $this->result['status'] = 0;
        $this->result['message'] = '非法操作';
        $this->result['emessage'] = 'Illegal operation';
    }

    /**
     * 用户所选取的商品的详细信息，返回模型图片url
     */
    public function selectGoodsInfo()
    {
        if(!IS_GET) $this->R($this->result);
        $hd_id = intval(I('hd_id'));
        $bd_id = intval(I('bd_id'));

        if(empty($hd_id) || empty($bd_id))
        {
            $this->result['message'] = '缺少必要信息';
            $this->result['emessage'] = 'The necessary information can not be empty';
            $this->R($this->result);
        }

        $modelImage = $this->model->getModelImageInfoById($hd_id,$bd_id);
        if(empty($modelImage))
        {
            $this->result['message'] = '没有找到相关信息';
            $this->result['emessage'] = 'The relevant information is not found';
            $this->R($this->result);
        }
        $this->result['status'] = 1;
        $this->result['message'] = 'OK';
        $this->result['emessage'] = 'OK';
        $this->result['goodsImage'] = $modelImage['murl'];
        $this->result['goodsBigImage'] = $modelImage['big_url'];
        $this->R($this->result);
    }

    /**
     * 确认购买商品接口，生成商品信息，返回跳转到商品详情页面
     */
    public function confirmBuyGoods()
    {
        if(!IS_POST) $this->R($this->result);
        $this->data['uid'] = intval(I('uid'));
        $this->data['hd_id'] = intval(I('hd_id'));
        $this->data['bd_id'] = intval(I('bd_id'));
        //数量若为空，则默认为1
        $this->data['num'] = intval(I('num'));
        $this->data['num'] = empty($this->data['num']) ? 1 : $this->data['num'] ;
        //size若为空，则默认为1
        $this->data['size'] = intval(I('size_type'));
        //其他若为空，则返回错误
        if(empty($this->data['uid']) || empty($this->data['hd_id']) || empty($this->data['bd_id']))
        {
            $this->result['message'] = '必要信息不能为空';
            $this->result['emessage'] = 'The necessary information can not be empty';
            $this->R($this->result);
        }
        //根据size来确定价格
        if(empty($this->data['size']))
        {
            $this->data['price'] =  $this->data['num'] * 199;
        }elseif($this->data['size'] == 1)
        {
            $this->data['price'] =  $this->data['num'] * 299;
        }else
        {
            $this->data['price'] =  $this->data['num'] * 399;
        }
        $this->data['cTime'] = time();

        //用户图片信息上传
        if(!empty($_FILES))
        {
            $image = new ImageController();
            $rs = $image->uploadUserGoodesImage();
            $this->data['user_model'] = $rs;
        }
        //生成商品信息
        $pid = $this->model->setGoodsInfo($this->data);
        if(empty($pid))
        {
            $this->result['message'] = '商品信息生成失败';
            $this->result['emessage'] = 'Commodity information generation failure';
            $this->R($this->result);
        }
        $goodsInfo[0]['pid'] = $pid;
        //获取收货地址信息和用户图片信息
        $rest= array();
        $userImage = $this->model->getUserImageList($this->data['uid']);
        $uImage = unserialize($userImage['user_image']);
        $this->result['status'] = 1;
        $this->result['message'] = 'OK';
        $this->result['emessage'] = 'OK';
        //组装默认的收货地址信息
        $postInfo = $this->model->getPostDefaultByUid($this->data['uid']);
        $post['postId'] = $postInfo['postId'];
        $post['postName'] = $postInfo['pName'];
        $post['postAddr'] = $postInfo['province'].$postInfo['city'].$postInfo['zone'].$postInfo['addr'];
        unset($postInfo);
        $this->result['postAddrList'] = empty($post) ? $rest : $post ;
        unset($post);
        //构造商品信息
        $goodsInfo[0]['num'] = $this->data['num'];
        $goodsInfo[0]['size'] =$this->data['size'];
        $goodsInfo[0]['price'] = $this->data['price'];
        $goodUrl = $this->model->getModelImageInfoById($this->data['hd_id'],$this->data['bd_id']);
        $goodsInfo[0]['goodsImage'] = $goodUrl['murl'];
        switch($goodsInfo[0]['size'])
        {
            case 0 :
                $goodsInfo[0]['unit_price'] = 199;
                break;
            case 1 :
                $goodsInfo[0]['unit_price'] = 299;
                break;
            case 2 :
                $goodsInfo[0]['unit_price'] = 399;
                break;
        }
        $this->result['goodsList'] = $goodsInfo;
        unset($goodsInfo);
        unset($this->data);
        $this->result['userImage'] = empty($uImage) ? $rest : $uImage;
        unset($this->data);
        unset($userImage);
        $this->R($this->result);
    }

    /**
     * 下单接口
     */
    public function goodsOrder()
    {
        if(!IS_POST) $this->R($this->result);
        $this->data['uid'] = intval(I('uid'));
        $this->data['pid'] = trim(I('pid'));
        $this->data['postId'] = intval(I('postId'));
        $this->data['total'] = I('total_price');
        $this->data['order_desc'] = trim(I('content'));
        if(empty($this->data['uid']) || empty($this->data['pid']) || empty($this->data['postId']) || empty($this->data['total']))
        {
            $this->result['message'] = '必要信息不能为空';
            $this->result['emessage'] = 'The necessary information can not be empty';
            $this->R($this->result);
        }

        //生成订单信息
        $this->data['cTime'] = time();
        $this->data['order_num'] = $this->createOrderNo();
        $this->data['goods_num'] = count(explode('-',$this->data['pid']));
        $this->data['exp_date'] = 7200;
        $rs = $this->model->setOrderInfo($this->data);
        $goodUpdate = $this->model->updataGoodsConfirm(implode(',',explode('-',$this->data['pid'])));
        if(empty($rs) || empty($goodUpdate))
        {
            $this->result['message'] = '生成订单失败';
            $this->result['emessage'] = 'Failed to generate orders';
            $this->R($this->result);
        }

        $this->result['status'] = 1;
        $this->result['message'] = 'OK';
        $this->result['emessage'] = 'OK';
        $this->result['bid'] = $this->data['order_num'];
        $this->result['cTime'] = $this->data['cTime'];
        //获取收货信息
        $postInfo = $this->model->getPostInfoById($this->data['postId'],$this->data['uid']);
        $this->result['postAddr'] = $postInfo[0]['province'].$postInfo[0]['city'].$postInfo[0]['zone'].$postInfo[0]['addr'].' '.$postInfo[0]['pName'];
        $this->result['goodsNum'] = $this->data['goods_num'];
        $this->result['total'] = $this->data['total'];
        $this->result['content'] = $this->data['order_desc'];
        $this->result['orderId'] = intval($rs);
        unset($this->data);
        $this->R($this->result);
    }

    /**
     * 生成订单号
     */
    public function createOrderNo() {
        $year_code = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        return $year_code[intval(date('Y')) - 2010] .
        strtoupper(dechex(date('m'))) . date('d') .
        substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('d', rand(0, 99));
    }

    public function changeGoodsNum()
    {
        if(!IS_POST) $this->R($this->result);
        $num = intval(I('num'));
        $pid = intval(I('pid'));
        if(empty($num) || empty($pid))
        {
            $this->result['message'] = '必要信息不能为空';
            $this->result['emessage'] = 'The necessary information can not be empty';
            $this->R($this->result);
        }
        $goodInfo = $this->model->getGoodsInfoById($pid);
        $this->data['size'] = $goodInfo['size'];
        //根据size来确定价格
        if(empty($this->data['size']))
        {
            $this->data['price'] =  $num * 199;
        }
        elseif($this->data['size'] == 1)
        {
            $this->data['price'] =  $num * 299;
        }else
        {
            $this->data['price'] =  $num * 399;
        }
        $this->data['num'] = $num;
        $rs = $this->model->updataGoodsNumInfo($this->data,$pid);
        if(empty($rs))
        {
            $this->result['message'] = '修改购物信息失败';
            $this->result['emessage'] = 'Change good info failed';
            $this->R($this->result);
        }
        $return = $this->model->getGoodsInfoById($pid);
        $this->result['status'] = 1;
        $this->result['message'] = 'OK';
        $this->result['emessage'] = 'OK';
        $this->result['newInfo'] = $return;
        $this->R($this->result);
    }
} 