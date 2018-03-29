<?php 

 namespace controllers; 

 class indexController extends \core\Controller { 

 	 public function index(){ 
 		 //echo '<html><body style=\'text-align:center; color:#555;margin-top:120px;\' ><h1>欢迎使用 iframe 框架</h1><p>@长沙异新优网络科技有限责任公司</p></body></html>'; 
 		 
 		 $m = new \core\model('sqlite',null);
 		 $data['name'] = 'test2';
 		 $data['nickname'] = '22222';
 		 $data['pwd']='2222';
 		 $data['status']=0;
 		 //$m->save($data,"users");
 		 $res=$m->select("select * from users");
 		 print_r($res);
	 } 

 } 
 ?>
