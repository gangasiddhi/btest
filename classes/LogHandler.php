<?php
abstract class LogHandlerCore {
    protected static $logDir = _PS_LOG_DIR_;

    public static function appendLog($relativePath, $message, $env) {
        if ($env && $env != _BU_ENV_) return;

        $absolutePath = self::$logDir.'/'.$relativePath;
        $date = date("Y-m-d H:i:s");
        $fh = @fopen($absolutePath, 'a');

        if (!$fh) {
            error_log("Log file is not writable : " .$absolutePath);
        } else {
            fwrite($fh, "# Message-Begin-$date\n". $message ."\n# Message-End-$date\n");
            fclose($fh);
        }
    }

    /**
     *   Clear file content and write new content
    */
    public static function write($relativePath, $message, $env) {
        if ($env && $env != _BU_ENV_) return;

        $absolutePath = self::$logDir.'/'.$relativePath;
        $date = date("Y-m-d H:i:s");
        $fh = @fopen($absolutePath, 'w');

        if (!$fh) {
            error_log("Log file is not writable : " .$absolutePath);
        } else {
            fwrite($fh, "# Message-Begin-$date\n". $message ."\n# Message-End-$date\n");
            fclose($fh);
        }
    }

    public static function openLogFile($relativePath, $mode = 'a'){
        $absolutePath = self::$logDir . '/' . $relativePath;

        if (@mkdir(dirname($absolutePath), 0755, true)) {
            error_log('Log directory cannot be created: ' . dirname($absolutePath));
            return false;
        }

        $fh = @fopen($absolutePath, $mode);

        if (!$fh) {
            error_log("Log file is not writable : " . $absolutePath);
            return false;
        }

        return $fh;
    }

}

?>
