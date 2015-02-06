<?php
/**
 * Created by PhpStorm.
 * 资讯模块的基类，提供一些基本的共同操作
 *
 * User: jun
 * Date: 2015/1/22
 * Time: 13:39
 */

namespace Common\Controller;
use Think\Controller;

class InformationBaseController extends Controller {

    protected $model = null;

    protected $data = array();

    protected $result = array();

    /**
     * 构造函数
     */
    function __construct()
    {
        parent::__construct();
        $this->result['status'] = 0;
        $this->result['message'] = '非法操作';
        $this->result['emessage'] = 'Illegal operation';
    }

    /**
     * 返回流程中执行状态
     * @param int $code
     */
    function getStatusInfo($code = 0)
    {
        switch($code){
            case 0 :
                $this->result['message'] = '必要信息不能为空';
                $this->result['emessage'] = 'The necessary information can not be empty';
                $this->R($this->result);
                break;
            case -1 :
                $this->result['status'] = 1;
                $this->result['message'] = '没有找到相关信息';
                $this->result['emessage'] = 'Has On more Information';
                break;
            case -2 :
                $this->result['message'] = '写入数据库失败';
                $this->result['emessage'] = 'Insert to dateBase Failed';
                $this->R($this->result);
                break;
            case -3 :
                $this->result['message'] = '更新数据库失败';
                $this->result['emessage'] = 'Update dateBase Failed';
                $this->R($this->result);
                break;
            case -4 :
                $this->result['message'] = '附件数量超过预定数目';
                $this->result['emessage'] = 'Files count is Bridge';
                $this->R($this->result);
                break;
            case -5 :
                $this->result['message'] = '文件上传错误！';
                $this->result['emessage'] = 'Upload files failed';
                $this->R($this->result);
                break;
            case 1 :
                $this->result['status'] = 1;
                $this->result['message'] = 'ok';
                $this->result['emessage'] = 'ok';
                break;
            default :
                $this->result['message'] = '必要信息不能为空';
                $this->result['emessage'] = 'The necessary information can not be empty';
                $this->R($this->result);
                break;
        }
    }

    //用来分页的初始化
    function getPageInfo($total,$start=0,$num=5)
    {
        if(empty($total))
        {
            return false;
        }
        $page = new \Think\Page($total,$num);
        $page->firstRow = 0;
        //如果给定了开始和结束值则赋值
        if(empty($num))
        {
            $page->listRows = $total;
        }
        else
        {
            $page->listRows = $num;
        }
        //若开始地方不为空
        if(!empty($start))
        {
            $page->firstRow = $start;
        }
        return $page;
    }


}