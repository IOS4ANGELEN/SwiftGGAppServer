<?php

require_once (APPLIB_PATH.'libs/NetUtil.php');
require_once (APPLIB_PATH.'libs/ToolUtil.php');
require_once (APPLIB_PATH.'config/app.inc.php');

header("Content-Type: text/html; charset=UTF-8");

/*
 *  抓取文章
 */
class CatchController extends Controller {

    public function addNewArticle() {
    	// 导入MODEL
    	$articleOpr = Flight::model(INTERFACE_SGARTICLE);
    	$typeOpr    = Flight::model(INTERFACE_SGTYPE);
    	// 解析文章的路径
    	$articleDir = ARTICLE_PATH;
    	// 搜索目录下所有的文件和文件夹
		$rt         = ToolUtil::deepScanDir($articleDir);
		if(count($rt['file']) == 0){
			echo '文章目录有误';
			return;
		}
		// 记录参数
		$articleHandleNumber = 0; // 处理的文章数
		$typeHandleNumber    = 0; // 处理的分类数
		$jumpHandleNumber    = 0; // 不处理的文章数
		// 遍历所有 md 文件的内容
		foreach ($rt['file'] as $key => $value) {
			// 判断是否为 md 文件
			$isWeekly = false;
			if(ToolUtil::getExtension($value) == 'md'){
				echo '正在解析的文件名:' . $value . '<br>';
				// 一个个文件进行读取
				$content = ToolUtil::readFile($value);
				$matches = array();
				$data    = array();
				if($content){
					/* 解析标题 */
					preg_match('/title:\s*([\s\S]+?)\s*\n/',$content,$matches);
					if(!empty($matches[1])) {
						$title = $matches[1];
						// 去掉双引号
						$title = str_replace('"','',$title);
						// 去掉前后空格
						$title = trim($title);
						// 判断数据库中是否存在该名称
						if($articleId = $articleOpr->isExistByTitle($title)){
							$jumpHandleNumber++;
							echo '---->跳出文章名:' . $title .'<br><br>';
							continue;
						}else{
							echo '---->解析文章名:' . $title . '<br><br>';
						}
						preg_match('/每周 Swift 社区问答/',$content,$matches);
						if(!empty($matches)){
							$isWeekly = true;
						}
						// 解析成功的文件名
						//echo '--- title:' . $title . '<br>';
						// 解析文件数累加
					}else{
						// 当无法解析时
						$title = "";
						echo '---->无法解析文章名:' . $title . '<br><br>';
						//echo '无法解析的文件名(titile)' . $value . '<br>';
						continue;
					}
					/* 解析日期 */
					preg_match('/date:([\s\S]+?)\n/' ,$content,$matches);
					if(!empty($matches[1])) {
						$date = $matches[1];
						// 去掉换行
						$date = str_replace("\n",'',$date);
						// 解析成功的文件名
						//echo '--- Date:' . $date . '<br>';
					}else{
						$date = "";
						//echo '无法解析的文件名(date)' . $value . '<br>';
						//continue;
					}
					// 解析标签
					preg_match('/tags: \[([\s\S]+?)\]/' ,$content,$matches);
					if(!empty($matches[1])) {
						$tags = $matches[1];
						// 去掉换行
						$tags = str_replace("\n",'',$tags);
						$tags = str_replace(" ",'',$tags);
						$tags = explode(",",$tags);
						$tags = json_encode($tags);
						// 解析成功的文件名
						// echo '--- tags:' . $tags . '<br>';
					}else{
						$tags = "";
						// echo '无法解析的文件名(tags)' . $value . '<br>';
						// continue;
					}
					// 解析分类
					preg_match('/categories: \[([\s\S]+?)\]\n/' ,$content,$matches);
					if(!empty($matches[1])) {
						$categories = $matches[1];
						// 去掉换行
						$categories = str_replace("\n",'',$categories);
						$categories = str_replace(" ",'',$categories);
						$categories = explode(",",$categories);
						$typeId     = array();
						// 解析成功的文件名
						//echo '--- categories:';
						foreach ($categories as $key => $value) {
						    //echo $value . ' ';
							$ret = $typeOpr->getIdByName($value);
							$typeId[$key] = $ret['id'];
							if($ret['isAddType']) $typeHandleNumber++;
						}
						//echo '<br>';
					}else{
						$typeId = "";
						//echo '无法解析的文件名(categories)' . $value . '<br>';
						//continue;
					}
					// 解析固定链接
					preg_match('/permalink: ([\s\S]+?)\n/' ,$content,$matches);
					if(!empty($matches[1])) {
						$permalink = $matches[1];
						// 去掉换行
						$permalink = str_replace("\n",'',$permalink);
						// 解析成功的文件名
						//echo '--- permalink:' . $permalink . '<br>';
					}else{
						$permalink = "";
						//echo '无法解析的文件名(permalink)' . $value . '<br>';
						//continue;
					}
					// 解析原文链接
					preg_match('/\[原文链接\]\(([\s\S]+?)\)/' ,$content,$matches);
					if(!empty($matches[1])) {
						$originalUrl = $matches[1];
						// 去掉换行
						$originalUrl = str_replace("\n",'',$originalUrl);
						// 解析成功的文件名
						//echo '--- originalUrl:' . $originalUrl . '<br>';
					}else{
						$originalUrl = "";
						//echo '无法解析的文件名(originalUrl)' . $value . '<br>';
						//continue;
					}
					// 解析作者
					preg_match('/作者：\s*([\s\S]+?)\s*，/' ,$content,$matches);
					if(!empty($matches[1]) && !$isWeekly) {
						$author = $matches[1];
						// 去掉换行
						$author = str_replace("\n",'',$author);
						// 去掉空格
						$author = str_replace(" ",'',$author);
						// 解析成功的文件名
						//echo '--- author:' . $author . '<br>';
					}else if($isWeekly){
						$author = "";
						preg_match('/作者：\s*([\s\S]+?)\s*\n/' ,$content,$matches);
						if(!empty($matches)){
							$header = $matches[0];
							preg_match_all('/\[([\s\S]*?)\]/' ,$header,$matches);
							if(!empty($matches)){
								foreach ($matches[1] as $key => $value) {
									if( $key == 0 ) $author .= $value;
									else $author .= '｜' . $value;
								}
							}
						}
					}else{
						$author = "";
						//echo '无法解析的文件名(author)' . $value . '<br>';
						//continue;
					}
					// 解析原文日期
					preg_match('/原文日期：\s*([\s\S]+?)\s*\n/' ,$content,$matches);
					if(!empty($matches[1])) {
						$originalDate = $matches[1];
						// 去掉换行
						$originalDate = str_replace("\n",'',$originalDate);
						// 解析成功的文件名
						//echo '--- originalDate:' . $originalDate . '<br>';
					}else{
						$originalDate = "";
						//echo '无法解析的文件名(originalDate)' . $value . '<br>';
						//continue;
					}
					// 解析译者
					preg_match('/译者：\s*\[\s*([\s\S]+?)\s*\]\s*/' ,$content,$matches);
					if(!empty($matches[1])) {
						$translator = $matches[1];
						// 去掉换行
						$translator = str_replace("\n",'',$translator);
						// 去掉空格
						$translator = str_replace(" ",'',$translator);
						// 替换逗号为|
						$translator = str_replace(",",'｜',$translator);
						// 替换逗号为|
						$translator = str_replace("，",'｜',$translator);
						// 解析成功的文件名
						//echo '--- translator:' . $translator . '<br>';
					}else{
						$translator = "";
						//echo '无法解析的文件名(translator)' . $value . '<br>';
						//continue;
					}
					// 解析校对
					preg_match('/校对：\s*\[\s*([\s\S]+?)\s*\]\s*/' ,$content,$matches);
					if(!empty($matches[1])) {
						$proofreader = $matches[1];
						// 去掉换行
						$proofreader = str_replace("\n",'',$proofreader);
						// 去掉空格
						$translator = str_replace(" ",'',$translator);
						// 解析成功的文件名
						//echo '--- proofreader:' . $proofreader . '<br>';
					}else{
						$proofreader = "";
						//echo '无法解析的文件名(proofreader)' . $value . '<br>';
						//continue;
					}
					// 解析定稿
					preg_match('/定稿：\s*\[\s*([\s\S]+?)\s*\]\s*/' ,$content,$matches);
					if(!empty($matches[1])) {
						$finalization = $matches[1];
						// 去掉换行
						$finalization = str_replace("\n",'',$finalization);
						// 去掉空格
						$translator = str_replace(" ",'',$translator);
						// 解析成功的文件名
						//echo '--- translator:' . $translator . '<br>';
					}else{
						$finalization = "";
						//echo '无法解析的文件名(translator)' . $value . '<br>';
						//continue;
					}
					// 解析描述
					preg_match('/<!--此处开始正文-->\s*([\s\S]+?)\s*\<\!--more--\>/' ,$content,$matches);
					if(!empty($matches[1]) && !$isWeekly) {
						$description = $matches[1];
            $description = str_replace("(/img/",'(https://github.com/SwiftGGTeam/SwiftGGTeam.github.io/tree/master/img/',$description);
					}else if($isWeekly){
						preg_match('/---\s*([\s\S]+?)\s*\<\!--more--\>/' ,$content,$matches);
						if(!empty($matches)){
							$description = $matches[1];
              $description = str_replace('<!--此处开始正文-->\n','',$description);
              $description = str_replace("(/img/",'(https://github.com/SwiftGGTeam/SwiftGGTeam.github.io/tree/master/img/',$description);
						}else{
							$description = $title;
						}
					}else{
						$description = $title;
						// echo '无法解析的文件名(description)' . $value . '<br>';
						// continue;
					}
          // 解析内容
					preg_match('/<!--此处开始正文-->\s*([\d\D]*)/' ,$content,$matches);
          if(!empty($matches[1]) && !$isWeekly) {
						$handleContent = $matches[1];
            $handleContent = str_replace('(/img/','(http://swift.gg/img/',$handleContent);
            $handleContent = str_replace('<!--more-->','',$handleContent);
            $handleContent = str_replace('<!--此处开始正文-->','',$handleContent);
					}else if($isWeekly){
						$handleContent = $title;
						preg_match('/---\s*([\d\D]*)/' ,$content,$matches);
						if(!empty($matches)){
							$handleContent = $matches[1];
              $handleContent = str_replace('(/img/','(http://swift.gg/img/',$handleContent);
              $handleContent = str_replace('<!--more-->','',$handleContent);
              $handleContent = str_replace('<!--此处开始正文-->','',$handleContent);
						}else{
              $jumpHandleNumber++;
							$handleContent = "内容解析失败";
              echo '---->无法解析文章名:' . $title . '<br><br>';
              continue;
						}
					}else{
            $jumpHandleNumber++;
						$handleContent = "内容解析失败";
            echo '---->无法解析文章名:' . $title . '<br><br>';
            continue;
						// echo '无法解析的文件名(description)' . $value . '<br>';
						// continue;
					}
          $articleHandleNumber++;
					// 数据封装
					$articleData = array(
						'type'           => $typeId,
						'tags'           => $tags,
						'title'          => $title,
						'cover_url'      => "",
						'translator'     => $translator,
						'proofreader'    => $proofreader,
						'finalization'   => $finalization,
						'author'         => $author,
						'author_image'   => "",
						'original_date'  => $originalDate,
						'original_url'   => $originalUrl,
						'permalink'      => $permalink,
						'description'    => $description,
						'content'        => $handleContent,
						'stars_number'   => 0,
						'clicked_number' => 0,
						'created_time'   => strtotime($date),
						'updated_time'   => time()
					);
					//ToolUtil::p($articleData);
					$articleId = $articleOpr->addArticle($articleData);
					//usleep(100000);
				}
			}
		}
		echo '一共添加了 ' . $typeHandleNumber . ' 个分类, ' . $articleHandleNumber . ' 篇文章, 跳出文章数:' . $jumpHandleNumber;
    }

    // 更新所有文章
    public function updateAllArticle() {

    	echo '一共更新了 ' . $typeHandleNumber . ' 个分类, ' . $articleHandleNumber . ' 篇文章, 跳出文章数:' . $jumpHandleNumber;
    }

    // 获取文章列表
	public function catchArticleList(){
		$url     = "https://api.github.com/repos/SwiftGGTeam/source/contents/_posts/";
	 	$reponse = NetUtil::cURLHTTPSGet($url,20);
	 	return $reponse;
    }

    // 获取文章信息（单个文章）
    public function catchArticleProperty($fileName){
    	$url     = "https://api.github.com/repos/SwiftGGTeam/source/contents/_posts/" . $fileName ."?ref=master";
	 	$reponse = NetUtil::cURLHTTPSGet($url,20);
	 	return $reponse;
    }

}

?>
