<?php
session_start();
include "../includes/db.php";

if(!isset($_SESSION['admin'])){
    header("Location: admin-login.php");
    exit;
}

// Set page title
$pageTitle = "Dashboard - GreenTour Admin";

// Fetch counts for stats
$userCount = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'];
$bookingCount = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings"))['total'];
$hotelCount = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) as total FROM hotels"))['total'];
$destinationCount = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) as total FROM destinations"))['total'];

// Recent bookings
$recentBookings = mysqli_query($conn, "SELECT b.*, u.full_name, h.name as hotel_name 
                                      FROM bookings b 
                                      JOIN users u ON b.user_id = u.user_id 
                                      JOIN hotels h ON b.hotel_id = h.hotel_id 
                                      ORDER BY b.booking_date DESC LIMIT 5");

// Recent users
$recentUsers = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC LIMIT 5");

// Monthly booking stats for chart
$monthlyStats = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $query = "SELECT COUNT(*) as count FROM bookings WHERE DATE_FORMAT(booking_date, '%Y-%m') = '$month'";
    $result = mysqli_fetch_array(mysqli_query($conn, $query));
    $monthlyStats[] = [
        'month' => date('M Y', strtotime($month)),
        'bookings' => $result['count'] ?? 0
    ];
}

// Popular destinations
$popularDestinations = mysqli_query($conn, "
    SELECT d.name, COUNT(b.booking_id) as booking_count 
    FROM destinations d 
    LEFT JOIN hotels h ON d.destination_id = h.destination_id 
    LEFT JOIN bookings b ON h.hotel_id = b.hotel_id 
    GROUP BY d.destination_id 
    ORDER BY booking_count DESC 
    LIMIT 5
");

// Revenue stats (assuming you have a payments table)
$totalRevenue = 0;
$revenueQuery = mysqli_query($conn, "SELECT SUM(amount) as total FROM payments WHERE status = 'completed'");
if ($revenueQuery && mysqli_num_rows($revenueQuery) > 0) {
    $revenueData = mysqli_fetch_assoc($revenueQuery);
    $totalRevenue = $revenueData['total'] ?? 0;
}

// Current month revenue
$currentMonth = date('Y-m');
$currentMonthRevenue = 0;
$monthRevenueQuery = mysqli_query($conn, "SELECT SUM(amount) as total FROM payments WHERE status = 'completed' AND DATE_FORMAT(payment_date, '%Y-%m') = '$currentMonth'");
if ($monthRevenueQuery && mysqli_num_rows($monthRevenueQuery) > 0) {
    $monthRevenueData = mysqli_fetch_assoc($monthRevenueQuery);
    $currentMonthRevenue = $monthRevenueData['total'] ?? 0;
}
// In your admin pages:
$pageTitle = "Manage Users";
$breadcrumb = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php'],
    ['title' => 'Users']
];
include "admin-header.php";
?>

