<?php
/**
 * install
 * 快速文件夹配置
 */
//INSTALL TAG
$root = dirname(dirname(__FILE__)) . '/';

echo "Create folders and example...<br/>";
umask(0);
@mkdir($root . 'rs/index', 0755,true);
@mkdir($root . 'request', 0755);
@mkdir($root . 'view/html/index', 0755, true);
@mkdir($root . 'view/css', 0755, true);
@mkdir($root . 'view/js', 0755, true);

file_put_contents(mkFile($root.'rs/index/index.php'),'<?php 
class rs_index_index extends antc
{
    function exec(antr $r)
	{
		echo "Hello World";
		$this->noView();//删除将会因为没有模板而报错,模板应该被放在 web目录/view/html/index/index.php
	}
}
');
function mkFile($path)
{
	$fp = fopen($path,'w');
	fclose($fp);
	return $path;
}

// createIndex()
$fn = $root . 'index.php';
if (!file_exists($fn)) {
    $fp = fopen($fn, 'w');
    fwrite($fp, '<?php
	//正式环境请不要定义DEBUG这个常量
	//Hello World 的代码在 web目录/rs/index/index.php,或者试着查找 rs_index_index这个类名
	define("DEBUG",1);
	include ("ant/ant.php");
	ant::getInstance()->run();
?>');
    fclose($fp);
}

$str = file_get_contents(__FILE__);
$str = preg_replace('/\/\/INSTALL TAG/', 'die("installed!");', $str);
file_put_contents(__FILE__, $str);
echo 'Done!<br/>';
echo 'Open <a href="../index.php">main page</a>';

