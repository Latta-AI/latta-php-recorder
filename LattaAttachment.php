<?php

namespace Lattaai\Reporter;
use BrandEmbassy\Memory\MemoryConfiguration;
use BrandEmbassy\Memory\MemoryLimitProvider;
use Exception;

class LattaAttachment {

    private $e;
    private $logs;

    public function __construct(Exception $e, array $logs) {
        $this->e = $e;
        $this->logs = $logs;
    }

    private function getCPULoad() {
        $loads = sys_getloadavg();
        $core_nums = trim(shell_exec("grep -P '^processor' /proc/cpuinfo|wc -l"));
        $load = round($loads[0]/($core_nums + 1)*100, 2);
        return $load;
    }

    private function getRAM() {
        $configuration = new MemoryConfiguration();
        $limitProvider = new MemoryLimitProvider($configuration);

        return [
            "free_memory" => $limitProvider->getLimitInBytes() - memory_get_usage(),
            "total_memory" => $limitProvider->getLimitInBytes()
        ];
    }

    public function toString() {
        $ramValues = $this->getRAM();

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
                    "query" => $_GET,
                    "headers" => getallheaders(),
                    "body" => file_get_contents('php://input')
                ],
                "response" => [
                    "status_code" => 500,
                    "body" => ob_get_contents(),
                    "headers" => []
                ],
                "name" => "Fatal Error",
                "message" => $this->e->getMessage(),
                "stack" => $this->e->getTraceAsString(),
                "environment_variables" => [],
                "system_info" => [
                    "free_memory" => $ramValues["free_memory"],
                    "total_memory" => $ramValues["total_memory"],
                    "cpu_usage" => $this->getCPULoad()
                ],
                "logs" => [
                    "entries" => $this->logs
                ]
            ]
        ];
    }

}