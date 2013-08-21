<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

/**
 * 用户组模型类
 * Class AuthGroupModel 
 * @author 朱亚杰 <zhuyajie@topthink.net>
 */
class AuthGroupModel extends CmsadminModel
{
    const TYPE_ADMIN        = 1;                    //管理员用户组类型标识
    const AUTH_GROUP_ACCESS = 'auth_group_access';  //关系表表名
    const AUTH_GROUP        = 'auth_group';         //用户组表名

    protected $_validate = array(
        array('title','require', '必须设置用户组标题', Model::MUST_VALIDATE ,'regex',Model::MODEL_BOTH),
        array('description','0,80', '描述最多80字符', Model::VALUE_VALIDATE , 'length'  ,Model::MODEL_BOTH ),
        array('rules','/^(\d,?)+(?<!,)$/', '规则数据不合法', Model::VALUE_VALIDATE , 'regex'  ,Model::MODEL_BOTH ),
    );

    /**
     * 返回用户组列表
     * 默认返回正常状态的管理员用户组列表
     * @param array $where   查询条件,供where()方法使用
     *
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function getGroups($where=array())
    {
        $map = array('status'=>1,'type'=>self::TYPE_ADMIN,'module'=>'admin');
        $map = array_merge($map,$where);
        return $this->where($map)->select();
    }

    /**
     * 把用户添加到用户组,支持批量添加用户到用户组
     * @author 朱亚杰 <zhuyajie@topthink.net>
     * 
     * 示例: 把uid=1的用户添加到group_id为1,2的组 `AuthGroupModel::addToGroup(1,'1,2');`
     */
    static public function addToGroup($uid,$gid)
    {
        $uid = is_array($uid)?$uid:explode(',',$uid);
        $gid = is_array($gid)?$gid:explode(',',$gid);

        $Access = M(self::AUTH_GROUP_ACCESS);
        foreach ($uid as $u){
            if(is_numeric($u)){
                $id = $Access->where( array('uid'=>$u) )->delete(); //删除用户旧的用户组关系
                foreach ($gid as $g){
                    if(is_numeric($g)){
                        $Access->add(array('uid'=>$u,'group_id'=>$g));
                    }
                }
            }
        }
    }

    /**
     * 返回用户所属用户组信息
     * @param  int    $uid 用户id
     * @return array  用户所属的用户组 array(
     *                                         array('uid'=>'用户id','group_id'=>'用户组id','title'=>'用户组名称','rules'=>'用户组拥有的规则id,多个,号隔开'),
     *                                         ...)   
     */
    static public function getUserGroup($uid)
    {
        static $groups = array();
        if (isset($groups[$uid]))
            return $groups[$uid];
        $prefix = C('DB_PREFIX');
        $user_groups = M()
            ->field('uid,group_id,title,description,rules')
            ->table($prefix.self::AUTH_GROUP_ACCESS.' a')
            ->join ($prefix.self::AUTH_GROUP." g on a.group_id=g.id")
            ->where("a.uid='$uid' and g.status='1'")
            ->select();
        $groups[$uid]=$user_groups?$user_groups:array();
        return $groups[$uid];
    }
    
}
