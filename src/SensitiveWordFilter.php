<?php

namespace Phpch\SensitiveWord;

use SplFileObject;

class SensitiveWordFilter
{
	private static   $_instance;
	private ?HashMap $words;
	/**
	 * 匹配规则
	 * 1:最小匹配规则，如：敏感词库["中国","中国人"]，语句："我是中国人"，匹配结果：我是[中国]人
	 * 2:最大匹配规则，如：敏感词库["中国","中国人"]，语句："我是中国人"，匹配结果：我是[中国人]
	 *
	 * @var int
	 */
	private int $matchType = 2;
	
	private string $file;
	
	private $iCache = false;
	
	private array $disturbList = [];
	
	private array $filePath = [];
	
	private array $array = [];
	
	private function __construct()
	{
		$this->file = $this->getAuthFilePath();
	}
	
	private function getAuthFilePath(): string
	{
		return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Cache' . DIRECTORY_SEPARATOR . md5('SensitiveWordFilter');
	}
	
	/**
	 * 获取单例
	 *
	 * @return self
	 */
	public static function init(): SensitiveWordFilter
	{
		if (!self::$_instance instanceof self) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * 设置干扰因子
	 *
	 * @param  array  $disturbList
	 * @return $this
	 */
	public function setDisturbList(array $disturbList = []): SensitiveWordFilter
	{
		$this->disturbList = $disturbList;
		return $this;
	}
	
	
	/**
	 * 设置匹配规则为最大
	 * 最小匹配规则，如：敏感词库["中国","中国人"]，语句："我是中国人"，匹配结果：我是[中国]人
	 * 最大匹配规则，如：敏感词库["中国","中国人"]，语句："我是中国人"，匹配结果：我是[中国人]
	 *
	 * @return $this
	 */
	public function setMatchTypeMax(): SensitiveWordFilter
	{
		$this->matchType = 2;
		return $this;
	}
	
	/**
	 * 设置匹配规则为最小
	 * 最小匹配规则，如：敏感词库["中国","中国人"]，语句："我是中国人"，匹配结果：我是[中国]人
	 * 最大匹配规则，如：敏感词库["中国","中国人"]，语句："我是中国人"，匹配结果：我是[中国人]
	 *
	 * @return $this
	 */
	public function setMatchTypeMin(): SensitiveWordFilter
	{
		$this->matchType = 1;
		return $this;
	}
	
	public function setArray(array $sensitiveWords): SensitiveWordFilter
	{
		$this->array = $sensitiveWords;
		return $this;
	}
	
	public function setFilePath($filePath): SensitiveWordFilter
	{
		if (!is_array($filePath)) {
			$filePath = [$filePath];
		}
		
		$this->filePath = $filePath;
		return $this;
	}
	
	/**
	 * 清空缓存
	 *
	 * @return $this
	 */
	public function clearCache(): SensitiveWordFilter
	{
		unlink($this->file);
		return $this;
	}
	
	/**
	 * 判断是否包含敏感字符
	 *
	 * @param  string  $txt
	 * @return bool
	 */
	public function contains(string $txt): bool
	{
		if (empty($txt)) {
			return false;
		}
		
		$len = mb_strlen($txt);
		for ($i = 0; $i < $len; $i++) {
			if ($this->checkSensitiveWord($txt, $i) > 0) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 检查是否包含敏感词,如果存在返回长度,不存在返回0
	 *
	 * @param $txt
	 * @param $index
	 * @return int
	 */
	private function checkSensitiveWord($txt, $index): int
	{
		$tempMap = $this->getWords();
		$matchFlag = 0;
		$len = mb_strlen($txt);
		$flag = false;
		for ($i = $index; $i < $len; $i++) {
			// 获取key
			$word = mb_substr($txt, $i, 1);
			
			if ($this->checkDisturb($word)) {
				$matchFlag++;
				continue;
			}
			
			// 获取指定节点树
			$tempMap = $tempMap->get($word);
			if (!empty($tempMap)) {
				$matchFlag++;
				// 如果为最后一个匹配规则,结束循环，返回匹配标识数
				if (true === $tempMap->get('isEnd')) {
					$flag = true;
					if ($this->matchType == 1) {
						break;
					}
				}
			} else {
				break;
			}
		}
		
		if ($matchFlag < 2 || !$flag) {
			$matchFlag = 0;
		}
		return $matchFlag;
	}
	
	/**
	 * 干扰因子检测
	 *
	 * @param $word
	 * @return bool
	 */
	private function checkDisturb($word): bool
	{
		return in_array($word, $this->disturbList);
	}
	
	/**
	 * 标记
	 *
	 * @author  chenhuan  2023/7/21
	 * @param  string  $txt   文本
	 * @param  string  $sTag  标签开头，如<span>
	 * @param  string  $eTag  标签开头，如</span>
	 * @return string
	 */
	public function mark(string $txt, string $sTag = '<span>', string $eTag = '</span>'): string
	{
		if (empty($txt)) {
			return $txt;
		}
		$badWordList = $this->getBadWord($txt);
		
		// 未检测到敏感词，直接返回
		if (empty($badWordList)) {
			return $txt;
		}
		
		$badWordList = array_unique($badWordList);
		
		foreach ($badWordList as $badWord) {
			$hasReplacedChar = $sTag . $badWord . $eTag;
			$txt = str_replace($badWord, $hasReplacedChar, $txt);
		}
		return $txt;
	}
	
	/**
	 * 获取文字中的敏感词
	 *
	 * @param  string  $txt      文本
	 * @param  int     $wordNum  获取数量默认全部
	 * @return array
	 */
	public function getBadWord(string $txt, int $wordNum = 0): array
	{
		$badWordList = [];
		$txtLen = mb_strlen($txt);
		for ($i = 0; $i < $txtLen; $i++) {
			$len = $this->checkSensitiveWord($txt, $i);
			if ($len > 0) {
				$badWordList[] = mb_substr($txt, $i, $len);
				$i = $i + $len - 1;
				
				if ($wordNum > 0 && count($badWordList) == $wordNum) {
					return $badWordList;
				}
			}
		}
		
		return $badWordList;
	}
	
	/**
	 * 替换敏感字字符
	 *
	 * @param  string  $txt          文本内容
	 * @param  string  $replaceChar  替换字符
	 * @param  bool    $repeat
	 * @return string
	 */
	public function replace(string $txt, string $replaceChar = '*', bool $repeat = true): string
	{
		if (empty($txt)) {
			return $txt;
		}
		$badWordList = $this->getBadWord($txt);
		
		// 未检测到敏感词，直接返回
		if (empty($badWordList)) {
			return $txt;
		}
		$badWordList = array_unique($badWordList);
		
		foreach ($badWordList as $badWord) {
			$hasReplacedChar = $replaceChar;
			if ($repeat) {
				$hasReplacedChar = $this->getReplaceChars($replaceChar, mb_strlen($badWord));
			}
			$txt = str_replace($badWord, $hasReplacedChar, $txt);
		}
		return $txt;
	}
	
	/**
	 * 敏感词替换为对应长度的字符
	 *
	 * @param  string  $replaceChar  替换的字符串
	 * @param  int     $len          长度
	 * @return string
	 */
	private function getReplaceChars(string $replaceChar, int $len): string
	{
		return str_repeat($replaceChar, $len);
	}
	
	/**
	 * 数组构建铭感词树
	 *
	 * @param  array|null  $sensitiveWords
	 * @return void
	 */
	private function setTreeByArray(?array $sensitiveWords = null): void
	{
		if (file_exists($this->file) && $this->iCache) {
			return;
		}
		
		$this->words = $this->words ?? new HashMap();
		
		if (!empty($sensitiveWords)) {
			foreach ($sensitiveWords as $word) {
				$this->buildDFA(trim($word));
			}
		}
	}
	
	/**
	 *  将单个敏感词构建成树结构
	 *
	 * @param  string  $word
	 * @return void
	 */
	private function buildDFA(string $word = ''): void
	{
		if ($word == '') {
			return;
		}
		
		$tree = $this->words ?? new HashMap();
		$wordLength = mb_strlen($word);
		for ($i = 0; $i < $wordLength; $i++) {
			$keyChar = mb_substr($word, $i, 1);
			$treeTmp = $tree->get($keyChar);
			if ($treeTmp) {
				$tree = $treeTmp;
			} else {
				$newTree = new HashMap();
				$newTree->put('isEnd', false);
				
				// 添加到集合
				$tree->put($keyChar, $newTree);
				$tree = $newTree;
			}
			
			// 到达最后一个节点
			if ($i == $wordLength - 1) {
				$tree->put('isEnd', true);
			}
		}
	}
	
	public function buildTree($isCache = false): SensitiveWordFilter
	{
		$this->iCache = $isCache;
		
		if (!empty($this->array)) {
			$this->setTreeByArray($this->array);
		}
		
		if (!empty($this->filePath)) {
			$this->setTreeByFile($this->filePath);
		}
		
		if ($this->iCache && !file_exists($this->file)) {
			$catalog = substr($this->file, 0, strrpos($this->file, DIRECTORY_SEPARATOR));
			if (!is_dir($catalog)) {
				mkdir($catalog);
			}
			file_put_contents($this->file, serialize($this->words));
		}
		
		return $this;
	}
	
	private function getWords()
	{
		if (empty($this->words)) {
			if (file_exists($this->file) && $this->iCache) {
				$this->words =  unserialize(file_get_contents($this->file));
			} else {
				$this->words =  new HashMap();
			}
		}
		
		return $this->words;
	}
	
	/**
	 * 文件构建铭感词树
	 *
	 * @param $filepath
	 * @return void
	 */
	private function setTreeByFile($filepath): void
	{
		if (file_exists($this->file) && $this->iCache) {
			return;
		}
		
		$this->words = $this->words ?? new HashMap();
		if (!is_array($filepath)) {
			$filepath = [$filepath];
		}
		
		foreach ($filepath as $file) {
			if (!file_exists($file)) {
				continue;
			}
			
			$this->readFile($file);
		}
		
	}
	
	private function readFile($file): void
	{
		$fp = new SplFileObject($file, 'rb');
		while (!$fp->eof()) {
			$fp->fseek(0, SEEK_CUR);
			$line = $fp->current(); // 当前行
			
			$line = trim($line);
			$line = explode(",", $line);
			foreach ($line as $v) {
				$this->buildDFA(trim($v));
			}
			
			// 指向下一个，不能少
			$fp->next();
		}
		
		$fp = null;
	}
}
