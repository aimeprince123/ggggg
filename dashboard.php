<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: reader.php");
    exit;
}

// TOTAL PARKING SLOTS
$totalSlots = $pdo->query("SELECT COUNT(*) FROM parking_slots")->fetchColumn();

// OCCUPIED SLOTS
$occupiedSlots = $pdo->query("SELECT COUNT(*) FROM parking_slots WHERE status = 'occupied'")->fetchColumn();

// AVAILABLE SLOTS
$availableSlots = $totalSlots - $occupiedSlots;

// TOTAL CARS
$totalCars = $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn();

// DISPLAY PARKING SLOTS
$slots = $pdo->query("SELECT * FROM parking_slots ORDER BY slot_number")->fetchAll(PDO::FETCH_ASSOC);

// DISPLAY PARKING RECORDS
$records = $pdo->query("
    SELECT 
        parking_records.id,
        cars.plate_number,
        parking_slots.slot_number,
        parking_records.entry_time,
        parking_records.exit_time,
        attendants.name AS attendant_name
    FROM parking_records
    INNER JOIN cars ON parking_records.car_id = cars.id
    INNER JOIN parking_slots ON parking_records.slot_id = parking_slots.id
    LEFT JOIN attendants ON parking_records.attendant_id = attendants.id
    ORDER BY parking_records.id DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// DISPLAY PAYMENTS
$payments = $pdo->query("
    SELECT 
        payments.amount,
        payments.payment_method,
        payments.payment_date,
        cars.plate_number
    FROM payments
    INNER JOIN parking_records ON payments.record_id = parking_records.id
    INNER JOIN cars ON parking_records.car_id = cars.id
    ORDER BY payments.id DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkSmart Car Parking Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="dashboard-container">
        <!-- Sidebar Ibumoso -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <i class="fa-solid fa-car"></i>
                <h2>ParkSmart</h2>
                <span>Car Parking System</span>
            </div>
            
            <ul class="sidebar-menu">
                <li class="active"><a href="#"><i class="fa-solid fa-house"></i> Dashboard</a></li>
                <li><a href="#"><i class="fa-solid fa-square-parking"></i> Parking Slots</a></li>
                <li><a href="#"><i class="fa-solid fa-file-lines"></i> Records</a></li>
                <li><a href="#"><i class="fa-solid fa-credit-card"></i> Payments</a></li>
                <li><a href="#"><i class="fa-solid fa-gear"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>

            <div class="sidebar-footer-card">
                <i class="fa-solid fa-car-side"></i>
                <p>Smart Parking<br>Better Tomorrow</p>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Top Banner Header -->
            <header class="top-banner">
                <div class="banner-text">
                    <h1>Car Parking Dashboard</h1>
                    <p>Welcome, <?= htmlspecialchars($_SESSION["user_name"] ?? "aimeprince") ?></p>
                </div>
                <div class="banner-actions">
                    <a href="logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </div>
            </header>

            <!-- Stat Cards Row (4 Cards) -->
            <section class="stats-row">
                <div class="stat-card blue">
                    <div class="stat-icon"><i class="fa-solid fa-car"></i></div>
                    <div class="stat-info">
                        <h3>Total Slots</h3>
                        <h2><?= $totalSlots ?></h2>
                    </div>
                </div>
                <div class="stat-card red">
                    <div class="stat-icon"><i class="fa-solid fa-car"></i></div>
                    <div class="stat-info">
                        <h3>Occupied</h3>
                        <h2><?= $occupiedSlots ?></h2>
                    </div>
                </div>
                <div class="stat-card green">
                    <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="stat-info">
                        <h3>Available</h3>
                        <h2><?= $availableSlots ?></h2>
                    </div>
                </div>
                <div class="stat-card orange">
                    <div class="stat-icon"><i class="fa-solid fa-car"></i></div>
                    <div class="stat-info">
                        <h3>Total Cars</h3>
                        <h2><?= $totalCars ?></h2>
                    </div>
                </div>
            </section>

            <!-- Middle Section: Parking Slots Grid & System Status -->
            <div class="grid-middle">
                <!-- Parking Slots Grid -->
                <div class="card slots-card">
                    <div class="card-header">
                        <h3><i class="fa-solid fa-square-parking"></i> Parking Slots</h3>
                        <a href="#" class="view-all">View All <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                    
                    <div class="slots-grid">
                        <?php foreach ($slots as $slot): ?>
                            <?php 
                                $status = strtolower($slot["status"]);
                                $statusClass = in_array($status, ["available", "occupied", "maintenance"], true) ? $status : "maintenance";
                            ?>
                            <div class="slot-box <?= htmlspecialchars($statusClass) ?>">
                                <span class="slot-id"><?= htmlspecialchars($slot["slot_number"]) ?></span>
                                <span class="slot-status"><?= htmlspecialchars(ucfirst($status)) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="slot-legend">
                        <span><i class="fa-solid fa-circle" style="color: #10b981;"></i> Available</span>
                        <span><i class="fa-solid fa-circle" style="color: #ef4444;"></i> Occupied</span>
                        <span><i class="fa-solid fa-circle" style="color: #f59e0b;"></i> Maintenance</span>
                    </div>
                </div>

                <!-- System Status Card -->
                <div class="card status-card">
                    <div class="status-header">
                        <h3>System Status</h3>
                        <span class="online-badge"><i class="fa-solid fa-circle"></i> Online</span>
                    </div>
                    <div class="status-body">
                        <div class="circular-progress">
                            <div class="inner-circle">
                                <h2><?= $totalSlots > 0 ? round(($availableSlots / $totalSlots) * 100) : 0 ?>%</h2>
                                <p>Available Slots</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Section: Records & Payments Tables (Side by Side) -->
            <div class="grid-bottom">
                <!-- Recent Parking Records -->
                <div class="card table-card">
                    <div class="card-header">
                        <h3><i class="fa-solid fa-car"></i> Recent Parking Records</h3>
                        <a href="#" class="view-all">View All <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Plate Number</th>
                                    <th>Slot</th>
                                    <th>Entry Time</th>
                                    <th>Exit Time</th>
                                    <th>Attendant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($records)): ?>
                                    <tr><td colspan="5" style="text-align: center; color: #64748b;">No parking records found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($records as $record): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($record["plate_number"]) ?></td>
                                            <td><span class="badge-slot"><?= htmlspecialchars($record["slot_number"]) ?></span></td>
                                            <td><?= htmlspecialchars($record["entry_time"]) ?></td>
                                            <td><?= htmlspecialchars($record["exit_time"] ?? "Still parked") ?></td>
                                            <td><?= htmlspecialchars($record["attendant_name"] ?? "Not assigned") ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Payments -->
                <div class="card table-card">
                    <div class="card-header">
                        <h3><i class="fa-solid fa-credit-card"></i> Recent Payments</h3>
                        <a href="#" class="view-all">View All <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Car Number</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($payments)): ?>
                                    <tr><td colspan="4" style="text-align: center; color: #64748b;">No payments found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($payment["plate_number"]) ?></td>
                                            <td><?= number_format((float)$payment["amount"], 0) ?> RWF</td>
                                            <td><?= htmlspecialchars($payment["payment_method"]) ?></td>
                                            <td><?= htmlspecialchars($payment["payment_date"]) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Page Footer -->
            <footer class="main-footer">
                <p>&copy; 2026 ParkSmart. All rights reserved.</p>
                <p>Designed for a Smarter Parking Experience <i class="fa-solid fa-heart" style="color: #ff3b30;"></i></p>
            </footer>
        </main>
    </div>

</body>
</html>