<!DOCTYPE>
<html>
<head>
<meta charset="gbk">
<style type="text/css">
	*{font-size:12px;padding:0;margin:0;}
	.red{color:red;}
	.green{color:green;}
	.info{border:1px solid #c0c0c0;margin:20px;padding:20px;}
    .title{padding-bottom:10px;}
	.infolist{margin-top:10px;}
	h1{font-size:18px;}
	h2{font-size:16px;}
	h3{font-size:14px;}
	h4{font-size:12px;}
	h5{font-size:10px;}
	li{list-style:none;}
    .detail{padding-top:10px;border-top:1px solid #c0c0c0;}
</style>
</head>
<body>
	<div class="info">
	<h1><?php echo $title; ?></h1>
	<ul class="infolist">
	<li class="title <?php
	    switch($type) {
	    case 'wrong':
	        echo 'red';break;
	    case 'info';
	        break;
	    case 'right':
	        echo 'green';break;
	    }
	?>"><?php echo $info;?></li>
	<li class="detail"><pre><?php
	        echo $detail;
	    ?></pre></li>
	</ul>
</div>
</body>
</html><?php die;?>
