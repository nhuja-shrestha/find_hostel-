<?php
session_start();
require_once('../../config/db.php');


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'owner') {
    header("Location: ../login.php");
    exit;
}

$owner_id = $_SESSION['user_id'];
$success = "";
$error = "";

/* ===============================
   HANDLE APPROVE / REJECT
================================ */

if (isset($_GET['action']) && isset($_GET['id'])) {

    $reservation_id = intval($_GET['id']);
    $action = $_GET['action'];

    // Get reservation + hostel info (only owner's hostels)
    $query = "
        SELECT 
            r.id AS reservation_id,
            r.status,
            r.reserved_at,
            r.hostel_id,
            u.name AS student_name,
            u.email,
            u.phone,
            h.name AS hostel_name,
            h.rooms AS available_rooms,
            h.owner_id
        FROM reservations r
        JOIN users u ON r.user_id = u.id
        JOIN hostels h ON r.hostel_id = h.hostel_id
        WHERE r.id = $reservation_id AND h.owner_id = $owner_id
    ";

    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {

        $reservation = $result->fetch_assoc();

        if ($action == 'approve') {

            if ($reservation['available_rooms'] > 0) {

                // 1️⃣ Update reservation status
                $conn->query("
                    UPDATE reservations
                    SET status = 'approved'
                    WHERE id = $reservation_id
                ");

                // 2️⃣ Decrease hostel room count
                $conn->query("
                    UPDATE hostels
                    SET rooms = rooms - 1
                    WHERE hostel_id = {$reservation['hostel_id']}
                ");

                $success = "Reservation approved and room count decreased.";

            } else {
                $error = "No rooms available!";
            }

        } elseif ($action == 'reject') {

            $conn->query("
                UPDATE reservations
                SET status = 'rejected'
                WHERE id = $reservation_id
            ");

            $success = "Reservation rejected.";
        }
    } else {
        $error = "Reservation not found or unauthorized!";
    }
}

/* ===============================
   FETCH ALL OWNER RESERVATIONS
================================ */

$reservations = $conn->query("
    SELECT r.*, u.name AS student_name, u.email, u.phone, h.name AS hostel_name, h.location
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN hostels h ON r.hostel_id = h.hostel_id
    WHERE h.owner_id = $owner_id
    ORDER BY r.reserved_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Manage Reservations</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .reservation-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-top: 15px;
        }

        .reservation-table thead {
            background: var(--accent, #007bff);
            color: white;
        }

        .reservation-table th {
            padding: 14px;
            text-align: left;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .reservation-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        .reservation-table tbody tr:hover {
            background: #f9f9f9;
        }

        .reservation-table tbody tr:last-child td {
            border-bottom: none;
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
            margin-right: 6px;
        }

        .btn-approve {
            background: #28a745;
            color: white;
        }

        .btn-approve:hover {
            background: #218838;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(40,167,69,0.3);
        }

        .btn-reject {
            background: #dc3545;
            color: white;
        }

        .btn-reject:hover {
            background: #c82333;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(220,53,69,0.3);
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
        }

        .muted {
            color: #999;
            font-size: 13px;
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
    </style>
</head>
<body>
<div class="container">

    <header>
        <div>
            <h2 style="margin:0;color:var(--accent);">Manage Reservations</h2>
            <div class="muted">View and approve pending reservation requests</div>
        </div>
    </header>

    <?php if ($success): ?>
        <div class="alert success">✅ <?= $success ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert error">❌ <?= $error ?></div>
    <?php endif; ?>

    <?php if ($reservations->num_rows > 0): ?>
        <table class="reservation-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Hostel</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Reserved Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $reservations->fetch_assoc()): ?>
                <tr>
                    <td><strong>#<?= $row['id']; ?></strong></td>
                    <td><?= htmlspecialchars($row['student_name']); ?></td>
                    <td><?= htmlspecialchars($row['email']); ?></td>
                    <td><?= htmlspecialchars($row['hostel_name']); ?></td>
                    <td><?= htmlspecialchars($row['location']); ?></td>
                    <td>
                        <span class="badge <?= $row['status']; ?>">
                            <?= ucfirst($row['status']); ?>
                        </span>
                    </td>
                    <td class="muted"><?= date('M d, Y', strtotime($row['reserved_at'])); ?></td>
                    <td>
                        <?php if ($row['status'] == 'pending'): ?>
                            <a href="?action=approve&id=<?= $row['id']; ?>" 
                               class="btn btn-approve"
                               onclick="return confirm('Approve this reservation?');">
                               ✓ Approve
                            </a>

                            <a href="?action=reject&id=<?= $row['id']; ?>" 
                               class="btn btn-reject"
                               onclick="return confirm('Reject this reservation?');">
                               ✗ Reject
                            </a>
                        <?php else: ?>
                            <span class="muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-data">📭 No reservations yet.</div>
    <?php endif; ?>

    <a class="btn btn-back" href="dashboard.php">← Back to Dashboard</a>

</div>
</body>
</html>