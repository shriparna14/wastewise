<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database configuration
$host = "localhost";
$user = "root";
$password = "";
$dbname = "wastewise";

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create tables if not exists
$conn->query("CREATE TABLE IF NOT EXISTS complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(255),
    image VARCHAR(255),
    status VARCHAR(20) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS forum_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    complaint_id INT,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];

    if (isset($_POST['submit_complaint'])) {
        $type = $conn->real_escape_string($_POST['type']);
        $description = $conn->real_escape_string($_POST['description']);
        $location = $conn->real_escape_string($_POST['location'] ?? '');
        $image_path = '';

        // Handle file upload
        if (!empty($_FILES['image']['name'])) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0755, true);
            $file_name = uniqid() . '_' . basename($_FILES['image']['name']);
            $target_file = $target_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = $target_file;
            }
        }

        $stmt = $conn->prepare("INSERT INTO complaints (user_id, type, description, location, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $type, $description, $location, $image_path);
        $stmt->execute();
    }

    if (isset($_POST['submit_comment'])) {
        $complaint_id = $conn->real_escape_string($_POST['complaint_id']);
        $message = $conn->real_escape_string($_POST['message']);
        
        $stmt = $conn->prepare("INSERT INTO forum_posts (user_id, complaint_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $complaint_id, $message);
        $stmt->execute();
    }
}

// Fetch data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM complaints WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$complaints = $stmt->get_result();

