<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Db;

// 应用公共文件 
// 

function get_task_all_by_year($year) {
	$result = Db::query("select count(*) as num from task where year(create_time)=?",[2018]);
	return $result[0]['num'];
}

function get_task_finished_per_month_by_groupId($group_id) {
	$result = Db::query("select month(finished_time) month, count(*) count from task where year(finished_time)=year(now()) and year(create_time)=year(now()) and status=1 and group_id=? group by month(finished_time)",[$group_id]);
	return $result;
}

function get_task_all_by_groupId_at_curYear($id) {
	$map['group_id'] = $id;
	$result = Db::table('task')->where($map)->whereTime('create_time', 'year')->count('id');
	return $result;
}

function get_task_finished_by_groupId_at_curYear($id) {
	$map['group_id'] = $id;
	$map['status'] = 1;
	$result = Db::table('task')->where($map)->whereTime('create_time', 'year')->whereTime('finished_time', 'year')->count('id');
	return $result;
}

function get_task_finished_all_div($id) {
	$all = get_task_all_by_groupId_at_curYear($id);
	if (!$all) {
		return 0;
	} else {
		$finished = get_task_finished_by_groupId_at_curYear($id);
		$result = $finished / $all * 1000;
		return round($result, 2);
	}
}

function get_task_finished_per_month_all_div_by_groupId($id) {
	$all = get_task_all_by_groupId_at_curYear($id);
	if (!$all) {
		return [0];
	} else {
		$finished = get_task_finished_per_month_by_groupId($id);
		// $sum = 0;
        // $rate_group = [];
        // foreach ($finished as $item) {
        //     $sum += $item['count'];
        //     $result = ($sum / $all) * 100;
        //     array_push($rate_group, round($result, 2));
        // }
        $sum = 0;
        $max = 0;
        $cur_month = 1;
        $rate_group = [];
        foreach ($finished as $item) {        	
            for ($i = $cur_month; $i < $item['month']; ++$i) {
            	array_push($rate_group, round($max, 2));
            }
        	$sum += $item['count']; 
            $result = ($sum / $all) * 100;
            array_push($rate_group, round($result, 2));
            if ($result > $max) {
            	$max = $result;
            }
            $cur_month = $item['month'] + 1;
        }
        return $rate_group;		
	}

}

// 获取id=$id的用户的分组信息
function get_user_group_list($id)
{
	$result = Db::table('user_group')->alias('ug')->join('__GROUP__ g', 'g.id = ug.group_id')->where('ug.user_id', $id)->field('g.name as g_name, g.id as g_id, ug.status as ug_status')->order('ug.status desc')->select();
	return $result;
}

// 获取组别id为$id的所有用户
function get_group_user_list($id)
{
	$result = Db::table('user_group')->alias('ug')->join('__USER__ u', 'ug.user_id = u.id')->where('ug.group_id', $id)->field('u.nickname, u.status, u.like, u.avatar, u.create_time')->select();
	return $result;
}

// 获取所有用户
function get_group_list($id)
{
	$result = Db::table('group')->select();
	return $result;
}

// 获取所有用户
function get_user_list($id)
{
	$result = Db::table('user')->where('id', $id)->field('nickname, status, like, avatar, create_time')->select();
	return $result;
}

// 获取登录用户的信息
function get_login_user_info()
{
	$id = session('login_id');
	$result = Db::table('user')->where('id', $id)->where('status', 0)->field('id, nickname, avatar, like')->find();
	return $result;
}

// 获取id=$id的帖子相关的信息
function get_user_post_one($id)
{
	$result = Db::table('post')->alias('p')->join('__USER__ u', 'p.user_id = u.id')->where('p.status', 0)->where('u.status', 0)->where('p.id', $id)->field('p.id, p.user_id, p.user_id, p.title, p.content, p.status, p.create_time, p.modified_time, p.like as p_like, p.number, u.nickname, u.like as u_like')->order('p.create_time desc')->find();
	return $result;
}

/* 
	获取帖子列表分页
	过滤掉锁定的帖子以及锁定用户所发的帖子
	如果id为空，查询的是所有的帖子
	不为空，查询的是用户ID为id所发的帖子
*/
function get_user_post_list($id=0)
{
	if (empty($id))
	{
		$result = Db::table('post')->alias('p')->join('__USER__ u', 'p.user_id = u.id')->where('p.status', 0)->where('u.status', 0)->field('p.id, p.user_id, p.title, p.content, p.status, p.create_time, p.modified_time, p.number, p.like as p_like, u.nickname, u.like as u_like')->order('p.modified_time desc')->paginate(10);
	} else {
		$result = Db::table('post')->alias('p')->join('__USER__ u', 'p.user_id = u.id')->where('p.status', 0)->where('u.status', 0)->where('u.id', $id)->field('p.id, p.user_id, p.title, p.content, p.status, p.number, p.create_time, p.modified_time, p.like as p_like, u.nickname, u.like as u_like')->order('p.modified_time desc')->paginate(10);
	}	
	return $result;
}

// 获取帖子回复列表分页
function get_post_comment_list($id)
{
	$result = Db::table('comment')->alias('c')->join('__USER__ u', 'c.user_id = u.id')->where('c.status', 0)->where('c.post_id', $id)->field('c.id as c_id, c.user_id, c.content, c.like as c_like, c.modified_time, u.nickname, u.like as u_like, u.avatar')->order('c.modified_time')->paginate(10);
	return $result;
}

// 获取最新的三条公告
function get_new_announcement()
{

	$result = Db::table('post')->alias('p')->join('__USER__ u', 'p.user_id = u.id')->where('p.status', 0)->where('u.status', 0)->where('is_announcement', 1)->field('p.id, p.user_id, p.title, p.content, p.status, p.number, p.create_time, p.modified_time, p.like as p_like, u.nickname, u.avatar, u.like as u_like')->order('p.modified_time desc')->limit(3)->select();

	return $result;
}

// 获取最新的三条置顶
function get_new_top()
{
	$result = Db::table('post')->alias('p')->join('__USER__ u', 'p.user_id = u.id')->where('p.status', 0)->where('u.status', 0)->where('is_top', 1)->field('p.id, p.user_id, p.title, p.content, p.status, p.number, p.create_time, p.modified_time, p.like as p_like, u.nickname, u.avatar, u.like as u_like')->order('p.modified_time desc')->limit(3)->select();

	return $result;
}

// 获取最新的三条帖子
function get_new_post()
{
	$result = Db::table('post')->alias('p')->join('__USER__ u', 'p.user_id = u.id')->where('p.status', 0)->where('u.status', 0)->field('p.id, p.user_id, p.title, p.content, p.status, p.number, p.create_time, p.modified_time, p.like as p_like, u.nickname, u.avatar, u.like as u_like')->order('p.create_time desc')->limit(3)->select();
	return $result;
}

// 获取评论最多的两个帖子
function get_most_comment_post()
{
	$result = Db::table('post')->alias('p')->join('__USER__ u', 'p.user_id = u.id')->where('p.status', 0)->where('u.status', 0)->field('p.id, p.user_id, p.title, p.content, p.status, p.number, p.create_time, p.modified_time, p.like as p_like, u.nickname, u.avatar, u.like as u_like')->order('p.number desc')->limit(2)->select();
	return $result;
}