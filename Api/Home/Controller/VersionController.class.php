<?php

namespace Home\Controller;
use Home\Model\VersionModel;
use Think\Controller;

/**
 * 版本控制
 */
class VersionController extends Controller
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
        $this->result['status'] = 0;
        $this->model = new VersionModel();
        $this->result['message'] = '非法操作';
        $this->result['emessage'] = 'Illegal operation';
    }

    public function versionCheck()
    {
        if(!IS_GET) $this->R($this->result);
        $oldVersion = I('version');
        $type = intval(I('type'));
        if(empty($oldVersion))
        {
            $this->result['message'] = '必要信息不能为空';
            $this->result['emessage'] = 'The necessary information can not be empty';
            $this->R($this->result);
        }
        $version = $this->model->getVersionUpdateInfo($type);
        if($version['version'] === $oldVersion)
        {
            $this->result['message'] = '已经是最新版本，无需更新';
            $this->result['emessage'] = 'There is no update';
            $this->R($this->result);
        }
        $this->result['status'] = 1;
        $this->result['message'] = '版本已更新，请下载更新';
        $this->result['emessage'] = 'Update information success';
        $this->result['versionInfo'] = $version;
        $this->R($this->result);
    }
}
?>