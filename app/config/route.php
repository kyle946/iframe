<?php
//判断是否开启 rewrite 模式 ，
if(URL_REWRITE){
	//'array('正则表达式'，'    控制器/操作')'
	    return ;
}else{

	//'array('正则表达式'，'控制器/操作')'
	return array(
	
	);
}
?>