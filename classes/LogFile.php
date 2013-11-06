<?php

class LogFileCore extends LogHandlerCore {
    private static $time;

    protected $file;
    protected $logging = true;

    public $prefix;

    public function __construct($relativePath, $mode = 'a') {
        self::$time = time();

        $this->file = $this->openLogFile($relativePath, $mode);
        $this->addLine("# Message-Begin-" . date("Y-m-d H:i:s", self::$time). "\n");

        return $this;
    }

    public function __destruct() {
        return $this->close();
    }

    public function close() {
        $date = date("Y-m-d H:i:s", self::$time);
        $this->prefix = '';
        $this->addLine("# Message-End-$date\n");
        fclose($this->file);
        return true;
    }

    // Log if condition is True
    public function setCondition($logging) {
        $this->logging = ($logging) ? true : false;
    }

    public function addLog($str) {
        if ($this->logging) {
            fwrite($this->file, $this->prefix. $str);
        }
        return $this;
    }

    public function addLine($str) {
        return $this->addLog("\n" . $this->prefix . $str);
    }

    public function setPrefix($prefix) {
        $this->prefix = $prefix . ' ';
    }
}


?>