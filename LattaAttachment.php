<?php

namespace LattaAi\Recorder;
use Exception;
use stdClass;

class LattaAttachment {

    private $e;
    private $logs;

    public function __construct(Exception $e, array $logs) {
        $this->e = $e;
        $this->logs = $logs;
    }

    private function getCPULoad() {
        if(!function_exists('sys_getloadavg'))
            return 0;

        $loads = sys_getloadavg();
        $core_nums = trim(shell_exec("grep -P '^processor' /proc/cpuinfo|wc -l"));
        $load = round($loads[0]/($core_nums + 1)*100, 2);
        return $load;
    }

    public function toString() {
        $query = [];

        foreach($_GET as $key => $value)
            $query[$key] = $value;

        return [
            "type" => "record",
            "data" => [
                "type" => "request",
                "timestamp" => time(),
                "level" => "ERROR",
                "request" => [
                    "method" => $_SERVER['REQUEST_METHOD'],
                    "url" => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]",
                    "route" => $_SERVER['REQUEST_URI'],
                    "query" => sizeof($query) > 0 ? $query : new stdClass(),
                    "headers" => getallheaders(),
                    "body" => file_get_contents('php://input')
                ],
                "response" => [
                    "status_code" => 500,
                    "body" => ob_get_contents(),
                    "headers" => new stdClass()
                ],
                "name" => "Fatal Error",
                "message" => $this->e->getMessage(),
                "stack" => $this->e->getTraceAsString(),
                "environment_variables" => new stdClass(),
                "system_info" => [
                    "free_memory" => 0,
                    "total_memory" => 0,
                    "cpu_usage" => $this->getCPULoad()
                ],
                "logs" => [
                    "entries" => $this->logs
                ]
            ]
        ];
    }

}