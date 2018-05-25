<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/3
 * Time: 13:55
 */

namespace app\officialnumber\model;


class UserRecommend extends Base
{
    //查询用户数据
    public function getUserData($where,$field){
        return $this->getOne($where,$field);

    }
        //获取用户
    public  function getUser($openid,$app_id){
     return $this->getOne(['openid' => $openid, 'app_id' => $app_id], ["id,openid,nickname,headimgurl,uniq_scene,recommended_num,media_id,media_timestamp,app_id,target_num,type,recommend_user_id,is_subscribe,cat_id"]);
    }
    //更新media_id
    public function updateMediaId($id,$media_id,$time){
       $this->update(["media_id"=>$media_id,"media_timestamp"=>$time],['id'=>$id]);

    }
    //获取推荐人
    public function get_recmmended_num($eventKey,$cat_id,$type){
       return $this->getOne(['uniq_scene'=>$eventKey,'cat_id'=>$cat_id,'type'=>$type],["id,recommended_num,openid,target_num,nickname,app_id,type,cat_id"]);

    }

    //更改用户已推荐人数
    public function change_recommended_num($eventKey,$cat_id,$type,$app_id=''){
        //$this->inc();
        $recommended_user = $this->get_recmmended_num($eventKey,$cat_id,$type);  //获取推荐人已推荐人数,openid,id,target_num(目标人数),app_id
        if(!$recommended_user){
            return false;
        }
        $recommended_id = $recommended_user['id'];//推荐人id
        $recommended_num = $recommended_user['recommended_num'] + 1;
        $update_rs = $this->update(['recommended_num' => $recommended_num], ['id' => $recommended_id]);//更新推荐人推荐人数
        if($recommended_user['recommended_num'] < $recommended_user['target_num']) {
            //如果推荐人数小于目标值flag赋值为0
            //$recommended_num = $recommended_user['recommended_num'] + 1;
            //$update_rs = $this->update(['recommended_num' => $recommended_num], ['id' => $recommended_id]);//更新推荐人推荐人数

            //更新成功，需要更新推荐人的recommended_num，再返回出去
            //$recommended_user['flag'] = 0;
            $recommended_user['recommended_num'] = $recommended_num;
            return $recommended_user;//返回推荐人数据数组

        }else{
           //推荐人推荐人数已满就直接返回
            $recommended_user['flag'] = 1;//加一个标志，表示推荐人数已满
            $recommended_user['recommended_num'] = $recommended_num;
            return $recommended_user;//返回推荐人数据数组
        }

    }
    //保存新用户
    public function save_user($user){
        return $this->save($user);
    }
    //更新用户信息
    public function updateUser($where,$data){
        return $this->addOrUp($where,$data);//更新并返回该条数据
    }
    //某个字段自减1
    public function  decNum($where,$field,$number=1){
        return $this->dec($where,$field,$number);//返回自增id

    }



}