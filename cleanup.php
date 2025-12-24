<?php
// cleanup.php - Command line cleanup tool

if (php_sapi_name() !== 'cli') {
    die("CLI only\n");
}

require_once __DIR__ . '/src/config/database.php';
require_once __DIR__ . '/src/services/AutoCleanupService.php';

$service = new AutoCleanupService();
$options = getopt("fhs", ["force", "help", "stats"]);

if (isset($options['h']) || isset($options['help'])) {
    echo "Usage: php cleanup.php [options]\n";
    echo "  -f, --force   Force cleanup ALL\n";
    echo "  -s, --stats   Show statistics\n";
    echo "  -h, --help    Show help\n";
    exit(0);
}

if (isset($options['s']) || isset($options['stats'])) {
    $stats = $service->getCleanupStats();
    echo "Expired Bookings: " . $stats['expired_bookings'] . "\n";
    echo "Unavailable Vehicles: " . $stats['unavailable_vehicles'] . "\n";
    exit(0);
}

if (isset($options['f']) || isset($options['force'])) {
    echo "Force cleaning ALL expired bookings...\n";
    $result = $service->forceCleanupAll();
    
    if ($result['success']) {
        echo "✅ Force cleanup completed!\n";
        echo "   Completed: " . $result['completed_bookings'] . " bookings\n";
        echo "   Freed: " . $result['freed_vehicles'] . " vehicles\n";
    } else {
        echo "❌ Error: " . $result['error'] . "\n";
        exit(1);
    }
    exit(0);
}

// Normal cleanup
echo "Running cleanup...\n";
$result = $service->runAutoCleanup(true);

if ($result['success']) {
    if (isset($result['skipped'])) {
        echo "⏭️  Cleanup skipped (ran recently)\n";
    } else {
        echo "✅ Cleanup completed!\n";
        echo "   " . $result['message'] . "\n";
    }
} else {
    echo "❌ Error: " . $result['error'] . "\n";
    exit(1);
}
