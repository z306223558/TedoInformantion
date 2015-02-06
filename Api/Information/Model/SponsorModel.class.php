<?php
/**
 * Created by PhpStorm.
 * 主办方的模型类
 *
 * User: jun
 * Date: 2015/1/22
 * Time: 13:51
 */

namespace Information\Model;
use Think\Model;

class SponsorModel extends Model{
    //获取主页上部的logo列
    function getSponsorList($start,$num)
    {
        return M('sponsor')->where(array('isDel'=>0))->limit($start,$num)->field('sid,sName,sLogo')->order(array('sort'=>'asc'))->select();
    }

    //获取活动的总数
    function getActivtyTotal()
    {
        return M('activity')->where(array('isDel'=>0,'act_end'=>array('gt',date('Y-m-d H:i:s',time())),'act_begin'=>array('lt',date('Y-m-d H:i:s',time()))))->count('aid');
    }

    //分页获取活动列表
    function getActivtyInfoByPage($start,$end,$city)
    {
        return M('activity')->where(array('isDel'=>0,'act_city'=>$city,'act_end'=>array('gt',date('Y-m-d H:i:s',time())),'act_begin'=>array('lt',date('Y-m-d H:i:s',time()))))->limit($start.','.$end)->field('aid,act_name,act_logo,act_begin,act_end,act_addr,top')->order(array('sort'=>'asc'))->select();
    }

    //获取主办方详细信息
    function getSponsorInfoBySid($sid)
    {
        return M('sponsor')->where(array('isDel'=>0))->field('sid,sName,log_desc,cTime,short_desc,contract,c_mobile,c_email,st_count')->find($sid);
    }

    //获取主办方照片墙第一张
    function getSponsorTopImage($sid)
    {
        return M('sponsor_image')->where(array('isDel'=>0,'sid'=>$sid,'top'=>1))->field('url')->find();
    }

    //获取主办方照片墙的总数
    function getSponsorImageCount($sid)
    {
        return M('sponsor_image')->where(array('isDel'=>0,'sid'=>$sid))->count();
    }

    //获取主办方所举办过的活动的集合
    function getSidAidList($sid)
    {
        return M('activity')->where(array('sid'=>$sid,'isDel'=>0))->field('aid,act_name,act_logo')->order(array('act_end'=>'desc'))->select();
    }

    //获取主办方的店铺
    function getSponsorShopList($sid)
    {
        return M('store')->where(array('sid'=>$sid,'is_certified'=>1,'isDel'=>0))->field('st_id,st_name,st_logo')->order(array('is_recommend'=>'desc','sort'=>'asc'))->select();
    }

    function getMoreSponsor()
    {
        return M('sponsor')->where(array('isDel'=>0))->field('sid,sName,sLogo')->order(array('sort'=>'asc'))->select();
    }

    //获取主办方的照片墙信息
    function getSponsorImageList($sid)
    {
        return M('sponsor_image')->where(array('isDel'=>0,'sid'=>$sid))->field('id,url')->order(array('top'=>'desc','cTime'=>'desc'))->select();
    }


}