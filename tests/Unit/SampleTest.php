<?php

namespace Phpch\SensitiveWord\Test;

use Phpch\SensitiveWord\SensitiveWordFilter;
use PHPUnit\Framework\TestCase;

class SampleTest extends TestCase
{
	public function testTreeByFile() {
		$a = SensitiveWordFilter::init()->setFilePath('tests/words/key.txt');

		$this->assertEquals('我是**', $a->replace('我是阿扁'));
	}
	
	public function testTreeByArray() {
		$a = SensitiveWordFilter::init()->setArray(["中国", "中国人"]);

		$this->assertEquals('我是***', $a->replace('我是中国人'));
	}

	public function testDisturbList() {
		$a = SensitiveWordFilter::init()->setArray(["中国", "中国人"])->setDisturbList(['-']);

		$this->assertEquals('我是*****', $a->replace('我是中--国人'));
	}

	public function testMatchTypeMin() {
		$a = SensitiveWordFilter::init()->setMatchTypeMin()->setArray(["中国", "中国人"])->setDisturbList(['-']);

		$this->assertEquals('我是****人', $a->replace('我是中--国人'));
	}

	public function testCache() {
		$a = SensitiveWordFilter::init()->setFilePath('tests/words/key.txt')->setCache();

		$this->assertEquals('我是**', $a->replace('我是阿扁'));
	}
	
}