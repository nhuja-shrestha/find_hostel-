<?php
session_start();
require_once('../../config/db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$student_name = $_SESSION['name'] ?? 'Student';
$success = "";
$error = "";

/* ===============================
   HANDLE CANCEL RESERVATION
================================ */

if (isset($_GET['action']) && isset($_GET['id'])) {
    $reservation_id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action == 'cancel') {
        // Verify the reservation belongs to this student
        $check = $conn->query("
            SELECT r.id, r.status, r.hostel_id
            FROM reservations r
            WHERE r.id = $reservation_id AND r.user_id = $user_id AND r.status = 'pending'
        ");

        if ($check && $check->num_rows > 0) {
            $res_data = $check->fetch_assoc();
            
            // Cancel the reservation
            $conn->query("DELETE FROM reservations WHERE id = $reservation_id");

            // Add back the room count since reservation is cancelled
            $conn->query("
                UPDATE hostels
                SET rooms = rooms + 1
                WHERE hostel_id = {$res_data['hostel_id']}
            ");

            $success = "Reservation cancelled successfully.";
        } else {
            $error = "Cannot cancel this reservation. Only pending reservations can be cancelled.";
        }
    }
}

/* ===============================
   FETCH STUDENT RESERVATIONS
================================ */

$reservations = $conn->query("
    SELECT 
        r.id,
        r.status,
        r.reserved_at,
        r.hostel_id,
        h.name AS hostel_name,
        h.location,
        h.price,
        h.description,
        u.name AS owner_name,
        u.email AS owner_email,
        u.phone AS owner_phone
    FROM reservations r
    JOIN hostels h ON r.hostel_id = h.hostel_id
    JOIN users u ON h.owner_id = u.id
    WHERE r.user_id = $user_id
    ORDER BY r.reserved_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>My Reservations</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .reservation-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid var(--accent, #007bff);
            transition: all 0.3s ease;
        }

        .reservation-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .hostel-title {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            margin: 0;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .pending {
            background: #fff3cd;
            color: #856404;
        }

        .approved {
            background: #d4edda;
            color: #155724;
        }

        .rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .card-body {
            margin-bottom: 15px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #666;
            font-weight: 600;
        }

        .info-value {
            color: #333;
        }

        .owner-info {
            background: #f9f9f9;
            padding: 12px;
            border-radius: 6px;
            margin: 15px 0;
            font-size: 14px;
        }

        .owner-info strong {
            color: var(--accent, #007bff);
        }

        .description {
            color: #666;
            font-size: 14px;
            padding: 10px 0;
            line-height: 1.5;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .btn {
            padding: 8px 14px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-block;
        }

        .btn-cancel {
            background: #dc3545;
            color: white;
        }

        .btn-cancel:hover {
            background: #c82333;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(220,53,69,0.3);
        }

        .btn-disabled {
            background: #6c757d;
            color: white;
            cursor: not-allowed;
        }

        .btn-disabled:hover {
            background: #6c757d;
            transform: none;
        }

        .btn-back {
            background: var(--accent, #007bff);
            color: white;
            margin-top: 20px;
        }

        .btn-back:hover {
            background: #0056b3;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,123,255,0.3);
        }

        .alert {
            padding: 14px 16px;
            margin-bottom: 20px;
            border-radius: 6px;
            border-left: 4px solid;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }

        .no-data {
            background: white;
            padding: 40px;
            text-align: center;
            border-radius: 8px;
            color: #666;
            margin-top: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .muted {
            color: #999;
            font-size: 13px;
        }

        .status-icon {
            font-size: 20px;
            margin-right: 8px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        header h2 {
            margin: 0;
            color: var(--accent, #007bff);
        }

        .status-message {
            margin-top: 10px;
            padding: 10px;
            border-radius: 4px;
            font-size: 13px;
        }

        .status-message.approved-msg {
            background: #d4edda;
            color: #155724;
            border-left: 3px solid #28a745;
        }

        .status-message.rejected-msg {
            background: #f8d7da;
            color: #721c24;
            border-left: 3px solid #dc3545;
        }

        .status-message.pending-msg {
            background: #fff3cd;
            color: #856404;
            border-left: 3px solid #ffc107;
        }

        .price-tag {
            font-size: 16px;
            font-weight: 700;
            color: var(--accent, #007bff);
        }

        @media (max-width: 768px) {
            .card-header {
                flex-direction: column;
            }

            .badge {
                margin-top: 10px;
            }

            .info-row {
                flex-direction: column;
            }

            .info-label {
                display: block;
                margin-bottom: 5px;
            }

            .card-footer {
                flex-direction: column;
                gap: 10px;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
<div class="container">

    <header>
        <div>
            <h2 style="margin:0;color:var(--accent);">My Reservations</h2>
            <div class="muted">Track and manage your hostel reservation requests</div>
        </div>
    </header>

    <?php if ($success): ?>
        <div class="alert success">✅ <?= htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert error">❌ <?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($reservations && $reservations->num_rows > 0): ?>
        <div class="reservations-list">
            <?php while ($row = $reservations->fetch_assoc()): ?>
            <div class="reservation-card">
                <div class="card-header">
                    <div>
                        <h3 class="hostel-title">
                            <?= htmlspecialchars($row['hostel_name']); ?>
                        </h3>
                        <div class="muted" style="margin-top: 5px;">
                            📍 <?= htmlspecialchars($row['location']); ?>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <span class="badge <?= $row['status']; ?>">
                            <?php 
                                if ($row['status'] == 'pending') {
                                    echo "⏳ Pending";
                                } elseif ($row['status'] == 'approved') {
                                    echo "✓ Approved";
                                } else {
                                    echo "✗ Rejected";
                                }
                            ?>
                        </span>
                        <div class="muted" style="margin-top: 8px;">
                            Reserved: <?= date('M d, Y', strtotime($row['reserved_at'])); ?>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Status Message -->
                    <?php if ($row['status'] == 'approved'): ?>
                        <div class="status-message approved-msg">
                            <strong>✓ Great news!</strong> Your reservation has been approved by the hostel owner. You can now proceed with the booking.
                        </div>
                    <?php elseif ($row['status'] == 'rejected'): ?>
                        <div class="status-message rejected-msg">
                            <strong>✗ Reservation Rejected</strong> Unfortunately, the hostel owner has rejected your reservation request. Please try another hostel.
                        </div>
                    <?php else: ?>
                        <div class="status-message pending-msg">
                            <strong>⏳ Pending Review</strong> Your reservation request is awaiting approval from the hostel owner. You'll be notified once they respond.
                        </div>
                    <?php endif; ?>

                    <!-- Hostel Details -->
                    <div style="margin-top: 15px;">
                        <div class="info-row">
                            <span class="info-label">Price per Month</span>
                            <span class="info-value price-tag">Rs <?= number_format($row['price']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Hostel Description</span>
                        </div>
                        <div class="description">
                            <?= nl2br(htmlspecialchars($row['description'])); ?>
                        </div>
                    </div>

                    <!-- Owner Contact Info -->
                    <div class="owner-info">
                        <strong>👤 Hostel Owner Details:</strong><br>
                        <strong><?= htmlspecialchars($row['owner_name']); ?></strong><br>
                        📧 Email: <a href="mailto:<?= htmlspecialchars($row['owner_email']); ?>" style="color: var(--accent, #007bff); text-decoration: none;">
                            <?= htmlspecialchars($row['owner_email']); ?>
                        </a><br>
                        <?php if ($row['owner_phone']): ?>
                            📱 Phone: <strong><?= htmlspecialchars($row['owner_phone']); ?></strong>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="muted">
                        <?php 
                            $reserved_date = new DateTime($row['reserved_at']);
                            $now = new DateTime();
                            $diff = $reserved_date->diff($now);
                            
                            if ($diff->days == 0) {
                                echo "Reserved today";
                            } elseif ($diff->days == 1) {
                                echo "Reserved 1 day ago";
                            } else {
                                echo "Reserved " . $diff->days . " days ago";
                            }
                        ?>
                    </div>
                    <div>
                        <?php if ($row['status'] == 'pending'): ?>
                            <a href="?action=cancel&id=<?= $row['id']; ?>" 
                               class="btn btn-cancel"
                               onclick="return confirm('Are you sure you want to cancel this reservation request?');">
                               ✗ Cancel Request
                            </a>
                        <?php else: ?>
                            <span class="btn btn-disabled">
                                <?php echo ($row['status'] == 'approved') ? '✓ Confirmed' : '✗ Cancelled'; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-data">
            <div style="font-size: 48px; margin-bottom: 15px;">🔍</div>
            <h3 style="margin: 0 0 10px 0; color: #333;">No Reservations Yet</h3>
            <p style="margin: 0; color: #999;">Start exploring hostels and make your first reservation!</p>
            <a class="btn btn-back" href="../../index.php" style="margin-top: 20px;">
                ← Browse Hostels
            </a>
        </div>
    <?php endif; ?>

    <a class="btn btn-back" href="dashboard.php">← Back to Dashboard</a>

</div>
</body>
</html>