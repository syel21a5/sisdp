<?php
$pythonCmd = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'python' : 'python3';
$scriptPath = 'scripts/python/boe_extractor.py';
$tmpPath = 'test.txt';
$command = escapeshellcmd($pythonCmd) . " " . escapeshellarg($scriptPath) . " " . escapeshellarg($tmpPath) . " 2>&1";
$output = shell_exec($command);
echo "OUT: " . $output . "\n";
