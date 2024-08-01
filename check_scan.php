<?php
$pidFile = __DIR__ . '/clamscan_pid.txt';
$outputFile = __DIR__ . '/clamscan_output.txt';

if (file_exists($pidFile)) {
    $pid = file_get_contents($pidFile);
    $result = shell_exec("ps -p $pid");
    
    if (strpos($result, $pid) === false) {
        // Process has completed
        $output = file_get_contents($outputFile);
        unlink($pidFile);
        unlink($outputFile);
        echo json_encode(['status' => 'completed', 'output' => $output]);
    } else {
        // Process is still running
        echo json_encode(['status' => 'running']);
    }
} else {
    echo json_encode(['status' => 'not_started']);
}

