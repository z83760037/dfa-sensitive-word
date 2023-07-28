# dfa-sensitive-word
 php实现基于确定有穷自动机算法的铭感词过滤

### 安装扩展

    composer require phpch/dfa-sensitive-word

#### 如果你需要手动引入

    require './vendor/autoload.php';
    
    use Phpch\SensitiveWord\SensitiveWordFilter;

####  构建敏感词库树
场景一: 可以拿到不同（用户）词库数组
```
 $wordData = array(
        '察象蚂',
        '拆迁灭',
        '车牌隐',
        '成人电',
        '成人卡通',
        ......
    );
    
    $handle = SensitiveWordFilter::init()->setArray($wordData)->buildTree();
```
场景二: 全站使用一套敏感词库
```
    // 获取感词库文件路径
    $wordFilePath = 'tests/words/key2.txt';
    
    $handle = SensitiveWordFilter::init()->setFilePath($wordFilePath)->buildTree();
    
     // 设置缓存后可以避免重复构建铭感词树
      $handle = SensitiveWordFilter::init()->setFilePath($wordFilePath)->buildTree(true);
      
      // 删除缓存
    $handle->clearCache();
```
### 设置干扰因子
    $wordFilePath = 'tests/words/key2.txt';
    $handle = SensitiveWordFilter::init()->setFilePath($wordFilePath)->setDisturbList(['-'])->buildTree();
    $filterContent = $handle->replace('我是中--国人'); // 我是*****
    
### 设置匹配规则
    默认是最大匹配

    // 最小匹配规则
    $wordData = ["中国","中国人"];
    $handle = SensitiveWordFilter::init()->setArray($wordData)->setMatchTypeMin()->buildTree();
    $filterContent = $handle->replace('我是中国人'); // 我是**人

    // 最大匹配规则
    $wordData = ["中国","中国人"];
    $handle = SensitiveWordFilter::init()->setArray($wordData)->setMatchTypeMax()->buildTree();
    $filterContent = $handle->replace('我是中国人'); // 我是***


### 检测是否含有敏感词

    $islegal = $handle->contains($content);

### 敏感词过滤

    // 敏感词替换为*为例（会替换为相同字符长度的*）
    $filterContent = $handle->replace($content, '*');
    
     // 或敏感词替换为***为例
     $filterContent = $handle->replace($content, '***', false);

### 标记敏感词
     $markedContent =  $handle->mark($content, '<mark>', '</mark>');

### 获取文字中的敏感词

    // 获取内容中所有的敏感词
    $sensitiveWordGroup = $handle->getBadWord($content);
    // 仅且获取一个敏感词
    $sensitiveWordGroup =  $handle->getBadWord($content, 1);