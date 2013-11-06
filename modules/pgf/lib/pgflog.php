<?php

  // Log levels
  define("L_OFF", 0); // No log
  define("L_ERR", 1); // Log Errors
  define("L_RQST", 2); // Log Request from GC
  define("L_RESP", 4); // Log Resoponse To Google
  define("L_ERR_RQST", L_ERR | L_RQST);
  define("L_ALL", L_ERR | L_RQST | L_RESP);

class PGFLog {
    var $errorLogFile;
    var $messageLogFile;
    var $logLevel = L_ERR_RQST;

    // SetLogFiles
    function PGFLog($errorLogFile, $messageLogFile, $logLevel=L_ERR_RQST, $die=true){
        $today = date('d-m-Y');
    	$errname = explode(".", $errorLogFile);
    	$msgname = explode(".", $messageLogFile);
    	$errorLogFile = _PS_LOG_DIR_.'/pgf/'.$errname[0].'_'.$today.'.'.$errname[1];
    	$messageLogFile = _PS_LOG_DIR_.'/pgf/'.$msgname[0].'_'.$today.'.'.$msgname[1];
        $this->logLevel = $logLevel;

        if (! $this->errorLogFile = @fopen($errorLogFile, "a") OR ! $this->messageLogFile = @fopen($messageLogFile, "a")) {
            $log = "Log files are not writable:";
            if (! $this->errorLogFile) {
                $log .= "\n$errorLogFile";
            }
            if (! $this->messageLogFile) {
                $log .= "\n$messageLogFile";
            }
            error_log($log);
            fclose($this->errorLogFile);
            fclose($this->messageLogFile);
        }
    }

    function LogError($log){
        if($this->logLevel & L_ERR){
          fwrite($this->errorLogFile,
          sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$log));
          return true;
        }
        return false;
    }

    function LogRequest($log){
        if($this->logLevel & L_RQST){
          fwrite($this->messageLogFile,
           sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$log));
           return true;
        }
        return false;
    }

    function LogResponse($log) {
        if($this->logLevel & L_RESP){
          $this->LogRequest($log);
          return true;
        }
        return false;
    }
}
?>
