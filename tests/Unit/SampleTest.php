<?php

namespace Phpch\SensitiveWord\Test;

use Phpch\SensitiveWord\SensitiveWordFilter;
use PHPUnit\Framework\TestCase;

class SampleTest extends TestCase
{
	public function testTrueAssertsToTrue()
	{
		$a = SensitiveWordFilter::init()
			->setMatchTypeMin()
//			->setMatchTypeMax()
			->setTreeByFile('../words/key2.txt')
			->setTreeByArray(["中国", "中国人"])
			->setDisturbList(['-'])
			->setCache();
		
		
		$this->assertEquals('我是*****', $a->replace('我是中--国人'));
	}
}