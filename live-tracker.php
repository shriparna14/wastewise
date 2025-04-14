<?php
// Database connection
define('DB_SERVER', 'localhost');  // Database host
define('DB_USERNAME', 'root');     // Database username
define('DB_PASSWORD', '');         // Database password (empty if no password)
define('DB_DATABASE', 'wastewise'); // Database name

// Create database connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch scheduled pickups
$sql = "SELECT id, name, address, scheduled_date, status FROM pickup_schedule ORDER BY scheduled_date DESC";
$result = $conn->query($sql);

$statuses = [
    "scheduled" => ["✔", "green"],
    "pending" => ["⏳", "orange"],
    "cancelled" => ["✖", "red"]
];

$pickupData = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $statusIcon = $statuses[$row['status']][0];
        $statusColor = $statuses[$row['status']][1];

        $pickupData[] = [
            'name' => $row['name'],
            'address' => $row['address'],
            'scheduled_date' => $row['scheduled_date'],
            'status' => ucfirst($row['status']),
            'icon' => $statusIcon,
            'color' => $statusColor
        ];
    }
}

// Convert data to JSON for AJAX request
$pickupDataJson = json_encode($pickupData);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Live Pickup Tracker - WasteWise</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f1f5f9;
            padding: 2rem;
        }
        h1 {
            text-align: center;
            color: #047857;
        }
        #tracker-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px 20px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background-color: #047857;
            color: white;
        }
        .status {
            font-weight: bold;
        }
        .green { color: green; }
        .orange { color: orange; }
        .red { color: red; }
    </style>
</head>
<body>

<h1>📦 Live Pickup Tracker</h1>

<table id="tracker-table">
    <thead>
        <tr>
            <th>#</th>
            <th>User Name</th>
            <th>Address</th>
            <th>Scheduled Date</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody id="tracker-body">
        <!-- Data will be populated here -->
    </tbody>
</table>

<script>
// Load the pickup data
function loadTrackerData() {
    const data = <?php echo $pickupDataJson; ?>;
    const body = document.getElementById("tracker-body");
    body.innerHTML = "";

    data.forEach((row, index) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${index + 1}</td>
            <td>${row.name}</td>
            <td>${row.address}</td>
            <td>${row.scheduled_date}</td>
            <td class="status ${row.color}">${row.icon} ${row.status}</td>
        `;
        body.appendChild(tr);
    });
}

// Load initially
loadTrackerData();

// Optionally, you can implement an interval for auto-refreshing (if data changes frequently)
setInterval(loadTrackerData, 5000); // Refresh every 5 seconds
</script>

</body>
</html>
