<?php
/**
 * 封装一些常用的工具函数
 */
class ToolUtil
{
	/**
	 * 获取当前Unix时间戳和微秒数
	 */
    public static function getMicrotime()
	{
   		list($usec, $sec) = explode(" ", microtime());
   		return ((float)$usec + (float)$sec);
	}

	/**
	 * 打印数组
	 */
	public static function p($var, $echo=true, $label='<pre>', $strict=false) {
	    $label = ($label === null) ? '' : rtrim($label) . ' ';
	    if (!$strict) {
	        if (ini_get('html_errors')) {
	            $output = print_r($var, true);
	            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
	        } else {
	            $output = $label . print_r($var, true);
	        }
	    } else {
	        ob_start();
	        var_dump($var);
	        $output = ob_get_clean();
	        if (!extension_loaded('xdebug')) {
	            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
	            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
	        }
	    }
	    if ($echo) {
	        echo($output);
	        return null;
	    }else
	        return $output;
	}

	/**
	 * 取得输入目录所包含的所有目录和文件
	 * 以关联数组形式返回
	 */
	public static function deepScanDir($dir)
	{
	    $fileArr = array();
	    $dirArr = array();
	    $dir = rtrim($dir, '//');
	    if(is_dir($dir)){
	        $dirHandle = opendir($dir);
	        while(false !== ($fileName = readdir($dirHandle))){
	            $subFile = $dir . DIRECTORY_SEPARATOR . $fileName;
	            if(is_file($subFile)){
	                $fileArr[] = $subFile;
	            } elseif (is_dir($subFile) && str_replace('.', '', $fileName)!=''){
	                $dirArr[] = $subFile;
	                $arr = deepScanDir($subFile);
	                $dirArr = array_merge($dirArr, $arr['dir']);
	                $fileArr = array_merge($fileArr, $arr['file']);
	            }
	        }
	        closedir($dirHandle);
	    }
	    return array('dir'=>$dirArr, 'file'=>$fileArr);
	}

	/**
	 * 获取文件扩展名
	 */
	public static function getExtension($file)
	{
		return substr(strrchr($file, '.'), 1);
	}

	/**
	 * 读取文件内容
	 */
	public static function readFile($file_path){
		if(file_exists($file_path)){ 
			if($fp=fopen($file_path,"r")){ 
				//读取文件 
				$conn=fread($fp,filesize($file_path)); 
				// //替换字符串 
				// $conn=str_replace("rn","<br/>",$conn); 
				// echo $conn."<br/>"; 
			}else{ 
				return false; 
			} 
		}else{ 
			return false;
		} 
		return $conn;
	}

	/**
	 * 产生1-Z随机数
	 */
	public static function randomkeys($length) {
        $returnStr='';
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        for($i = 0; $i < $length; $i ++) {
            $returnStr .= $pattern {mt_rand ( 0, 61 )}; //生成php随机数
        }
        return $returnStr;
    }
	
}


?>