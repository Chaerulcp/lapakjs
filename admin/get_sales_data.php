<?php
session_start();
require_once '../includes/db.php';

// Check for admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

try {
    // Get period parameter (default to 1 month)
    $period = $_GET['period'] ?? '1month';
    
    // Define period configurations
    $periodConfigs = [
        '1day' => [
            'interval' => '1 DAY',
            'format' => '%H:00',
            'group_format' => 'DATE_FORMAT(created_at, "%Y-%m-%d %H")',
            'display_format' => 'H:i'
        ],
        '1week' => [
            'interval' => '7 DAY',
            'format' => '%Y-%m-%d',
            'group_format' => 'DATE(created_at)',
            'display_format' => 'd M'
        ],
        '1month' => [
            'interval' => '30 DAY',
            'format' => '%Y-%m-%d',
            'group_format' => 'DATE(created_at)',
            'display_format' => 'd M'
        ],
        '3month' => [
            'interval' => '90 DAY',
            'format' => '%Y-%u',
            'group_format' => 'YEARWEEK(created_at)',
            'display_format' => 'W'
        ],
        '6month' => [
            'interval' => '180 DAY',
            'format' => '%Y-%m',
            'group_format' => 'DATE_FORMAT(created_at, "%Y-%m")',
            'display_format' => 'M Y'
        ],
        '1year' => [
            'interval' => '365 DAY',
            'format' => '%Y-%m',
            'group_format' => 'DATE_FORMAT(created_at, "%Y-%m")',
            'display_format' => 'M Y'
        ]
    ];
    
    // Validate period
    if (!isset($periodConfigs[$period])) {
        throw new Exception('Invalid period specified');
    }
    
    $config = $periodConfigs[$period];
    
    // Build the query based on period
    $sql = "
        SELECT 
            {$config['group_format']} as period_key,
            DATE_FORMAT(created_at, '{$config['format']}') as period_label,
            COUNT(*) as order_count,
            COALESCE(SUM(total), 0) as total_sales,
            MIN(created_at) as period_start
        FROM orders
        WHERE status != 'dibatalkan'
        AND created_at >= DATE_SUB(NOW(), INTERVAL {$config['interval']})
        GROUP BY period_key
        ORDER BY period_key ASC
    ";
    
    $stmt = $pdo->query($sql);
    $rawData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for chart
    $chartData = [];
    $labels = [];
    $salesData = [];
    $orderCounts = [];
    
    foreach ($rawData as $row) {
        // Format label based on period type
        $date = new DateTime($row['period_start']);
        
        switch ($period) {
            case '1day':
                $label = $date->format('H:i');
                break;
            case '1week':
            case '1month':
                $label = $date->format('d M');
                break;
            case '3month':
                $weekNum = $date->format('W');
                $label = "W{$weekNum}";
                break;
            case '6month':
            case '1year':
                $label = $date->format('M Y');
                break;
            default:
                $label = $row['period_label'];
        }
        
        $labels[] = $label;
        $salesData[] = (float)$row['total_sales'];
        $orderCounts[] = (int)$row['order_count'];
    }
    
    // Calculate statistics
    $totalSales = array_sum($salesData);
    $totalOrders = array_sum($orderCounts);
    $avgOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;
    
    // Prepare response
    $response = [
        'success' => true,
        'period' => $period,
        'data' => [
            'labels' => $labels,
            'sales' => $salesData,
            'orders' => $orderCounts
        ],
        'stats' => [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'avg_order_value' => $avgOrderValue,
            'period_label' => getPeriodLabel($period)
        ]
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error fetching sales data: " . $e->getMessage());
    
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Terjadi kesalahan saat memuat data penjualan'
    ]);
}

function getPeriodLabel($period) {
    $labels = [
        '1day' => '24 Jam Terakhir',
        '1week' => '7 Hari Terakhir',
        '1month' => '30 Hari Terakhir',
        '3month' => '3 Bulan Terakhir',
        '6month' => '6 Bulan Terakhir',
        '1year' => '1 Tahun Terakhir'
    ];
    
    return $labels[$period] ?? 'Periode Tidak Dikenal';
}
?>
