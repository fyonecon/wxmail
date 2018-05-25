<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/3
 * Time: 12:02
 */

namespace app\officialnumber\model;


use think\Model;

class Base extends Model
{
    public function __construct($data = [])
    {
        parent::__construct($data);//调用模型类的构造函数，把要查询的格式数组传入，并转换成数组（如果传入的是对象会转化）
    }
    /**
     * 获取单条数据并返回数组
     * @param string $where
     * @param string $field
     * @return array|null
     */
    public function getOne($where = "", $field = "")
    {
        $rs = $this->where($where)->field($field)->find();
        if($rs){
            return $rs->toArray();//返回数组格式的数据
        }else{
            return null;
        }
    }
    /**
     * 获取多条数据
     * @param string $where
     * @param string $field
     * @return false|\PDOStatement|string|\think\Collection
     */
    //默认降序查询
    public function getAll($where = "", $field = "", $order = ['id' => 'desc'])
    {
        $rs = $this->where($where)->field($field)->order($order)->select();

           return $rs;
    }
    //限制数目查询
    public function getLimit($where = "", $field = "", $limit = "0,30", $order = ['id' => 'desc'])
    {
        $res = $this->where($where)->field($field)->limit($limit)->order($order)->select();
        return $res;
    }

    /**
     * 获取分页
     * @param string $where
     * @param string $field
     * @param int $page
     * @param array $order
     * @return mixed
     */
    public function getList($where = "", $field = "", $page = 30, $order = ['id' => 'desc'])
    {
        $res = $this->where($where)->field($field)->order($order)->paginate($page, false, ['query' => request()->param()]);
        if ($res) {
            $data = $res->toArray();
            $data['page'] = $res->render();
        } else {
            $data = null;
        }
        return $data;
    }

    /**
     * 添加或更新，并反回所有数据
     * @param string $where
     * @param string $data
     * @return array|false|int
     */
    public function addOrUp($where = '', $data = '')
    {
        $rs = $this->save($data,$where);//保存要添加或者修改的数据
        if ($rs) {
            return $this->toArray();
        } else {
            return $rs;
        }
    
    }
    /**
     * 删除
     * @param $where
     * @return int
     * @throws \think\Exception
     */
    public function del($where)
    {
        return $this->where($where)->delete();
    }

    /**
     * 自增
     * @param $where
     * @param $field
     * @param $number
     * @return int|true
     * @throws \think\Exception
     */
    public function inc($where, $field, $number)
    {
        return $this->where($where)->setInc($field, $number);
    }

    /**
     * 自减
     * @param $where
     * @param $field
     * @param $number
     * @return int|true
     * @throws \think\Exception
     */
    public function dec($where, $field, $number)
    {
        return $this->where($where)->setDec($field, $number);
    }


}