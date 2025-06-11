<?php
require_once '../includes/db.php';
require_once 'includes/admin_header.php';

try {
    // Initialize variables with default values
    $orders_count = $pending_orders = $products_count = $users_count = 0;
    
    // Fetch statistics with error handling for each query
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
        $orders_count = $stmt->fetch()['total'];
    } catch (PDOException $e) {
        error_log("Error fetching orders count: " . $e->getMessage());
    }

    try {
        $stmt = $pdo->query("SELECT COUNT(*) as pending FROM orders WHERE status = 'menunggu'");
        $pending_orders = $stmt->fetch()['pending'];
    } catch (PDOException $e) {
        error_log("Error fetching pending orders: " . $e->getMessage());
    }

    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
        $products_count = $stmt->fetch()['total'];
    } catch (PDOException $e) {
        error_log("Error fetching products count: " . $e->getMessage());
    }

    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role != 'admin'");
        $users_count = $stmt->fetch()['total'];
    } catch (PDOException $e) {
        error_log("Error fetching users count: " . $e->getMessage());
    }

    // Recent orders with more details and error handling
    try {
        $stmt = $pdo->query("
            SELECT o.*, u.nama as customer_name, u.no_hp,
            GROUP_CONCAT(p.nama SEPARATOR ', ') as products
            FROM orders o 
            JOIN users u ON o.user_id = u.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            LEFT JOIN products p ON oi.product_id = p.id
            GROUP BY o.id
            ORDER BY o.created_at DESC 
            LIMIT 5
        ");
        $recent_orders = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching recent orders: " . $e->getMessage());
        $recent_orders = [];
    }

    // Low stock products with more details and error handling
    try {
        $stmt = $pdo->query("
            SELECT *, 
            CASE 
                WHEN stok = 0 THEN 'habis'
                WHEN stok < 5 THEN 'kritis'
                WHEN stok < 10 THEN 'menipis'
                ELSE 'cukup'
            END as stock_status
            FROM products 
            WHERE stok < 10 
            ORDER BY stok ASC 
            LIMIT 5
        ");
        $low_stock = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching low stock products: " . $e->getMessage());
        $low_stock = [];
    }


} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = 'Terjadi kesalahan saat memuat data.';
}
?>

<!-- Page header -->
<div class="bg-white shadow rounded-lg mb-6">
    <div class="px-6 py-4">
        <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-600">
            Selamat datang kembali, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
        </p>
    </div>
</div>

<!-- Stats grid -->
<div class="grid grid-cols-1 gap-6 mb-6 sm:grid-cols-2 lg:grid-cols-4">
    <!-- Total Orders -->
    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-100 text-red-600">
                <i class="fas fa-shopping-cart text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Pesanan</p>
                <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($orders_count); ?></p>
            </div>
        </div>
    </div>

    <!-- Pending Orders -->
    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <i class="fas fa-clock text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Menunggu Proses</p>
                <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($pending_orders); ?></p>
            </div>
        </div>
    </div>

    <!-- Total Products -->
    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <i class="fas fa-box text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Produk</p>
                <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($products_count); ?></p>
            </div>
        </div>
    </div>

    <!-- Total Users -->
    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class="fas fa-users text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Pengguna</p>
                <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($users_count); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Sales Chart -->
