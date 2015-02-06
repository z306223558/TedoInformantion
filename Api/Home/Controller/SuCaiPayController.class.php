<?php
namespace Home\Controller;
use Home\Model\SuCaiPayModel;
use Think\Controller;

class SuCaiPayController extends Controller
{
    /**
     * @var array 私有变量，用来返回信息
     */
    private $result = array();

    private $model = null;

    private $data = null;

    /**
     * 构造函数，继承父类的构造函数，初始化变量
     */
    public function __construct()
    {
        parent::__construct();
        $this->result = array();
        $this->model = new SuCaiPayModel();
        $this->result['status'] = 0;
        $this->result['message'] = '非法操作';
        $this->result['emessage'] = 'Illegal operation';
    }

    public function index()
    {
        $this->R($this->result);
    }

    public function bugSuCai()
    {
        if(!IS_POST) $this->R($this->result);
        $this->data['device_info'] = trim(I('device'));
        $this->data['sucai_id'] = intval(I('id'));
        if(empty($this->data['device_info']) ||  empty($this->data['sucai_id'] ))
        {
            $this->result['message'] = '缺少必要信息';
            $this->result['emessage'] = 'The necessary information can not be empty';
            $this->R($this->result);
        }
        $sucaiInfo = $this->model->getSuCaiInfoByDevice($this->data['device_info'],$this->data['sucai_id']);
        if(empty($sucaiInfo))
        {
            $this->model->setPayInfo($this->data['device_info'],$this->data['sucai_id']);
            $this->result['status'] = 1;
            $this->result['message'] = '购买成功';
            $this->result['emessage'] = 'Buy Success';
            $this->R($this->result);

        }
        elseif((time()-$sucaiInfo['payTime'])>30*24*3600)
        {
            if($this->model->setPayInfo($this->data['device_info'],$this->data['sucai_id'],1))
            {
                $this->result['status'] = 1;
                $this->result['message'] = '购买成功';
                $this->result['emessage'] = 'Buy Success';
                $this->R($this->result);
            }

        }else{
            $this->result['status'] = 0;
            $this->result['message'] = '已经购买，还未过期，无需购买！';
            $this->result['emessage'] = 'You Can Use For One Manth!';
            $this->R($this->result);
        }
    }

    public function getDevicePayList()
    {
        if(!IS_POST) $this->R($this->result);
        $this->data['device_info'] = trim(I('device'));
        if(empty($this->data['device_info']))
        {
            $this->result['message'] = '缺少必要信息';
            $this->result['emessage'] = 'The necessary information can not be empty';
            $this->R($this->result);
        }
        $sucaiD = $this->model->getSuCaiInfo($this->data['device_info']);
        if(empty($sucaiD))
        {
            $this->result['status'] = 1;
            $this->result['message'] = '该设备未购买素材';
            $this->result['emessage'] = 'Has No sucai';
            $this->result['sucai'] = 0;
            $this->R($this->result);
        }
        $sucai = array(0,0,0,0,0,0);
        foreach ($sucaiD as $k =>$v)
        {
            if((time()-$v['payTime'])<30*24*3600)
            {
                $sucai[$v['sucai_id']] = 1;
            }
        }
        $this->result['status'] = 1;
        $this->result['message'] = 'ok';
        $this->result['emessage'] = 'ok';
        $this->result['sucai'] = $sucai;
        $this->R($this->result);
    }
}