<!-- Your page content here -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GreenTour Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #2e7d32;
            --primary-dark: #1b5e20;
            --primary-light: #4caf50;
            --primary-lighter: #e8f5e9;
            --secondary-color: #f5f5f5;
            --text-color: #333;
            --text-light: #777;
            --white: #ffffff;
            --shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            --shadow-light: 0 2px 8px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f0f4f0;
            color: var(--text-color);
        }

        /* Dashboard Specific Styles */
        .dashboard-content {
            padding: 20px;
        }

        .welcome-banner {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--white);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: var(--shadow);
        }

        .welcome-banner h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .welcome-banner p {
            opacity: 0.9;
            font-size: 16px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            padding: 25px;
            border-radius: 10px;
            box-shadow: var(--shadow-light);
            display: flex;
            align-items: center;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 24px;
        }

        .stat-icon.users {
            background: #e3f2fd;
            color: #1976d2;
        }

        .stat-icon.bookings {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .stat-icon.hotels {
            background: #e8f5e9;
            color: var(--primary-color);
        }

        .stat-icon.destinations {
            background: #fff3e0;
            color: #f57c00;
        }

        .stat-icon.revenue {
            background: #fce4ec;
            color: #c2185b;
        }

        .stat-info h3 {
            font-size: 14px;
            color: var(--text-light);
            margin-bottom: 5px;
        }

        .stat-info .number {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .stat-info .trend {
            font-size: 12px;
            margin-top: 5px;
        }

        .trend.up {
            color: #4caf50;
        }

        .trend.down {
            color: #f44336;
        }

        .charts-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 992px) {
            .charts-section {
                grid-template-columns: 1fr;
            }
        }

        .chart-card {
            background: var(--white);
            padding: 25px;
            border-radius: 10px;
            box-shadow: var(--shadow-light);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-header h3 {
            color: var(--primary-color);
            font-size: 18px;
        }

        .chart-container {
            height: 300px;
            position: relative;
        }

        .recent-activities {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .recent-activities {
                grid-template-columns: 1fr;
            }
        }

        .activity-card {
            background: var(--white);
            padding: 25px;
            border-radius: 10px;
            box-shadow: var(--shadow-light);
        }

        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .activity-header h3 {
            color: var(--primary-color);
            font-size: 18px;
        }

        .view-all {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .view-all:hover {
            text-decoration: underline;
        }

        .activity-list {
            list-style: none;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-lighter);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--primary-color);
            font-weight: bold;
        }

        .activity-details {
            flex: 1;
        }

        .activity-details h4 {
            font-size: 15px;
            margin-bottom: 5px;
        }

        .activity-details p {
            font-size: 13px;
            color: var(--text-light);
        }

        .activity-time {
            font-size: 12px;
            color: var(--text-light);
        }

        .popular-destinations {
            background: var(--white);
            padding: 25px;
            border-radius: 10px;
            box-shadow: var(--shadow-light);
            margin-bottom: 30px;
        }

        .destination-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .destination-item:last-child {
            border-bottom: none;
        }

        .destination-rank {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--primary-lighter);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
            color: var(--primary-color);
        }

        .destination-name {
            flex: 1;
            font-weight: 500;
        }

        .destination-count {
            color: var(--text-light);
            font-size: 14px;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .action-card {
            background: var(--white);
            padding: 25px;
            border-radius: 10px;
            box-shadow: var(--shadow-light);
            text-align: center;
            transition: var(--transition);
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }

        .action-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-lighter);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: var(--primary-color);
            font-size: 24px;
        }

        .action-card h3 {
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .action-card p {
            color: var(--text-light);
            margin-bottom: 15px;
            font-size: 14px;
        }

        .action-btn {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            transition: var(--transition);
        }

        .action-btn:hover {
            background: var(--primary-dark);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .welcome-banner {
                padding: 20px;
            }
            
            .welcome-banner h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

    

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>TOTAL USERS</h3>
                        <div class="number"><?php echo $userCount; ?></div>
                        <div class="trend up">+12% from last month</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon bookings">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3>TOTAL BOOKINGS</h3>
                        <div class="number"><?php echo $bookingCount; ?></div>
                        <div class="trend up">+8% from last month</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon hotels">
                        <i class="fas fa-hotel"></i>
                    </div>
                    <div class="stat-info">
                        <h3>TOTAL HOTELS</h3>
                        <div class="number"><?php echo $hotelCount; ?></div>
                        <div class="trend up">+5% from last month</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon destinations">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>DESTINATIONS</h3>
                        <div class="number"><?php echo $destinationCount; ?></div>
                        <div class="trend up">+3% from last month</div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-section">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Bookings Overview</h3>
                        <select id="chartPeriod" onchange="updateChart()">
                            <option value="6">Last 6 Months</option>
                            <option value="12">Last 12 Months</option>
                        </select>
                    </div>
                    <div class="chart-container">
                        <canvas id="bookingsChart"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Revenue Stats</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="recent-activities">
                <div class="activity-card">
                    <div class="activity-header">
                        <h3>Recent Bookings</h3>
                        <a href="manage-bookings.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <ul class="activity-list">
                        <?php if(mysqli_num_rows($recentBookings) > 0): ?>
                            <?php while($booking = mysqli_fetch_assoc($recentBookings)): ?>
                                <li class="activity-item">
                                    <div class="activity-avatar">
                                        <?php echo strtoupper(substr($booking['full_name'], 0, 1)); ?>
                                    </div>
                                    <div class="activity-details">
                                        <h4><?php echo htmlspecialchars($booking['full_name']); ?></h4>
                                        <p><?php echo htmlspecialchars($booking['hotel_name']); ?></p>
                                    </div>
                                    <div class="activity-time">
                                        <?php echo date('M j', strtotime($booking['booking_date'])); ?>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li class="activity-item">
                                <div class="activity-details">
                                    <p>No recent bookings</p>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="activity-card">
                    <div class="activity-header">
                        <h3>Recent Users</h3>
                        <a href="manage-users.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <ul class="activity-list">
                        <?php if(mysqli_num_rows($recentUsers) > 0): ?>
                            <?php while($user = mysqli_fetch_assoc($recentUsers)): ?>
                                <li class="activity-item">
                                    <div class="activity-avatar">
                                        <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                    </div>
                                    <div class="activity-details">
                                        <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                                    </div>
                                    <div class="activity-time">
                                        <?php echo date('M j', strtotime($user['created_at'])); ?>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li class="activity-item">
                                <div class="activity-details">
                                    <p>No recent users</p>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Popular Destinations -->
            <div class="popular-destinations">
                <div class="activity-header">
                    <h3>Popular Destinations</h3>
                    <a href="manage-destinations.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <?php if(mysqli_num_rows($popularDestinations) > 0): ?>
                    <?php $rank = 1; ?>
                    <?php while($destination = mysqli_fetch_assoc($popularDestinations)): ?>
                        <div class="destination-item">
                            <div class="destination-rank"><?php echo $rank; ?></div>
                            <div class="destination-name"><?php echo htmlspecialchars($destination['name']); ?></div>
                            <div class="destination-count"><?php echo $destination['booking_count']; ?> bookings</div>
                        </div>
                        <?php $rank++; ?>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No destination data available</p>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h3>Add New User</h3>
                    <p>Create a new user account</p>
                    <a href="manage-users.php" class="action-btn">Add User</a>
                </div>

                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-hotel"></i>
                    </div>
                    <h3>Manage Hotels</h3>
                    <p>Add or edit hotel information</p>
                    <a href="manage-hotels.php" class="action-btn">Manage Hotels</a>
                </div>

                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <h3>Add Destination</h3>
                    <p>Create new travel destinations</p>
                    <a href="manage-destinations.php" class="action-btn">Add Destination</a>
                </div>

                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3>View Reports</h3>
                    <p>Detailed analytics and reports</p>
                    <a href="reports.php" class="action-btn">View Reports</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Bookings Chart
        const bookingsCtx = document.getElementById('bookingsChart').getContext('2d');
        const bookingsChart = new Chart(bookingsCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthlyStats, 'month')); ?>,
                datasets: [{
                    label: 'Bookings',
                    data: <?php echo json_encode(array_column($monthlyStats, 'bookings')); ?>,
                    borderColor: '#2e7d32',
                    backgroundColor: 'rgba(46, 125, 50, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Revenue Chart (Doughnut)
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Pending', 'Refunded'],
                datasets: [{
                    data: [75, 15, 10],
                    backgroundColor: [
                        '#2e7d32',
                        '#ff9800',
                        '#f44336'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Function to update chart based on period selection
        function updateChart() {
            // This would typically make an AJAX call to get new data
            // For now, we'll just show an alert
            const period = document.getElementById('chartPeriod').value;
            alert('Loading data for ' + period + ' months. This would update the chart with new data.');
        }

        // Auto-refresh data every 5 minutes
        setInterval(() => {
            // In a real application, this would fetch updated data
            console.log('Auto-refreshing dashboard data...');
        }, 300000);
    </script>
</body>
</html>