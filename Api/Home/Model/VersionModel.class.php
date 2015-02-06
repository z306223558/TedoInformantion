<?php

namespace Home\Model;
use Think\Model;

class VersionModel extends Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getVersionUpdateInfo($type)
    {
        return  M('version')->where(array('use_able' => 1,'type'=>intval($type)))->find();
    }

}