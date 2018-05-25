<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/3
 * Time: 10:51
 */
namespace app\officialnumber\model;

class OfficialApp extends Base
{
    //通过token获取app信息
    public function getAppByToken($token){
        return $this->getOne(['token'=>$token]);//通过token 拿到app数据
        //return $this->getAll();
    }
    public function getAppById($id){
        return $this->getOne(['id'=>$id]);//通过id拿到app数据

    }
    //更新app数据
    public function updateStatus($status,$app_id){
        return $this->update(['status'=>$status],['id'=>$app_id]);
    }
    //获取status为1,type为同类型的一个app
    public function getActiveApp($type){
        return $this->getOne(['status'=>1,'type'=>$type]);//拿一条状态为1的数据

    }


}