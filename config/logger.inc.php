<?php

require_once('log4php/Logger.php');

class ButigoLoggerAppenderRollingFile extends LoggerAppenderRollingFile {
    public function setFile($file) {
        $logPath = dirname(__FILE__) . '/../log/';
        parent::setFile($logPath . $file);
    }
}

Logger::configure(array(
    'rootLogger' => array(
        'appenders' => array('default'),
        'level' => 'debug'
    ),
    'appenders' => array(
        'default' => array(
            'class' => 'ButigoLoggerAppenderRollingFile',
            'layout' => array(
                'class' => 'LoggerLayoutPattern',
                'params' => array(
                    'conversionPattern' => '[%d][%C.%M @ %L][%p] %m%n%ex'
                )
            ),
            'params' => array(
                'file' => 'butigo.log',
                'maxBackupIndex' => 100
            )
        )
    )
));
