<?php

return [
    'adminEmail' => 'admin@example.com',
    'dataKey' => 'U2FsdGVkX19eM3Hk',
    'tokenKey' => 'U2FsdGVkX18h+0Ht',

    //权限
    'auth_config' => array(
        'auth_on' => true, //认证开关
        'auth_type' => 1, // 认证方式，1为时时认证；2为登录认证。
        'auth_group' => 'group', //用户组数据表名
        'auth_group_access' => 'group_access', //用户组明细表
        'auth_rule' => 'rule', //权限规则表
        'auth_user' => 'member'//用户信息表
    ),
    'table_prefix' => '21',   //加入前缀名称fc_
    //超级管理员id,拥有全部权限,只要用户uid在这个角色组里的,就跳出认证.可以设置多个值,如array('1','2','3')
    'administrator' => array(1),
    //后台允许访问ip  逗号分隔
    'admin_allow_ip' =>'',
    //用户登录加密key
    'auth_key' => 'UF9;a8%u#^A1,{SosHPpJTVI!QN*OtMB|b0q}"z[', //加密KEY


];
