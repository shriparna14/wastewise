<?php
session_start();
require 'config.php';

$inputs = [];
$errors = [];
$success = false;
$defaultLat = 40.7128;
$defaultLng = -74.0060;
$lat = $defaultLat;
$lng = $defaultLng;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inputs['full_name'] = htmlspecialchars(trim($_POST['full_name'] ?? ''));
    $inputs['email'] = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $inputs['phone'] = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? '');
    $inputs['waste_type'] = htmlspecialchars(trim($_POST['waste_type'] ?? ''));
    $inputs['pickup_date'] = htmlspecialchars(trim($_POST['pickup_date'] ?? ''));
    $lat = floatval($_POST['lat'] ?? $defaultLat);
    $lng = floatval($_POST['lng'] ?? $defaultLng);

    // Validation
    $errors = [];
    if (empty($inputs['full_name'])) $errors[] = 'Full name is required';
    if (!filter_var($inputs['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
    if (strlen($inputs['phone']) !== 10) $errors[] = 'Phone must be 10 digits';
    if (!in_array($inputs['waste_type'], ['Recyclables', 'Organic', 'Hazardous'])) $errors[] = 'Invalid waste type';
    if (empty($inputs['pickup_date']) || strtotime($inputs['pickup_date']) < time()) $errors[] = 'Invalid date';

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO pickups 
                (full_name, email, phone, waste_type, pickup_date, location_lat, location_lng, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
            
            $stmt->execute([
                $inputs['full_name'],
                $inputs['email'],
                $inputs['phone'],
                $inputs['waste_type'],
                $inputs['pickup_date'],
                $lat,
                $lng
            ]);
            
            $_SESSION['success'] = true;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } catch(PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Schedule Pickup - WasteWise</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
  <style>
    :root {
      --primary: #2a9d8f;
      --secondary: #e9c46a;
      --accent: #e76f51;
      --dark: #264653;
      --light: #f8f9fa;
      --gradient: linear-gradient(135deg, var(--primary) 0%, #1d7874 100%);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: var(--light);
      color: var(--dark);
      min-height: 100vh;
    }

    .header {
      background: var(--gradient);
      padding: 4rem 2rem;
      color: white;
      text-align: center;
      position: relative;
    }

    .container {
      max-width: 1200px;
      margin: -50px auto 2rem;
      padding: 0 2rem;
    }

    .schedule-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 2rem;
      background: white;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .calendar-section {
      padding: 2rem;
      background: rgba(42,157,143,0.05);
    }

    #calendar {
      background: white;
      border-radius: 15px;
      padding: 1rem;
    }

    .form-section {
      padding: 2rem;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    label {
      display: block;
      margin-bottom: 0.5rem;
      color: var(--dark);
      font-weight: 500;
    }

    input, select {
      width: 100%;
      padding: 1rem;
      border: 2px solid #e0e0e0;
      border-radius: 10px;
      transition: all 0.3s ease;
    }

    input:focus, select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(42,157,143,0.2);
      outline: none;
    }

    #map {
      height: 250px;
      border-radius: 10px;
      margin: 1rem 0;
      border: 2px solid #e0e0e0;
    }

    .btn {
      background: var(--gradient);
      color: white;
      padding: 1rem 2rem;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      transition: transform 0.3s ease;
    }

    .btn:hover {
      transform: translateY(-2px);
    }

    .success-message {
      background: rgba(42,157,143,0.1);
      padding: 2rem;
      border-radius: 15px;
      text-align: center;
      margin: 2rem 0;
    }

    @media (max-width: 768px) {
      .schedule-grid {
        grid-template-columns: 1fr;
      }
      .container {
        margin: 2rem auto;
      }
    }
  </style>
</head>
<body>
  <div class="header">
    <h1>Schedule Your Pickup</h1>
    <p>Help create a cleaner environment</p>
  </div>

  <div class="container">
    <?php if(isset($_SESSION['success'])): ?>
      <div class="success-message">
        <h3>✅ Pickup Scheduled!</h3>
        <p>We'll contact you to confirm details</p>
      </div>
      <?php unset($_SESSION['success']); ?>
    <?php else: ?>
      <div class="schedule-grid">
        <div class="calendar-section">
          <div id='calendar'></div>
        </div>
        
        <div class="form-section">
          <form method="POST" action="">
            <?php if(!empty($errors)): ?>
              <div class="error-message">
                <?php foreach($errors as $error): ?>
                  <p><?= $error ?></p>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <div class="form-group">
              <label>Full Name</label>
              <input type="text" name="full_name" required value="<?= htmlspecialchars($inputs['full_name'] ?? '') ?>">
            </div>

            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" required value="<?= htmlspecialchars($inputs['email'] ?? '') ?>">
            </div>

            <div class="form-group">
              <label>Phone</label>
              <input type="tel" name="phone" pattern="[0-9]{10}" required value="<?= htmlspecialchars($inputs['phone'] ?? '') ?>">
            </div>

            <div class="form-group">
              <label>Waste Type</label>
              <select name="waste_type" required>
                <option value="">Select Type</option>
                <option value="Recyclables" <?= ($inputs['waste_type'] ?? '') === 'Recyclables' ? 'selected' : '' ?>>Recyclables</option>
                <option value="Organic" <?= ($inputs['waste_type'] ?? '') === 'Organic' ? 'selected' : '' ?>>Organic</option>
                <option value="Hazardous" <?= ($inputs['waste_type'] ?? '') === 'Hazardous' ? 'selected' : '' ?>>Hazardous</option>
              </select>
            </div>

            <div class="form-group">
              <label>Pickup Date</label>
              <input type="datetime-local" name="pickup_date" required
                     min="<?= date('Y-m-d\TH:i') ?>"
                     value="<?= htmlspecialchars($inputs['pickup_date'] ?? '') ?>">
            </div>

            <div class="form-group">
              <label>Location</label>
              <div id="map"></div>
              <input type="hidden" name="lat" id="lat" value="<?= $lat ?>">
              <input type="hidden" name="lng" id="lng" value="<?= $lng ?>">
            </div>

            <button type="submit" class="btn">Schedule Pickup</button>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
    // Initialize OpenStreetMap
    let map, marker;
    function initMap() {
      map = L.map('map').setView([<?= $lat ?>, <?= $lng ?>], 13);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
      }).addTo(map);

      marker = L.marker([<?= $lat ?>, <?= $lng ?>], {draggable: true}).addTo(map);
      marker.on('dragend', updatePosition);
      map.on('click', e => updatePosition(e.latlng));
    }

    function updatePosition(e) {
      const latlng = e.latlng || e.target.getLatLng();
      marker.setLatLng(latlng);
      document.getElementById('lat').value = latlng.lat;
      document.getElementById('lng').value = latlng.lng;
    }

    // Initialize Calendar
    document.addEventListener('DOMContentLoaded', () => {
      const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        dateClick: info => {
          document.querySelector('input[name="pickup_date"]').value = info.dateStr + 'T00:00';
        }
      });
      calendar.render();
      initMap();
    });
  </script>
</body>
</html>