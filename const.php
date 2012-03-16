<?php
$GLOBALS['ant'] = array();
$GLOBALS['ant']['core_error'] = array(
    'TEMPLETE_NOT_FOUND'=>array(
        'title'    =>'模板未找到',
        'detail'   =>'访问路径：{$rs}/{$act}'
    ),
    'CONTROLLER_NOT_FOUND'=>array(
        'title'    =>'控制器未找到',
        'detail'   =>'访问路径：{$rs}/{$act}'
    )
);
$GLOBALS['ant']['antr_error'] = array(
    'number'       => '{$name} 需要输入一个数字，但是输入的是({$value})',
    'int'          => '{$name} 需要一个整数,但是输入是 \'{$value}\'',
    'isEmpty'      => '{$name} 不能为空',
    'equal'        => '{$name} 的输入值和指定值不相等',
    'length'       => '{$name} 的长度不符合要求'
);
