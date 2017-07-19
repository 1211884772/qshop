<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/2/17
 * Time: 17:41
 */

namespace Admin\Controller;

use Utils\Page;
class UserauthController extends CommonController
{
    public function actionIndex()
    {

        $length = intval(I('get.line_rows'))>0 ? I('get.line_rows'):10;
        $start_rows=   intval(I('get.start_rows'))>0? intval(I('get.start_rows')):0;

        $where = array();
        $title = I('request.title');
        if (!empty(trim($title))) {
            $where['title'] =  array('like', '%' . $title . '%');
        }
        /* 获取用户数据 */
        $auth_group_list = D('auth_group')->where($where)->order('id desc')->limit($start_rows, $length)->select();
        $auth_group_count = D('auth_group')->where($where)->count();
        $this->assign('list', $auth_group_list);

        $this->listPage($auth_group_count,$length);
        $this->display('index');
    }

    /**
     * 访问授权
     */
    public function actionEdit()
    {
        $id = I('request.id');
        //已选择的规则
        $check_rule_arr = D('auth_group')->where(array('id'=>$id))->find();
        $check_rule = explode(",",$check_rule_arr['rules']);
        //所有的规则
        $auth_rule_list = D('auth_rule')->where(array('status'=>1))->order('pid asc,sort asc')->select();
        $this->assign('auth_rule_list',getTree($auth_rule_list));
        $this->assign('check_rule_arr',$check_rule);
        $this->assign('id',$id);

        $this->display('edit');
    }

    public function actionDoEdit(){
        $id = I('post.id');
        $rule_arr = I('post.rules');
        $rules = implode(',',$rule_arr);
        if(!$id || empty($rules)){
            $this->error('权限修改失败！','',true);
        }
        $res = D('auth_group')->where(array('id'=>$id))->save(array('rules'=>$rules));
        if (!$res) {
            $this->error('权限修改失败！','',true);
        } else {
            $this->success('权限修改成功！', U('Userauth/index'),true);
        }
    }




}