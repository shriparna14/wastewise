<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    try {
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            header("Location: index.html");
            exit();
        } else {
            echo "<script>alert('Invalid email or password');</script>";
        }
    } catch(PDOException $e) {
        echo "<script>alert('Login error: ".addslashes($e->getMessage())."');</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - WasteWise</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2a9d8f;
            --secondary-color: #e9c46a;
            --accent-color: #e76f51;
            --dark-color: #1a2f38;
            --light-color: #f8f9fa;
            --gradient-primary: linear-gradient(135deg, #2a9d8f 0%, #1d7874 100%);
            --gradient-secondary: linear-gradient(45deg, #e9c46a 0%, #f4a261 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: var(--dark-color);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .particle {
            position: absolute;
            background: rgba(233, 196, 106, 0.1);
            border-radius: 50%;
            animation: float 8s infinite;
        }

        .hero {
            background: rgba(26, 47, 56, 0.95);
            padding: 2.5rem 3rem;
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            width: 90%;
            max-width: 450px;
            position: relative;
            z-index: 2;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease;
            backdrop-filter: blur(12px);
        }

        .hero:hover {
            transform: translateY(-5px);
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo i {
            font-size: 4rem;
            color: var(--secondary-color);
            text-shadow: 0 0 20px rgba(233, 196, 106, 0.3);
            animation: pulse 2s infinite;
        }

        h2 {
            color: var(--light-color);
            font-size: 2rem;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .tagline {
            color: #8b9da7;
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-box {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
            transition: 0.3s;
        }

        .form-box input {
            width: 100%;
            padding: 14px 14px 14px 45px;
            background: #233943;
            border: 2px solid #2a4a5a;
            border-radius: 8px;
            color: var(--light-color);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-box input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 15px rgba(42, 157, 143, 0.3);
            outline: none;
        }

        button {
            background: var(--gradient-primary);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(42, 157, 143, 0.4);
        }

        button::after {
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

        .features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: 2rem;
        }

        .feature {
            background: rgba(42, 157, 143, 0.1);
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            transition: 0.3s;
            border: 1px solid rgba(42, 157, 143, 0.2);
        }

        .feature:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(42, 157, 143, 0.2);
        }

        .feature i {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .feature span {
            color: var(--light-color);
            font-size: 0.85rem;
            line-height: 1.4;
        }

        .success-message {
            color: #2a9d8f;
            background: #e8f5e9;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
            text-align: center;
            border: 1px solid #2a9d8f;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        @keyframes shine {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }

        @media (max-width: 480px) {
            .hero {
                padding: 1.5rem;
                width: 95%;
            }
            
            .feature span {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
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

    <section class="hero">
        <div class="logo">
            <i class="fas fa-recycle"></i>
        </div>
        
        <h2>WasteWise Pro</h2>
        <p class="tagline">Smart Waste Management Platform</p>

        <?php if(isset($_GET['registration']) && $_GET['registration'] === 'success'): ?>
            <div class="success-message">Registration successful! Please login</div>
        <?php endif; ?>

        <form method="POST" class="form-box">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Enter password" required>
            </div>

            <button type="submit">LOGIN</button>
        </form>

        <div class="features">
            <div class="feature">
                <i class="fas fa-trash-arrow-up"></i>
                <span>Waste Collection Tracking</span>
            </div>
            <div class="feature">
                <i class="fas fa-recycle"></i>
                <span>Recycling Performance Metrics</span>
            </div>
            <div class="feature">
                <i class="fas fa-map-marker-alt"></i>
                <span>Smart Bin Locations</span>
            </div>
            <div class="feature">
                <i class="fas fa-chart-line"></i>
                <span>Carbon Footprint Analytics</span>
            </div>
        </div>

        <p>New user? <a href="register.php">Create account</a></p>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const particles = document.querySelectorAll('.particle');
            particles.forEach(particle => {
                particle.style.setProperty('--x', Math.random() * 100);
                particle.style.setProperty('--y', Math.random() * 100);
            });
        });
    </script>
</body>
</html>