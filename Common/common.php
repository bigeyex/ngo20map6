<?php


function check_profanity_words(){
    foreach(C('profanity_words') as $p_word){
        if(strpos($words, $p_word))
                return false;
    }
    return true;
}

function check_model(){
    $model_words = implode('',$_POST);
    return check_profanity_words($model_words);
}

//security check procedure
$getfilter="'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?Select|Update.+?SET|Insert\\s+INTO.+?VALUES|(Select|Delete).+?FROM|(Create|Alter|Drop|TRUNCATE)\\s+(TABLE|DATABASE)" ;  
$postfilter="\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?Select|Update.+?SET|Insert\\s+INTO.+?VALUES|(Select|Delete).+?FROM|(Create|Alter|Drop|TRUNCATE)\\s+(TABLE|DATABASE)" ;  
$cookiefilter="\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?Select|Update.+?SET|Insert\\s+INTO.+?VALUES|(Select|Delete).+?FROM|(Create|Alter|Drop|TRUNCATE)\\s+(TABLE|DATABASE)" ;  
function StopAttack($StrFiltKey,$StrFiltValue,$ArrFiltReq){  
    if(is_array($StrFiltValue))  
    {  
      $StrFiltValue=implode($StrFiltValue);  
    }  
    if (preg_match("/".$ArrFiltReq."/is",$StrFiltValue)==1){  
        print "输入的内容不合适!Improper input!" ;  
        exit();  
    }
} 
foreach($_GET as $key=>$value){  
  StopAttack($key,$value,$getfilter);  
}
foreach($_POST as $key=>$value){  
  StopAttack($key,$value,$postfilter);  
}
foreach($_COOKIE as $key=>$value){  
  StopAttack($key,$value,$cookiefilter);  
}

function auto_charset($fContents, $from='gbk', $to='utf-8')
{
	$from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
	$to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
	if (strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents)))
	{
//如果编码相同或者非字符串标量则不转换
		return $fContents;
	}
	if (is_string($fContents))
	{
		if (function_exists('mb_convert_encoding'))
		{
			return mb_convert_encoding($fContents, $to, $from);
		}
		elseif (function_exists('iconv')) {
			return iconv($from, $to, $fContents);
		}
		else {
			return $fContents;
		}
	}
	elseif (is_array($fContents)) {
		foreach ($fContents as $key => $val)
		{
			$_key = auto_charset($key, $from, $to);
			$fContents[$_key] = auto_charset($val, $from, $to);
			if ($key != $_key) unset($fContents[$key]);
		}
		return $fContents;
	}
	else {
		return $fContents;
	}
}
?>
