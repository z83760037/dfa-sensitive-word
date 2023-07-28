<?php

namespace Phpch\SensitiveWord\Test;

use Phpch\SensitiveWord\SensitiveWordFilter;
use PHPUnit\Framework\TestCase;

class SampleTest extends TestCase
{
	public function testTreeByFile() {
		$a = SensitiveWordFilter::init()->setFilePath('tests/words/key.txt')->buildTree();

		$this->assertEquals('我是**', $a->replace('我是阿扁'));
	}
	
	public function testTreeByArray() {
		$a = SensitiveWordFilter::init()->setArray(["中国", "中国人"])->buildTree();

		$this->assertEquals('我是***', $a->replace('我是中国人'));
	}

	public function testDisturbList() {
		$a = SensitiveWordFilter::init()->setArray(["中国", "中国人"])->setDisturbList(['-'])->buildTree();

		$this->assertEquals('我是*****', $a->replace('我是中--国人'));
	}

	public function testMatchTypeMin() {
		$a = SensitiveWordFilter::init()->setMatchTypeMin()->setArray(["中国", "中国人"])->setDisturbList(['-'])->buildTree();

		$this->assertEquals('我是****人', $a->replace('我是中--国人'));
	}

	public function testCache() {
		$a = SensitiveWordFilter::init()->setFilePath('tests/words/key.txt')->buildTree(true);

		$this->assertEquals('我是**', $a->replace('我是阿扁'));
	}
	
	public function testMark() {
		$a = SensitiveWordFilter::init()->setFilePath('tests/words/key.txt')->buildTree(true);
		
		$this->assertEquals('我是<span>阿扁</span>', $a->mark('我是阿扁'));
	}
	
	public function testGetBadWord() {
		$handle = SensitiveWordFilter::init()->setFilePath('tests/words/key.txt')->buildTree(true);
		$filterContent = $handle->getBadWord('我是阿扁我借种给阿扁');
		
		$badWords = $handle->getBadWord('我是阿扁我借种给阿扁',2);
		$this->assertEquals('阿扁', $filterContent[0]);
		$this->assertEquals('借种', $badWords[1]);
		$this->assertCount(2, $badWords);
		$this->assertCount(3, $filterContent);
	}
	
	public function testContains()
	{
		$handle = SensitiveWordFilter::init()->setFilePath('tests/words/key.txt')->buildTree(true);
		$filter = $handle->contains('我是阿扁我借种给阿扁');
		$this->assertTrue($filter);
	}
}
