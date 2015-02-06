<?php
namespace Home\Controller;
use Home\Model\VideoModel;
use Think\Controller;

class VideoController extends Controller
{
    /**
     * @var array 私有变量，用来返回信息
     */
    private $result = array();

    private $model = NULL;

    /**
     * 构造函数，继承父类的构造函数，初始化变量
     */
    public function __construct()
    {
        parent::__construct();
        $this->result = array();
        $this->model = new VideoModel();
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

    public function videoList()
    {
        if(!IS_GET) $this->R($this->result);
        $total = $this->model->getVideoTotal();
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
        $null=array();
        $list = $this->model->getVideoInfoByPage($page->firstRow,$page->listRows);
        $this->result['status'] = 1;
        $this->result['message'] = 'OK';
        $this->result['emessage'] = 'OK';
        $this->result['videoList'] = empty($list) ? $null : $list;
        unset($null);
        unset($list);
        $this->R($this->result);
    }
}