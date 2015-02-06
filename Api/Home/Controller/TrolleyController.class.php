<?php
namespace Home\Controller;
use Home\Model\TrolleyModel;
use Think\Controller;

class TrolleyController extends Controller
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
        $this->model = new TrolleyModel();
        $this->result['status'] = 0;
        $this->result['message'] = '非法操作';
        $this->result['emessage'] = 'Illegal operation';
    }

    /**
     * 用来控制非法访问的
     */
    public function index()
    {
        $this->R($this->result);
    }

    /**
     * 将商品加入购物车，就是生成该用户下的未下单的商品
     */
    public function addGoods()
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

        //上传用户图片
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
        $count = $this->model->getTrolleyCount($this->data['uid']);
        $this->result['status'] = 1;
        $this->result['message'] = 'OK';
        $this->result['emessage'] = 'OK';
        $this->result['count'] = empty($count) ? 0 : $count;
        $this->R($this->result);
    }

    /**
     * 获取购物车列表，列出所有未确认下单的商品
     */
    public function listGoods()
    {
        if(!IS_GET) $this->R($this->result);
        $this->data['uid'] = intval(I('uid'));
        if(empty($this->data['uid']))
        {
            $this->result['message'] = '必要信息不能为空';
            $this->result['emessage'] = 'The necessary information can not be empty';
            $this->R($this->result);
        }

        //获取购物车信息
        $goodsList = $this->model->getGoodsListByUid($this->data['uid']);
        foreach($goodsList as $key => $value)
        {
            $goodImage = $this->model->getGoodsImage($value['hd_id'],$value['bd_id']);
            $goodsList[$key]['goodsImage'] = $goodImage['murl'];
            switch($value['size'])
            {
                case 0 :
                    $goodsList[$key]['unit_price'] = 199;
                    break;
                case 1 :
                    $goodsList[$key]['unit_price'] = 299;
                    break;
                case 2 :
                    $goodsList[$key]['unit_price'] = 399;
                    break;
            }
        }
        $rest = array();
        $this->result['status'] = 1;
        $this->result['message'] = 'OK';
        $this->result['emessage'] = 'OK';
        $this->result['goodsList'] = empty($goodsList) ? $rest : $goodsList;
        $this->R($this->result);
    }

    /**
     * 购物车确认购买功能实现，允许同时购买多件商品
     */
    public function confirmGoods()
    {
        if(!IS_POST) $this->R($this->result);
        $this->data['uid'] = intval(I('uid'));
        $this->data['pid'] = trim(I('pid'));
        if(empty($this->data['uid']) || empty($this->data['pid']))
        {
            $this->result['message'] = '请选择商品后，再确认购买';
            $this->result['emessage'] = 'Please select a product, and then confirm the purchase';
            $this->R($this->result);
        }
        //获取收货地址信息和用户图片信息
        $postInfo = $this->model->getPostDefaultByUid($this->data['uid']);
        //获取商品信息
        $pid_str = explode('-',$this->data['pid']);
        $goodsList = $this->model->getGoodsListByPids($pid_str);
        if(empty($goodsList))
        {
            $this->result['message'] = '获取商品信息失败';
            $this->result['emessage'] = 'Commodity information failure';
            $this->R($this->result);
        }
        foreach($goodsList as $key => $value)
        {
            $good[$key]['pid'] = $value['pid'];
            $good[$key]['num'] = $value['num'];
            $good[$key]['size'] = $value['size'];
            $good[$key]['price'] = $value['price'];
            $goodImage = $this->model->getGoodsImage($value['hd_id'],$value['bd_id']);
            $good[$key]['goodsImage'] = $goodImage['murl'];
            switch($value['size'])
            {
                case 0 :
                    $good[$key]['unit_price'] = 199;
                    break;
                case 1 :
                    $good[$key]['unit_price'] = 299;
                    break;
                case 2 :
                    $good[$key]['unit_price'] = 399;
                    break;
            }
            unset($goodImage);
        }
        unset($goodsList);
        $userImage = $this->model->getUserImageList($this->data['uid']);
        $uImage = unserialize($userImage['user_image']);
        unset($this->data);
        $rest = array();
        $this->result['status'] = 1;
        $this->result['message'] = 'OK';
        $this->result['emessage'] = 'OK';
        //组装默认的收货地址信息
        $post['postId'] = $postInfo['postId'];
        $post['postName'] = $postInfo['pName'];
        $post['postAddr'] = $postInfo['province'].$postInfo['city'].$postInfo['zone'].$postInfo['addr'];
        unset($postInfo);
        $this->result['postAddrList'] = empty($post) ? $rest : $post ;
        unset($post);
        $this->result['goodsList'] = $good;
        unset($good);
        $this->result['userImage'] =  empty($uImage) ? $rest : $uImage ;
        $this->R($this->result);
    }

    /**
     * 为删除用户购物车商品信息
     */
    public function deleteTrolleyGood()
    {
        if(!IS_POST) $this->R($this->result);
        $pid = I('pid');
        if(empty($pid))
        {
            $this->result['message'] = '必要信息不能为空';
            $this->result['emessage'] = 'The necessary information can not be empty';
            $this->R($this->result);
        }
        $goodInfo = $this->model->getGoodsInfoById(implode(',',explode('-',$pid)));
        if(empty($goodInfo))
        {
            $this->result['message'] = '获取商品信息失败';
            $this->result['emessage'] = 'Commodity information failure';
            $this->R($this->result);
        }
        $this->model->startTrans();
        foreach($goodInfo as $key => $value)
        {
            $res = $this->model->delTrolleyGoods($value['pid']);
            if(empty($res))
            {
                $error[$key] = $value['pid'];
                unset($res);
                continue;
            }else
            {
                $success[$key] = $value['pid'];
                unset($re);
                continue;
            }
        }
        $this->model->commit();
        if(empty($success))
        {
            $this->model->rollback();
            $this->result['message'] = '删除商品信息失败';
            $this->result['emessage'] = 'Opearation failure';
            $this->R($this->result);
        }elseif(!empty($error))
        {
            $errorInfo = '';
            foreach($error as $k=>$v)
            {
                $errorInfo .= ' 第'.$k.'个 ';
            }
            $this->model->rollback();
            $this->result['status'] = 1;
            $this->result['message'] = '删除'.$errorInfo.'商品信息失败,其他成功';
            $this->result['emessage'] = 'Opearation failure';
            $this->R($this->result);
        }
        $this->result['status'] = 1;
        $this->result['message'] = '成功删除'.count($success).'商品！';
        $this->result['emessage'] = 'OK';
        $this->R($this->result);
    }
} 