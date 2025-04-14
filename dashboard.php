<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch(PDOException $e) {
    die("Error fetching user data: " . $e->getMessage());
}

// Sample data - replace with real database queries
$analyticsData = [
    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    'waste' => [65, 59, 80, 81, 56, 55],
    'recycling' => [28, 48, 40, 19, 86, 27],
    'composition' => [35, 25, 20, 20]
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - WasteWise</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #2a9d8f;
            --secondary-color: #e9c46a;
            --accent-color: #e76f51;
            --dark-color: #1a2f38;
            --light-color: #f8f9fa;
            --glass-bg: rgba(26, 47, 56, 0.95);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: var(--dark-color);
            color: var(--light-color);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .nav-bar {
            background: var(--glass-bg);
            padding: 1rem 2rem;
            border-radius: 15px;
            backdrop-filter: blur(12px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            position: sticky;
            top: 1rem;
            z-index: 1000;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        .main-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .analytics-section {
            display: grid;
            gap: 2rem;
            padding-bottom: 4rem;
        }

        .chart-card {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            border-radius: 15px;
            padding: 2rem;
            opacity: 0;
            transform: translateY(20px);
            transition: 1s;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        .chart-card.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .stat-box {
            background: var(--glass-bg);
            padding: 1.5rem;
            border-radius: 15px;
            position: relative;
            overflow: hidden;
        }

        .stat-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary-color);
        }

        .map-container {
            background: var(--glass-bg);
            border-radius: 15px;
            padding: 1.5rem;
            min-height: 400px;
        }

        .map-grid {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 5px;
        }

        .map-cell {
            background: rgba(42, 157, 143, 0.1);
            aspect-ratio: 1;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .map-cell.active {
            background: var(--primary-color);
        }

        @media (max-width: 1200px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <nav class="nav-bar">
            <div class="brand">
                <i class="fas fa-recycle"></i>
                <h1>WasteWise Analytics</h1>
            </div>
            <div class="user-info">
                <span><?= htmlspecialchars($user['username']) ?></span>
                <a href="index.html" class="btn">Home</a>
            </div>
        </nav>

        <div class="main-grid">
            <div class="analytics-section">
                <!-- Route Map -->
                <div class="chart-card scroll-animate">
                    <h3>Collection Route Map</h3>
                    <div class="map-container">
                        <div class="map-grid">
                            <?php for($i=0; $i<100; $i++): ?>
                                <div class="map-cell <?= rand(0,100) > 70 ? 'active' : '' ?>"></div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <!-- Statistics Grid -->
                <div class="stats-grid scroll-animate">
                    <div class="stat-box">
                        <h4>Total Collections</h4>
                        <h2>1,234</h2>
                        <div class="progress" style="width: 65%"></div>
                    </div>
                    <div class="stat-box">
                        <h4>Recycling Rate</h4>
                        <h2>78%</h2>
                        <div class="progress" style="width: 78%"></div>
                    </div>
                </div>

                <!-- Analytics Charts -->
                <div class="chart-card scroll-animate">
                    <h3>Waste Collection Trends</h3>
                    <canvas id="trendChart"></canvas>
                </div>

                <div class="chart-card scroll-animate">
                    <h3>Waste Composition</h3>
                    <canvas id="doughnutChart"></canvas>
                </div>

                <div class="chart-card scroll-animate">
                    <h3>Recycling Performance</h3>
                    <canvas id="barChart"></canvas>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="analytics-section">
                <div class="chart-card scroll-animate">
                    <h3>Real-time Metrics</h3>
                    <div class="stats-grid">
                        <div class="stat-box">
                            <h4>Today's Collection</h4>
                            <h2>85%</h2>
                        </div>
                        <div class="stat-box">
                            <h4>Carbon Saved</h4>
                            <h2>12.4T</h2>
                        </div>
                    </div>
                </div>

                <div class="chart-card scroll-animate">
                    <h3>Waste Type Distribution</h3>
                    <canvas id="polarChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize Charts
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($analyticsData['labels']) ?>,
                datasets: [{
                    label: 'Total Waste',
                    data: <?= json_encode($analyticsData['waste']) ?>,
                    borderColor: '#e76f51',
                    tension: 0.4
                }]
            }
        });

        const doughnutCtx = document.getElementById('doughnutChart').getContext('2d');
        new Chart(doughnutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Organic', 'Plastic', 'Metal', 'Paper'],
                datasets: [{
                    data: <?= json_encode($analyticsData['composition']) ?>,
                    backgroundColor: ['#2a9d8f', '#e9c46a', '#e76f51', '#8ab17d']
                }]
            }
        });

        const barCtx = document.getElementById('barChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($analyticsData['labels']) ?>,
                datasets: [{
                    label: 'Recycling Rate',
                    data: <?= json_encode($analyticsData['recycling']) ?>,
                    backgroundColor: '#2a9d8f'
                }]
            }
        });

        // Scroll Animation
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.scroll-animate').forEach(el => observer.observe(el));

        // Map Interactions
        document.querySelectorAll('.map-cell').forEach(cell => {
            cell.addEventListener('mouseover', () => {
                cell.style.transform = 'scale(1.2)';
            });
            cell.addEventListener('mouseout', () => {
                cell.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>