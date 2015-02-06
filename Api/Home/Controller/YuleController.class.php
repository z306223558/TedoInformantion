<?php
namespace Home\Controller;
use Home\Model\YuleModel;
use Think\Controller;

class YuleController extends Controller
{
    /**
     * @var array 私有变量，用来返回信息
     */
    private $result = array();

    private $model = null;

    /**
     * 构造函数，继承父类的构造函数，初始化变量
     */
    public function __construct()
    {
        parent::__construct();
        $this->result = array();
        $this->model = new YuleModel();
        $this->result['status'] = 0;
        $this->result['message'] = '非法操作';
        $this->result['emessage'] = 'Illegal operation';
    }

    /**
     * index方法，用于控制非法访问
     */
    public function index()
    {
        $this->R($this->result);
    }

    public function getYuleInfo()
    {
        if(!IS_GET) $this->R($this->result);
        $total = $this->model->getYuleCount();
        $page = new \Think\Page($total,5);
        $page->firstRow = 0;
        $page->listRows = 5;
        $start = intval(I('start'));
        $num = intval(I('num'));
        if(!empty($start))
        {
            $page->firstRow = $start;
        }
        if(!empty($num))
        {
            $page->listRows = $num;
        }
        $list = $this->model->getYuleInfoByPage($page->firstRow,$page->listRows);
        if(empty($list))
        {
            $this->result['status'] = 1;
            $this->result['message'] = '没有数据';
            $this->result['emessage'] = 'has no info';
            $this->result['yule'] = 0;
            $this->R($this->result);
        }
        foreach($list as $k=>$v)
        {
            $type = $this->model->getTypeInfoById($v['type']);
            $list[$k]['type'] = $type['name'];
            $image = unserialize($v['image']);
            $list[$k]['image'] = empty($image) ? 0 : array_values($image);
        }
        $this->result['status'] = 1;
        $this->result['message'] = 'OK';
        $this->result['emessage'] = 'OK';
        $this->result['yule'] = $list;
        $this->R($this->result);

    }

//    public function imageAdd()
//    {
//        $image[0] = '/Public/Image/Yule/1/1.jpg';
//        $image[1] = '/Public/Image/Yule/1/2.jpg';
//        $image[2] = '/Public/Image/Yule/1/3.jpg';
//        $image[3] = '/Public/Image/Yule/1/4.jpg';
//        var_dump($image);
//        var_dump(json_encode($image[0]));
//        var_dump(json_encode($image[1]));
//        var_dump(json_encode($image[2]));
//        var_dump(json_encode($image[3]));
//
//        exit;
//    }
}