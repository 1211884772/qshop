<?php
/**
 * Created by PhpStorm.
 * User: wei.wang
 * Date: 2017/6/26
 * Time: 14:55
 */

namespace app\modules\admin\controllers;

use yii;
use yii\web\Controller;
use yii\web\Request;

class MemberrController extends CommonController
{

    public function actionIndex()
    {
        $get = $this->getGET();
        $length = isset($get['line_rows']) && empty($get['line_rows']) ? intval($get['line_rows']):10;
        $start_rows=   isset($get['start_rows']) && empty($get['start_rows']) ? intval($get['start_rows']):10;
        $username = $get['username'];
        $uid = $get['uid'];
        $where = array();
        if (!empty(trim($username))) {
            $where['username'] = array('like', '%' . $username . '%');
        }
        if (!empty(trim($uid))) {
            $where['uid'] = $uid;
        }
        /* 获取用户数据 */
        $user_list = D('Member')->where($where)->order(array('uid' => 'desc'))->limit($start_rows, $length)->select();

        $user_count = D('Member')->where($where)->count();

        foreach ($user_list as $k => $v) {
            //$user_list[$k]['last_login_time'] = date('Y-m-d H:s', $v['last_login_time']);
            if(!empty($v['avatar'])){
                $user_list[$k]['avatar'] =  getDomain().$v['avatar'];
            }else{
                $user_list[$k]['avatar']= getDefaultAvatar();
            }
        }
        $this->assign('list', $user_list);// 赋值数据集

        //------------------
        //获取搜索的所有请求参数//
        $this->listPage($user_count, $length);
        //------------------
        $this->display('index');
    }

    /**
     * 添加用信息
     */
    public function actionAdd()
    {
        if (!D('User')->validate($this->rules)->create()){
            // 如果创建失败 表示验证没有通过 输出错误提示信息
            $this->error(D('User')->getError(), '', true);
        }
        $user_data['username'] = I('post.username');
        $user_data['secretkey'] = randStr(8);
        $user_data['password'] = gdsh_md5($user_data['secretkey'],I('post.password'), C(GDSH_AUTH_KEY));//加密密码
        $user_data['mobile'] = I('post.mobile');
        $user_data['email'] = I('post.email');
        $user_data['reg_time'] = mktime();
        $user_data['reg_ip'] = get_client_ip();
        $user_data['status'] = 1;

        D()->startTrans();
        $uid = D('user')->add($user_data);
        $member_data = array('uid' => $uid, 'username' => $user_data['username'], 'status' => 1);
        $res = D('member')->add($member_data);
        if (!($res>0) || !($uid>0)) {
            D()->rollback();
            $this->error('会员添加失败！', '', true);
        } else {
            D()->commit();
            $this->success('会员添加成功！', U('User/index'), true);
        }
    }

    public function actionEdit()
    {
        $id = I("request.id");
        $user_arr = D("user")->where(array('id' => $id))->find();
        $member_arr = D("member")->where(array('uid' => $id))->find();

        if(!empty($member_arr['avatar'])){
             $member_arr['avatar'] =  getDomain().$member_arr['avatar'];
        }else{
             $member_arr['avatar']= getDefaultAvatar();
        }

        $this->assign('user_arr', $user_arr);
        $this->assign('member_arr', $member_arr);
        $this->display('edit');
    }


    public function actionDoEdit()
    {
        //上传文件
        if (!empty(I('post.id')) && !empty($_FILES['avatar'])) {

            $u_file = $_FILES['avatar'];
            $upload_img = new Upload();
            $upload_img->sub_path = 'avatar/';
            if($img_info = $upload_img->uplodeOne($u_file)){
                //图片地址存数据库
                $img_url = $img_info['save_path']. $img_info['save_name'].'.'. $img_info['ext'];
                $res = D('member')->where(array('uid' => I('post.id')))->save(array('avatar' => $img_url));
            }else{
                $this->error($upload_img->getError(), '', true);
            }
            if($res===false){
                @unlink($img_url);
                $this->error('修改会员图像失败！','', true);
            }else{
                $this->success('修改会员图像成功！','', true);
            }
        }

        if (!D('User')->validate($this->rules)->create()){
            // 如果创建失败 表示验证没有通过 输出错误提示信息
            $this->error(D('User')->getError(), '', true);
        }

        $user_data['username'] = I('post.username');
        $user_data['secretkey'] = randStr(8);
        $user_data['password'] = gdsh_md5($user_data['secretkey'],I('post.password'), C(GDSH_AUTH_KEY));//加密密码
        $user_data['mobile'] = I('post.mobile');
        $user_data['email'] = I('post.email');
        $user_data['update_time'] = mktime();
        D()->startTrans();
        $uid = D('User')->where(array('id' => I('post.id')))->save($user_data);
        $member_data = array('username' => $user_data['username']);
        $res = D('member')->where(array('uid' => I('post.id')))->save($member_data);

        if ($res===false || $uid ===false) {
            D()->rollback();
            $this->error('会员修改失败！', '', true);
        } else {
            D()->commit();
            $this->success('会员修改成功！', U('User/index'), true);
        }
    }

    /**
     * 删除会员
     */
    public function actionDoDelete()
    {

        $uid = I('request.id');
        $uid_str = !empty(I('request.id_check_s')) ? I('request.id_check_s') : $uid;
        if ($uid_str) {
            $member_arr = D('Member')->where(array('uid' => array('in', $uid_str)))->select();
            D()->startTrans();
            $res_user = D('User')->where(array('id' => array('in', $uid_str)))->delete();
            $res_member = D('Member')->where(array('uid' => array('in', $uid_str)))->delete();
            //删除会员同时清空会员权限
            $res_group_access = D('auth_group_access')->where(array('uid' => array('in', $uid_str)))->delete();
            if ($res_member===false || $res_user===false|| $res_group_access===false) {
                D()->rollback();
                $this->error('会员删除失败！', '', true);
            } else {
                D()->commit();
                foreach($member_arr as $v){
                    @unlink($v['avatar']);
                }
                $this->success('会员删除成功！', '', true);
            }
        }
    }

    /**
     * 修改状态
     */
    public function actionDoStatus()
    {
        $uid = I('request.id');
        $uid_str = !empty(I('request.id_check_s')) ? I('request.id_check_s') : $uid;
        if ($uid_str) {
            D()->startTrans();
            //该表名称
            $res_user = D()->execute("UPDATE `gdsh_user` SET `status`=abs(`status`-1) WHERE ( `id` IN ('" . $uid_str . "') )");
            $res_member = D()->execute("UPDATE `gdsh_member` SET `status`=abs(`status`-1) WHERE ( `uid` IN ('" . $uid_str . "') )");
            // D('User')->where(array('id' => array('in', $uid_str)))->save(array('status'=>"abs(status-1)"));
            // $res = D('Member')->where(array('uid' => array('in', $uid_str)))->save(array('status'=>array('exp'=>"abs(status-1)")));
            if ($res_user===false || $res_member===false) {
                D()->rollback();
                $this->error('会员状态修改失败！', '', true);
            } else {
                D()->commit();
                $this->success('会员状态修改成功！', '', true);
            }
        }

    }


}