<div class="bg-white rounded-lg shadow mb-6">
<div class="p-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 space-y-3 sm:space-y-0">
            <h2 class="text-lg font-semibold text-gray-900">Grafik Penjualan</h2>
            <div class="flex flex-wrap gap-2">
                <button class="period-filter px-3 py-1 text-sm rounded-full bg-gray-100 hover:bg-gray-200 transition-colors" data-period="1day">1 Hari</button>
                <button class="period-filter px-3 py-1 text-sm rounded-full bg-gray-100 hover:bg-gray-200 transition-colors" data-period="1week">1 Minggu</button>
                <button class="period-filter px-3 py-1 text-sm rounded-full bg-gray-100 hover:bg-gray-200 transition-colors" data-period="1month">1 Bulan</button>
                <button class="period-filter px-3 py-1 text-sm rounded-full bg-gray-100 hover:bg-gray-200 transition-colors" data-period="3month">3 Bulan</button>
                <button class="period-filter px-3 py-1 text-sm rounded-full bg-gray-100 hover:bg-gray-200 transition-colors" data-period="6month">6 Bulan</button>
                <button class="period-filter px-3 py-1 text-sm rounded-full bg-gray-100 hover:bg-gray-200 transition-colors" data-period="1year">1 Tahun</button>
            </div>
        </div>
        <div class="relative h-80">
            <canvas id="salesChart" class="w-full h-full"></canvas>
            <div id="chartLoading" class="absolute inset-0 bg-white bg-opacity-90 flex flex-col items-center justify-center">
                <div class="w-8 h-8 border-4 border-primary border-t-transparent rounded-full animate-spin mb-2"></div>
                <span class="text-sm text-gray-600">Memuat data...</span>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Pesanan Terbaru</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($recent_orders as $order): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            #<?php echo $order['id']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($order['no_hp']); ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 truncate max-w-xs" data-tooltip="<?php echo htmlspecialchars($order['products']); ?>">
                                <?php echo substr($order['products'], 0, 30) . '...'; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            Rp <?php echo number_format($order['total'], 0, ',', '.'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $statusClasses = [
                                'menunggu' => 'bg-yellow-100 text-yellow-800',
                                'diproses' => 'bg-blue-100 text-blue-800',
                                'dikirim' => 'bg-indigo-100 text-indigo-800',
                                'selesai' => 'bg-green-100 text-green-800',
                                'dibatalkan' => 'bg-red-100 text-red-800'
                            ];
                            $statusClass = $statusClasses[$order['status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="manage_orders.php?id=<?php echo $order['id']; ?>" 
                               class="text-primary hover:text-primary-dark" 
                               data-tooltip="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Low Stock Products -->
<div class="bg-white rounded-lg shadow">
    <div class="p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Stok Menipis</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($low_stock as $product): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo htmlspecialchars($product['nama']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo $product['stok']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $stockStatusClasses = [
                                'habis' => 'bg-red-100 text-red-800',
                                'kritis' => 'bg-yellow-100 text-yellow-800',
                                'menipis' => 'bg-orange-100 text-orange-800',
                                'cukup' => 'bg-green-100 text-green-800'
                            ];
                            $statusClass = $stockStatusClasses[$product['stock_status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                <?php echo ucfirst($product['stock_status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                               class="text-primary hover:text-primary-dark"
                               data-tooltip="Edit Produk">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
/* Dashboard Layout */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

/* Card Styles */
.chart-card, .recent-orders, .stock-warning-card {
    background: #fff;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}

.chart-card {
    grid-column: 1 / -1;
}

/* Chart Styles */
.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.chart-header h2 {
    margin: 0;
    font-size: 1.25rem;
    color: var(--text-color);
}

.chart-legend {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #666;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.legend-item:hover {
    background: #fff;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.legend-item i {
    font-size: 0.75rem;
}

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
    margin-top: 1rem;
    background: #fff;
    border-radius: 8px;
    padding: 1rem;
}

.loading-text {
    margin-top: 0.5rem;
    font-size: 0.875rem;
    color: #666;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@media (max-width: 768px) {
    .chart-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .chart-legend {
        width: 100%;
        justify-content: center;
    }
}

/* Empty State */
.empty-state {
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #666;
    padding: 2rem;
    text-align: center;
    background: #f8f9fa;
    border-radius: 8px;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #ddd;
    animation: fadeInUp 0.5s ease;
}

.empty-state p {
    font-size: 1rem;
    margin: 0;
    animation: fadeInUp 0.5s ease 0.2s both;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Loading State */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.95);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    backdrop-filter: blur(2px);
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid var(--primary-color);
    border-radius: 50%;
    animation: spin 1s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Card Styles */
.chart-card, .recent-orders, .stock-warning-card, .stat-card {
    transition: all 0.3s ease;
    background: #fff;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}

.chart-card:hover, .recent-orders:hover, .stock-warning-card:hover, .stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.08);
}

/* Table Styles */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin: 0 -1rem;
    padding: 0 1rem;
}

.table {
    width: 100%;
    border-collapse: collapse;
    white-space: nowrap;
}

.table th, .table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.table th {
    font-weight: 600;
    color: #666;
    background: #f8f9fa;
    position: sticky;
    top: 0;
    z-index: 1;
}

/* Status Badges */
.status-badge, .stock-status {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    display: inline-block;
}

.status-menunggu { background: #fff3cd; color: #856404; }
.status-diproses { background: #cce5ff; color: #004085; }
.status-dikirim { background: #d4edda; color: #155724; }
.status-selesai { background: #d1e7dd; color: #0f5132; }

.stock-status.habis { background: #f8d7da; color: #721c24; }
.stock-status.kritis { background: #fff3cd; color: #856404; }
.stock-status.menipis { background: #cce5ff; color: #004085; }
.stock-status.cukup { background: #d4edda; color: #155724; }

/* Action Buttons */
.btn-action {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #f8f9fa;
    color: #666;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
    margin-right: 0.5rem;
}

.btn-action:hover {
    background: var(--primary-color);
    color: #fff;
}

/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1rem;
    margin: 1rem 0 2rem;
}

.stat-card {
    display: flex;
    flex-direction: column;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.stat-number {
    font-size: clamp(1.5rem, 4vw, 2rem);
    font-weight: 600;
    color: var(--text-color);
    margin: 0.5rem 0;
    line-height: 1.2;
}

.stat-label {
    color: #666;
    font-size: 0.875rem;
}

/* Order Products Truncation */
.order-products {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    cursor: help;
}

/* Responsive Adjustments */
@media (max-width: 1024px) {
    .chart-container {
        height: 250px;
    }
}

@media (max-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
}

@media (max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .chart-card, .recent-orders, .stock-warning-card, .stat-card {
        padding: 1rem;
    }

    .stat-number {
        font-size: 1.25rem;
    }

    .table th, .table td {
        padding: 0.75rem;
        font-size: 0.9rem;
    }

    .order-products {
        max-width: 120px;
    }

    .btn-action {
        width: 28px;
        height: 28px;
    }

    .loading-spinner {
        width: 32px;
        height: 32px;
        border-width: 2px;
    }

    .empty-state i {
        font-size: 2.5rem;
    }

    .empty-state p {
        font-size: 0.9rem;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .chart-container {
        height: 200px;
    }
}
</style>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Global variables
let salesChart = null;
let currentPeriod = '1month';

// Show loading state
const loadingOverlay = document.getElementById('chartLoading');

// Fetch sales data from API
const fetchSalesData = async (period) => {
    try {
        const response = await fetch(`get_sales_data.php?period=${period}`);
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Failed to fetch data');
        }
        
        return data;
    } catch (error) {
        console.error('Error fetching sales data:', error);
        throw error;
    }
};

// Update chart with new data
const updateChart = async (period) => {
    const chartCanvas = document.getElementById('salesChart');
    const ctx = chartCanvas?.getContext('2d');
    
    if (!ctx) return;
    
    // Show loading
    loadingOverlay.style.display = 'flex';
    
    try {
        const salesData = await fetchSalesData(period);
        
        // Destroy existing chart
        if (salesChart) {
            salesChart.destroy();
        }
        
        // Calculate max value for y-axis
        const maxValue = Math.max(...salesData.data.sales);
        const yAxisMax = maxValue > 0 ? Math.ceil(maxValue * 1.1 / 1000000) * 1000000 : 1000000;
        
        // Create new chart
        salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: salesData.data.labels,
                datasets: [{
                    label: 'Total Penjualan',
                    data: salesData.data.sales,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#007bff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                    axis: 'x'
                },
                animation: {
                    duration: 750,
                    easing: 'easeInOutQuart',
                    onComplete: function() {
                        loadingOverlay.style.display = 'none';
                    }
                },
                hover: {
                    mode: 'nearest',
                    intersect: false,
                    animationDuration: 150
                },
                elements: {
                    line: {
                        tension: 0.4
                    },
                    point: {
                        hitRadius: 8
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#000',
                        titleFont: {
                            weight: '600'
                        },
                        bodyColor: '#666',
                        bodyFont: {
                            size: 13
                        },
                        padding: 12,
                        borderColor: 'rgba(0,0,0,0.1)',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: yAxisMax,
                        grid: {
                            color: 'rgba(0,0,0,0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return 'Rp ' + (value / 1000000).toFixed(0) + ' jt';
                                } else if (value >= 1000) {
                                    return 'Rp ' + (value / 1000).toFixed(0) + ' rb';
                                } else {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            },
                            maxTicksLimit: 5,
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0,
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
        
        currentPeriod = period;
        
    } catch (error) {
        console.error('Error updating chart:', error);
        
        // Show error state
        chartCanvas.parentElement.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-exclamation-circle text-red-400"></i>
                <p class="text-gray-600 mt-2">Gagal memuat data grafik</p>
                <button onclick="location.reload()" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">
                    Muat Ulang
                </button>
            </div>
        `;
        loadingOverlay.style.display = 'none';
    }
};

// Initialize period filter buttons
const initPeriodFilters = () => {
    const filterButtons = document.querySelectorAll('.period-filter');
    
    // Set default active button
    const defaultButton = document.querySelector(`[data-period="${currentPeriod}"]`);
    if (defaultButton) {
        defaultButton.classList.add('bg-blue-500', 'text-white');
        defaultButton.classList.remove('bg-gray-100', 'hover:bg-gray-200');
    }
    
    filterButtons.forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            
            const period = button.dataset.period;
            
            // Update button states
            filterButtons.forEach(btn => {
                btn.classList.remove('bg-blue-500', 'text-white');
                btn.classList.add('bg-gray-100', 'hover:bg-gray-200');
            });
            
            button.classList.add('bg-blue-500', 'text-white');
            button.classList.remove('bg-gray-100', 'hover:bg-gray-200');
            
            // Update chart
            await updateChart(period);
        });
    });
};

// Initialize chart and filters
const initDashboard = async () => {
    try {
        // Initialize period filters
        initPeriodFilters();
        
        // Load initial chart data
        await updateChart(currentPeriod);
        
        // Add resize handler for responsive updates
        window.addEventListener('resize', () => {
            if (salesChart) {
                salesChart.resize();
            }
        });
        
    } catch (error) {
        console.error('Error initializing dashboard:', error);
        loadingOverlay.style.display = 'none';
    }
};

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', initDashboard);

// Initialize tooltips
document.querySelectorAll('[data-tooltip]').forEach(element => {
    element.title = element.dataset.tooltip;
});

// Add smooth scrolling for mobile tables
document.querySelectorAll('.table-responsive').forEach(table => {
    let isScrolling = false;
    let startX;
    let scrollLeft;

    table.addEventListener('touchstart', (e) => {
        isScrolling = true;
        startX = e.touches[0].pageX - table.offsetLeft;
        scrollLeft = table.scrollLeft;
    });

    table.addEventListener('touchmove', (e) => {
        if (!isScrolling) return;
        e.preventDefault();
        const x = e.touches[0].pageX - table.offsetLeft;
        const walk = (x - startX) * 2;
        table.scrollLeft = scrollLeft - walk;
    });

    table.addEventListener('touchend', () => {
        isScrolling = false;
    });
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>
