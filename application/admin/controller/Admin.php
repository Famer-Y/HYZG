<?php
namespace app\admin\controller;

use think\Controller, think\Request, think\Db;

class Admin extends Controller
{
    public function index() {
    	return $this->fetch();
    }

    public function addTask(Request $request) {
        $data = $request->post();
        if (0 == $data['status']) {
        	$result = Db::table('task')->insert($data);
        	if ($result) return $this->fetch('index');
        } elseif (1 == $data['status']) {
        	$data['finished_time'] = date("Y-m-d H:i:s");
        	$result = Db::table('task')->insert($data);
        	if ($result) return $this->fetch('index');
        }               
    }
}
