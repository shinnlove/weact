<?php

/**
 * 对log包装一下， 打印notice的log，写到date.ddkf.log的文件里
 */
class ddlog  {
	static function notice($str){
		$logfile = LOG_PATH.date('y_m_d').'.ddkf.log';
		Log::write($str,'NOTIC',3, $logfile);
	}
	static function warn($str){
		$logfile = LOG_PATH.date('y_m_d').'.ddkf.log';
		Log::write($str,'WARN',3, $logfile);
	}
    /*
     * $content 日志内容
     * $type 日志类型0普通日志1警告日志2错误日志
     */
	static function MakeRecord($content,$type)
	{   
	    $logfile =LOG_PATH."/CallBack/";
		
		if (!file_exists($logfile)){ mkdir ($logfile);}
		if (!file_exists($logfile.'Normal/')){ mkdir ($logfile.'Normal/');}
		if (!file_exists($logfile.'Warning/')){ mkdir ($logfile.'Warning/');}
		if (!file_exists($logfile.'Error/')){ mkdir ($logfile.'Error/');}
		
	    switch ($type) {
			case 0:
				$logfile =$logfile.'Normal/'.date('y_m_d').'.log';
				break;
            case 1:
                $logfile =$logfile.'Warning/'.date('y_m_d').'.log';
                break;
            case 2:
                $logfile =$logfile.'Error/'.date('y_m_d').'.log';
                break;
			default:
				break;
		}
        
		Log::write("\n".$content,'NOTIC',3, $logfile);
	}
}
?>