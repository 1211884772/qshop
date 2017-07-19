<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/2/17
 * Time: 10:56
 */

namespace Admin\Controller;

use Utils\Page;
class UsergroupController extends CommonController
{

    //添加和编辑的验证
    private  $rules = array(
        array('title','','用组户名称已经存在！',0,'unique',1), // 在新增的时候验证name字段是否唯一
        array('title','/^\w{3,}$/','用组户名称必须3个字母以上',0,'regex'),
    );

    public function actionIndex()
    {

        $length = intval(I('get.line_rows'))>0 ? I('get.line_rows'):10;
        $start_rows=   intval(I('get.start_rows'))>0? intval(I('get.start_rows')):0;

        $title = I('request.title');
        $where = array();
        if (!empty(trim($title))) {
            $where['title'] =  array('like', '%' . $title . '%');
        }
        /* 获取用户数据 */
        $auth_group_list = D('auth_group')->where($where)->order('id desc')->limit($start_rows, $length)->select();
        // echo  D()->getLastsql();
        $auth_group_count = D('auth_group')->where($where)->count();

        $this->assign('list', $auth_group_list);// 赋值数据集


        $this->listPage($auth_group_count,$length);
        $this->display('index');
    }

    /**
     * 添加用户组
     */
    public function actionAdd()
    {
        $title = I('post.title');
        if (!D('auth_group')->validate($this->rules)->create()){
            $this->error(D('User')->getError(), '', true);
        }
        $res = D('auth_group')->add(array('title' => $title, 'status' => 1));
        if ($res>0) {
            $this->error('用户组新增失败！');
        } else {
            $this->success('用户组新增成功！', U('Usergroup/index'),true);
        }
    }

    /**
     * 修改页面
     */
    public function actionEdit()
    {
        $id = I('request.id');
        $arr = D('auth_group')->where(array('id' => $id))->find();
        $this->assign('auth_group_arr', $arr);
        $this->display('edit');
    }

    /**
     * 编辑用户组
     */
    public function actionDoEdit()
    {
        $id = I('post.id');
        $title = I('post.title');
        if (!D('auth_group')->validate($this->rules)->create()){
            $this->error(D('User')->getError(), '', true);
        }
        $res = D('auth_group')->where(array('id' => $id))->save(array('title' => $title));
        if ($res===false) {
            $this->error('修改用户组失败！','',true);
        } else {
            $this->success('修改用户组成功！', U('Usergroup/index'),true);
        }

    }

    /**
     * 删除会员组
     */
    public function actionDoDelete()
    {
        $id = I('request.id');
        $id_str = !empty(I('request.id_check_s'))? I('request.id_check_s'):$id;
        if ($id_str) {
            D()->startTrans();
            $res_group = D('auth_group')->where(array('id' => array('in',$id_str)))->delete();
            //必须同时删除会员和此组的对应关系在auth_group_access表
            $res_access = D('auth_group_access')->where(array('group_id' =>  array('in',$id_str)))->delete();
            if ($res_group===false ||$res_access==false) {
                D()->rollback();
                $this->error('删除用户组失败！','',true);
            } else {
                D()->commit();
                $this->success('删除用户组成功！', U('Usergroup/index'),true);
            }
        }
    }


    /**
     * 更改状态
     */
    public function actionDoStatus()
    {
        $id = I('request.id');
        $id_str = !empty(I('request.id_check_s')) ? I('request.id_check_s') : $id;
        if (!($id > 0)) {
            $this->error('修改用户组失败！','',true);
        }
        $res = D()->execute("UPDATE `gdsh_auth_group` SET `status`=abs(`status`-1) WHERE ( `id` IN ('".$id_str."') )");
        if (!$res) {
            $this->error('修改用户组失败！','',true);
        } else {
            $this->success('修改用户组成功！','',true);
        }

    }


}