$forumPosts = $conn->query("SELECT fp.*, u.username FROM forum_posts fp
                          JOIN users u ON fp.user_id = u.id
                          ORDER BY fp.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Hub - WasteWise</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6C63FF;
            --secondary: #FF6584;
            --accent: #00C9A7;
            --dark: #1A2F38;
            --light: #F8F9FA;
            --gradient: linear-gradient(135deg, #6C63FF 0%, #4A90E2 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: var(--dark);
            color: var(--light);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .particles {
            position: fixed;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .particle {
            position: absolute;
            background: rgba(255,101,132,0.1);
            border-radius: 50%;
            animation: float 8s infinite;
        }

        .container {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .glass-card {
            background: rgba(26, 47, 56, 0.95);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
        }

        input, select, textarea {
            width: 100%;
            padding: 14px 14px 14px 45px;
            background: #233943;
            border: 2px solid #2a4a5a;
            border-radius: 8px;
            color: var(--light);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus, select:focus, textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(108,99,255,0.3);
            outline: none;
        }

        .complaint-card {
            background: rgba(42, 157, 143, 0.1);
            border-left: 4px solid var(--primary);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }

        .complaint-card:hover {
            transform: translateY(-5px);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-pending { background: rgba(255,101,132,0.2); color: #FF6584; }
        .status-in-progress { background: rgba(108,99,255,0.2); color: #6C63FF; }
        .status-resolved { background: rgba(0,201,167,0.2); color: #00C9A7; }

        .forum-post {
            background: rgba(26, 47, 56, 0.9);
            border-left: 3px solid var(--secondary);
            border-radius: 12px;
            padding: 1rem;
            margin: 1rem 0;
            animation: slideIn 0.5s ease;
        }

        .file-preview {
            border-radius: 12px;
            overflow: hidden;
            margin: 1rem 0;
            position: relative;
            transition: transform 0.3s ease;
        }

        .file-preview:hover {
            transform: scale(1.02);
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        @keyframes slideIn {
            from { transform: translateX(20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .btn {
            background: var(--gradient);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108,99,255,0.4);
        }

        .btn::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }

        .logout-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <a href="index.html" class="btn logout-btn">
        <i class="fas fa-sign-out-alt"></i> HOME
    </a>

    <div class="particles">
        <?php for($i=0; $i<15; $i++): ?>
            <div class="particle" style="
                width: <?= rand(20,50) ?>px;
                height: <?= rand(20,50) ?>px;
                top: <?= rand(0,100) ?>%;
                left: <?= rand(0,100) ?>%;
                animation-delay: <?= rand(0,10) ?>s;
            "></div>
        <?php endfor; ?>
    </div>

    <div class="container">
        <!-- Complaint Form -->
        <div class="glass-card" data-aos="fade-up">
            <h2 style="margin-bottom: 1.5rem;">🚨 Report an Issue</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <i class="fas fa-exclamation-triangle form-icon"></i>
                    <select name="type" required>
                        <option value="Missed Pickup">🚛 Missed Pickup</option>
                        <option value="Illegal Dumping">⚠️ Illegal Dumping</option>
                        <option value="General Complaint">📝 General Complaint</option>
                        <option value="Feedback">💡 Feedback</option>
                    </select>
                </div>

                <div class="form-group">
                    <i class="fas fa-comment-dots form-icon"></i>
                    <textarea name="description" rows="4" placeholder="Describe your issue..." required></textarea>
                </div>

                <div class="form-group">
                    <i class="fas fa-map-marker-alt form-icon"></i>
                    <input type="text" name="location" placeholder="Location (optional)">
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem;">
                        <i class="fas fa-camera"></i> Upload Image
                    </label>
                    <input type="file" name="image" accept="image/*" 
                           onchange="previewFile(this)" style="padding-left: 0;">
                    <div class="file-preview" id="filePreview"></div>
                </div>

                <button type="submit" name="submit_complaint" class="btn">
                    <i class="fas fa-paper-plane"></i> Submit Report
                </button>
            </form>
        </div>

        <!-- Complaint List -->
        <div class="glass-card" data-aos="fade-up">
            <h2 style="margin-bottom: 1.5rem;">📋 Your Reports</h2>
            <?php while($complaint = $complaints->fetch_assoc()): ?>
                <div class="complaint-card" data-aos="zoom-in">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3><?= htmlspecialchars($complaint['type']) ?></h3>
                            <p><?= htmlspecialchars($complaint['description']) ?></p>
                            <?php if($complaint['image']): ?>
                                <div class="file-preview">
                                    <img src="<?= htmlspecialchars($complaint['image']) ?>" alt="Issue photo" 
                                         style="width: 100%; border-radius: 8px;">
                                </div>
                            <?php endif; ?>
                            <small style="color: #8b9da7;">
                                Submitted on <?= date('M j, Y', strtotime($complaint['created_at'])) ?>
                            </small>
                        </div>
                        <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $complaint['status'])) ?>">
                            <?= htmlspecialchars($complaint['status']) ?>
                        </span>
                    </div>

                    <!-- Comments Section -->
                    <div style="margin-top: 1.5rem; padding-left: 1rem; border-left: 2px solid var(--secondary);">
                        <?php 
                        $postQuery = $conn->query("SELECT fp.*, u.username FROM forum_posts fp
                                                JOIN users u ON fp.user_id = u.id
                                                WHERE fp.complaint_id = {$complaint['id']}
                                                ORDER BY fp.created_at DESC");
                        while($post = $postQuery->fetch_assoc()): ?>
                            <div class="forum-post">
                                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                                    <strong><?= htmlspecialchars($post['username'] ?? 'Anonymous') ?></strong>
                                    <small style="color: #8b9da7;"><?= date('M j, Y', strtotime($post['created_at'])) ?></small>
                                </div>
                                <p><?= htmlspecialchars($post['message']) ?></p>
                            </div>
                        <?php endwhile; ?>

                        <form method="POST" style="margin-top: 1rem;">
                            <input type="hidden" name="complaint_id" value="<?= $complaint['id'] ?>">
                            <div class="form-group">
                                <i class="fas fa-comment form-icon"></i>
                                <textarea name="message" placeholder="Add your comment..." rows="2" required></textarea>
                            </div>
                            <button type="submit" name="submit_comment" class="btn">
                                <i class="fas fa-comment-dots"></i> Post Comment
                            </button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Community Forum -->
        <div class="glass-card" data-aos="fade-up">
            <h2 style="margin-bottom: 1.5rem;">💬 Community Forum</h2>
            <?php while($post = $forumPosts->fetch_assoc()): ?>
                <div class="forum-post">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 32px; height: 32px; background: var(--gradient); 
                                 border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-user" style="color: white;"></i>
                            </div>
                            <strong><?= htmlspecialchars($post['username'] ?? 'Anonymous') ?></strong>
                        </div>
                        <small style="color: #8b9da7;"><?= date('M j, Y', strtotime($post['created_at'])) ?></small>
                    </div>
                    <p><?= htmlspecialchars($post['message']) ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });

        function previewFile(input) {
            const preview = document.getElementById('filePreview');
            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.innerHTML = `
                    <img src="${e.target.result}" 
                         style="max-width: 100%; border-radius: 8px;" 
                         alt="Image preview">
                `;
            }

            if(file) reader.readAsDataURL(file);
        }

        // Real-time updates
        const eventSource = new EventSource('updates.php');
        eventSource.onmessage = e => {
            const data = JSON.parse(e.data);
            if(data.type === 'complaint_update') {
                const badges = document.querySelectorAll('.status-badge');
                badges.forEach(badge => {
                    if(badge.textContent === data.status) {
                        badge.className = `status-badge status-${data.status.toLowerCase().replace(' ', '-')}`;
                    }
                });
            }
        };
    </script>
</body>
</html>
<?php
$conn->close();
?>