<?php

use Phpch\SensitiveWord\SensitiveWordFilter;

require_once '../vendor/autoload.php';

$time = microtime(true);
$a = SensitiveWordFilter::init()
	->setMatchTypeMin()
//	->setMatchTypeMax()
//	->setTreeByFile('./words/key2.txt')
//	->setTreeByArray(["中国","中国人"])
	->setDisturbList(['-']);
//	->setCache();

var_dump($a->replace('我是中--国人'));
//$a->clearCache();



var_dump(microtime(true) - $time);