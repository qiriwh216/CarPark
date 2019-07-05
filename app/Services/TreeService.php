<?php

namespace App\Http\Service;

class TreeService {
    /**
     * 数组层级缩进转换
     * @param array $array 源数组
     * @param int   $pid
     * @param int   $level
     * @return array
     */
    public function array2level($array, $pid = 0, $level = 1)
    {
        static $list = [];
        foreach ($array as $v) {
            if ($v['pid'] == $pid) {
                $v['level'] = $level;
                $list[] = $v;
                array2level($array, $v['id'], $level + 1);
            }
        }
        return $list;
    }

    /**
     * 把返回的数据集转换成Tree
     * @param array $list 要转换的数据集
     * @param string $pid parent标记字段
     * @param string $level level标记字段
     * @return array
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
    {
        // 创建Tree
        $tree = array();
        if (is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] = &$list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId = $data[$pid];
                if ($root == $parentId) {
                    $tree[] = &$list[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent = &$refer[$parentId];
                        $parent[$child][] = &$list[$key];
                    }
                }
            }
        }
        return $tree;
    }

    //空数组带_child索引，值为空的
    public function getTree($data, $pId)
    {
        $tree = [];
        foreach ($data as $k => $v) {
            if ($v['pid'] == $pId) {
                //父亲找到儿子
                $v['_child'] = getTree($data, $v['id']);
                $tree[] = $v;
                //unset($data[$k]);
            }
        }
        return $tree;
    }
    /**
     * 将list_to_tree的树还原成列表
     * @param  array $tree  原来的树
     * @param  string $child 孩子节点的键
     * @param  string $order 排序显示的键，一般是主键 升序排列
     * @param  array  $list  过渡用的中间数组，
     * @return array        返回排过序的列表数组
     * @author yangweijie <yangweijiester@gmail.com>
     */
    public function tree_to_list($tree, $child = '_child', $order = 'id', &$list = array())
    {
        if (is_array($tree)) {
            foreach ($tree as $key => $value) {
                $reffer = $value;
                if (isset($reffer[$child])) {
                    unset($reffer[$child]);
                    tree_to_list($value[$child], $child, $order, $list);
                }
                $list[] = $reffer;
            }
            $list = list_sort_by($list, $order, $sortby = 'asc');
        }
        return $list;
    }
}