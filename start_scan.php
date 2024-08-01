<?php
function startProcess($command, &$pid) {
    $descriptorspec = array(
        0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
        1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
        2 => array("pipe", "w")   // stderr is a pipe that the child will write to
    );

    $process = proc_open($command, $descriptorspec, $pipes, null, null);

    if (is_resource($process)) {
        $status = proc_get_status($process);
        $pid = $status['pid'];
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        return true;
    }

    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $directory = $_POST['directory'] ?: __DIR__;
    $directory = escapeshellarg($directory);
    $outputFile = __DIR__ . '/clamscan_output.txt';
    $pidFile = __DIR__ . '/clamscan_pid.txt';
    $quarantineDir = __DIR__ . '/.quarantine';

    if (!is_dir($quarantineDir)) {
        mkdir($quarantineDir, 0777, true);
    }

    $options = isset($_POST['options']) ? implode(' ', array_map('escapeshellarg', $_POST['options'])) : '';

    $cmd = "clamscan --exclude-dir=$quarantineDir -r $directory $options > $outputFile 2>&1";

    if (startProcess($cmd, $pid)) {
        file_put_contents($pidFile, $pid);
        echo json_encode(['status' => 'started']);
    } else {
        echo json_encode(['status' => 'failed']);
    }
}

