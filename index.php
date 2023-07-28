<?php

use Phpch\SensitiveWord\SensitiveWordFilter;

require_once './vendor/autoload.php';

$time = microtime(true);
//$a = SensitiveWordFilter::init()
//	->setMatchTypeMin()
////	->setMatchTypeMax()
////	->setTreeByFile('./words/key2.txt')
//	->setTreeByArray(["中国","中国人"])
//	->setDisturbList(['-']);
//	->setCache();

$a = SensitiveWordFilter::init()->setFilePath('./tests/words/key2.txt')->setCache();

var_dump($a->replace('我是阿扁'));
//$a->clearCache();

var_dump(microtime(true) - $time);