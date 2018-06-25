<?php
namespace app\index\controller;

use think\Controller;
use think\Db;

class Index extends Controller
{
    public function index() {
    	$group_3d = get_task_finished_all_div(1);
    	$group_ht = get_task_finished_all_div(2);
    	$group_cv = get_task_finished_all_div(3);
    	$group_hq = get_task_finished_all_div(4);        
    	$all = ($group_3d + $group_cv + $group_ht + $group_hq) / 4;
        $rate_group_3d = get_task_finished_per_month_all_div_by_groupId(1);
        $rate_group_ht = get_task_finished_per_month_all_div_by_groupId(2);
        $rate_group_cv = get_task_finished_per_month_all_div_by_groupId(3);
        $rate_group_hq = get_task_finished_per_month_all_div_by_groupId(4);
    	$this->assign('group_3d', $group_3d);
    	$this->assign('group_ht', $group_ht);
    	$this->assign('group_cv', $group_cv);
    	$this->assign('group_hq', $group_hq);
    	$this->assign('all', $all);
        $this->assign('rate_group_3d', json_encode($rate_group_3d));
        $this->assign('rate_group_ht', json_encode($rate_group_ht));
        $this->assign('rate_group_cv', json_encode($rate_group_cv));
        $this->assign('rate_group_hq', json_encode($rate_group_hq));
    	return $this->fetch();
    }

    public function get_finished_by_month() {
        $rate_group_3d = get_task_finished_per_month_all_div_by_groupId(1);
        $rate_group_ht = get_task_finished_per_month_all_div_by_groupId(2);
        $rate_group_cv = get_task_finished_per_month_all_div_by_groupId(3);
        $rate_group_hq = get_task_finished_per_month_all_div_by_groupId(4);
        echo json_encode($rate_group_3d);
        echo json_encode($rate_group_ht);
        echo json_encode($rate_group_cv);
        echo json_encode($rate_group_hq);
        return ;
    }
}
