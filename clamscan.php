<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Malware Cleaner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #spinner {
            display: none;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center">
                    <h4>Clamscan Linux Malware Scanner</h4>
                </div>
                <div class="card-body">
                    <?php
                    // Check if the OS is Linux
                    if (PHP_OS_FAMILY !== 'Linux') {
                        echo '<div class="alert alert-danger" role="alert">This script can only run on Linux systems.</div>';
                    } else {
                        // Check if clamscan is installed
                        $clamscanPath = shell_exec('which clamscan');
                        if (empty($clamscanPath)) {
                            echo '<div class="alert alert-danger" role="alert">clamscan is not installed on this system. Please install ClamAV.</div>';
                        } else {
                            // Check if a scan is in progress
                            $pidFile = __DIR__ . '/clamscan_pid.txt';
                            $scanInProgress = false;
                            if (file_exists($pidFile)) {
                                $pid = file_get_contents($pidFile);
                                // TODO: Fix this, pretty sure its broken atm.
                                $result = shell_exec("ps -p $pid");
                                if (strpos($result, $pid) !== false) {
                                    $scanInProgress = true;
                                }
                            }

                            if ($scanInProgress) {
                                echo '<div id="spinner" class="text-center">';
                                echo '<div class="spinner-border" role="status">';
                                echo '<span class="visually-hidden">Loading...</span>';
                                echo '</div>';
                                echo '<p>Scanning in progress. Please wait...</p>';
                                echo '</div>';
                                echo '<pre id="scanResults"></pre>';
                            } else {
                                echo '<form id="scanForm" method="post" action="start_scan.php">';
                                echo '<div class="mb-3">';
                                echo '<label for="directory" class="form-label">Directory Path (optional)</label>';
                                echo '<input type="text" name="directory" id="directory" class="form-control" placeholder="' . htmlentities(__DIR__) . '">';
                                echo '</div>';
                                echo '<div class="mb-3">';
                                echo '<div class="form-check">';
                                echo '<input class="form-check-input" type="checkbox" name="options[]" value="-i" id="optionInfected">';
                                echo '<label class="form-check-label" for="optionInfected">Show only infected files (-i)</label>';
                                echo '</div>';
                                echo '<div class="form-check">';
                                echo '<input class="form-check-input" type="checkbox" name="options[]" value="--move=.quarantine" id="optionMove">';
                                echo '<label class="form-check-label" for="optionMove">Move to quarantine (--move)</label>';
                                echo '</div>';
                                echo '<div class="form-check">';
                                echo '<input class="form-check-input" type="checkbox" name="options[]" value="--remove" id="optionRemove">';
                                echo '<label class="form-check-label" for="optionRemove">Remove infected files (--remove)</label>';
                                echo '</div>';
                                echo '</div>';
                                echo '<button type="submit" name="run_scan" class="btn btn-primary btn-block">Run Malware Scan</button>';
                                echo '</form>';
                                echo '<div id="spinner" class="text-center" style="display:none;">';
                                echo '<div class="spinner-border" role="status">';
                                echo '<span class="visually-hidden">Loading...</span>';
                                echo '</div>';
                                echo '<p>Scanning in progress. Please wait...</p>';
                                echo '</div>';
                                echo '<pre id="scanResults"></pre>';
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        function checkScanStatus() {
            $.get('check_scan.php', function(response) {
                if (response.status === 'completed') {
                    $('#spinner').hide();
                    $('#scanResults').text(response.output);
                } else if (response.status === 'running') {
                    $('#spinner').show();
                    setTimeout(checkScanStatus, 3000); // Check every 3 seconds
                }
            }, 'json');
        }

        // Check if a scan is already in progress when the page loads
        checkScanStatus();

        $('#scanForm').on('submit', function(e) {
            e.preventDefault();
            $('#scanForm').hide();
            $('#spinner').show();
            $.post('start_scan.php', $(this).serialize(), function(data) {
                checkScanStatus();
            });
        });
    });
</script>
</body>
</html>

