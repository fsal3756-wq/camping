<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controllers/BookingController.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../config/database.php';

Auth::requireLogin();

$bookingId = $_GET['booking_id'] ?? null;

if (!$bookingId) {
    echo "<script>alert('Invalid booking ID'); window.close();</script>";
    exit;
}

$controller = new BookingController();
$booking = $controller->getBookingDetail($bookingId);

// Cek apakah booking milik user yang login (kecuali admin)
if ($booking['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Unauthorized access'); window.close();</script>";
    exit;
}

// Cek apakah payment sudah completed
if ($booking['payment_status'] !== 'completed') {
    echo "<script>alert('Pembayaran belum selesai!'); window.close();</script>";
    exit;
}

// Get booking items dari booking_items table
$db = Database::getInstance()->getConnection();
$itemsQuery = "SELECT bi.*, i.name as item_name, i.category
               FROM booking_items bi
               LEFT JOIN items i ON bi.item_id = i.id
               WHERE bi.booking_id = ?";
$stmt = $db->prepare($itemsQuery);
$stmt->execute([$bookingId]);
$bookingItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If no items in booking_items, use main booking (backward compatibility)
if (empty($bookingItems)) {
    $duration = calculateDays($booking['start_date'], $booking['end_date']);
    $pricePerDay = $booking['total_price'] / ($booking['quantity'] * $duration);
    
    $bookingItems = [[
        'item_name' => $booking['item_name'],
        'category' => $booking['category'] ?? '',
        'quantity' => $booking['quantity'],
        'price_per_day' => $pricePerDay,
        'start_date' => $booking['start_date'],
        'end_date' => $booking['end_date'],
        'subtotal' => $booking['total_price']
    ]];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran - <?= htmlspecialchars($booking['transaction_id'] ?? 'N/A') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Screen styles */
        body {
            background: #f5f5f5;
            padding: 20px;
        }
        
        .receipt-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        
        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .receipt-header h1 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .receipt-header p {
            margin: 2px 0;
            color: #666;
            font-size: 14px;
        }
        
        .receipt-title {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .receipt-title h2 {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .receipt-title .transaction-id {
            background: #f8f9fa;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
            border: 2px solid #dee2e6;
        }
        
        .receipt-section {
            margin-bottom: 30px;
        }
        
        .receipt-section h5 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .info-table {
            width: 100%;
        }
        
        .info-table td {
            padding: 8px 0;
            vertical-align: top;
        }
        
        .info-table td:first-child {
            font-weight: 600;
            width: 180px;
            color: #555;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .items-table th {
            background: #2c3e50;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .items-table tbody tr:last-child td {
            border-bottom: 2px solid #2c3e50;
        }
        
        .summary-box {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }
        
        .summary-box table {
            width: 100%;
        }
        
        .summary-box td {
            padding: 8px 0;
        }
        
        .summary-box td:last-child {
            text-align: right;
        }
        
        .total-row {
            border-top: 2px solid #2c3e50;
            padding-top: 10px !important;
            font-size: 18px;
            font-weight: bold;
            color: #28a745;
        }
        
        .receipt-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px dashed #333;
            text-align: center;
            color: #666;
        }
        
        .important-note {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .important-note ul {
            margin-bottom: 0;
            padding-left: 20px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .status-paid {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .no-print {
            text-align: center;
            margin-top: 30px;
        }
        
        /* Print styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .receipt-container {
                box-shadow: none;
                max-width: 100%;
                padding: 20px;
                border-radius: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            .receipt-header h1 {
                font-size: 24px;
            }
            
            .items-table th {
                background: #333 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .status-paid,
            .important-note,
            .summary-box {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            /* Prevent page break inside important elements */
            .receipt-section,
            .summary-box,
            .important-note {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="receipt-header">
            <h1>🏕️ <?= APP_NAME ?></h1>
            <p>📍 Jl. Camping Adventure No. 123, Bandung, Jawa Barat 40132</p>
            <p>📞 Telp: (022) 1234-5678 | WA: 0812-3456-7890</p>
            <p>📧 Email: info@campingrental.com | 🌐 www.campingrental.com</p>
        </div>

        <!-- Receipt Title -->
        <div class="receipt-title">
            <h2>BUKTI PEMBAYARAN</h2>
            <div class="transaction-id">
                <small class="text-muted d-block">Nomor Transaksi</small>
                <strong style="font-size: 18px;"><?= htmlspecialchars($booking['transaction_id'] ?? 'N/A') ?></strong>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="receipt-section">
            <h5><i class="bi bi-person-circle me-2"></i>Informasi Pelanggan</h5>
            <table class="info-table">
                <tr>
                    <td>Nama Pelanggan</td>
                    <td>: <?= htmlspecialchars($booking['user_name'] ?? $booking['full_name'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>: <?= htmlspecialchars($booking['email'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <td>No. Telepon</td>
                    <td>: <?= htmlspecialchars($booking['phone'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td>Tanggal Booking</td>
                    <td>: <?= formatDate($booking['created_at'] ?? date('Y-m-d'), 'd F Y H:i') ?> WIB</td>
                </tr>
                <tr>
                    <td>Status Pembayaran</td>
                    <td>: <span class="status-badge status-paid">✓ LUNAS</span></td>
                </tr>
            </table>
        </div>

        <!-- Rental Period -->
        <div class="receipt-section">
            <h5><i class="bi bi-calendar-check me-2"></i>Periode Sewa</h5>
            <table class="info-table">
                <tr>
                    <td>Tanggal Mulai Sewa</td>
                    <td>: <?= formatDate($bookingItems[0]['start_date'], 'd F Y') ?></td>
                </tr>
                <tr>
                    <td>Tanggal Selesai Sewa</td>
                    <td>: <?= formatDate($bookingItems[0]['end_date'], 'd F Y') ?></td>
                </tr>
                <tr>
                    <td>Durasi Sewa</td>
                    <td>: <strong><?= calculateDays($bookingItems[0]['start_date'], $bookingItems[0]['end_date']) ?> Hari</strong></td>
                </tr>
            </table>
        </div>

        <!-- Items Detail - MULTIPLE ITEMS -->
        <div class="receipt-section">
            <h5><i class="bi bi-box-seam me-2"></i>Detail Pesanan (<?= count($bookingItems) ?> Item)</h5>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">Nama Peralatan</th>
                        <th style="text-align: center; width: 10%;">Qty</th>
                        <th style="text-align: right; width: 18%;">Harga/Hari</th>
                        <th style="text-align: center; width: 12%;">Durasi</th>
                        <th style="text-align: right; width: 20%;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookingItems as $item): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($item['item_name']) ?></strong>
                            <?php if (!empty($item['category'])): ?>
                                <br><small class="text-muted">Kategori: <?= htmlspecialchars($item['category']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <strong><?= $item['quantity'] ?></strong>
                        </td>
                        <td style="text-align: right;">
                            <?= formatRupiah($item['price_per_day']) ?>
                        </td>
                        <td style="text-align: center;">
                            <?= calculateDays($item['start_date'], $item['end_date']) ?> hari
                        </td>
                        <td style="text-align: right;">
                            <strong><?= formatRupiah($item['subtotal']) ?></strong>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <div class="summary-box">
            <table>
                <tr>
                    <td><strong>Subtotal (<?= count($bookingItems) ?> item)</strong></td>
                    <td><strong><?= formatRupiah($booking['total_price']) ?></strong></td>
                </tr>
                <tr>
                    <td>Diskon</td>
                    <td><?= formatRupiah(0) ?></td>
                </tr>
                <tr>
                    <td>Biaya Admin</td>
                    <td><?= formatRupiah(0) ?></td>
                </tr>
                <tr class="total-row">
                    <td><strong>TOTAL PEMBAYARAN</strong></td>
                    <td><strong><?= formatRupiah($booking['total_price']) ?></strong></td>
                </tr>
            </table>
        </div>

        <!-- Payment Method -->
        <?php if (!empty($booking['payment_method'])): ?>
        <div class="receipt-section">
            <h5><i class="bi bi-credit-card me-2"></i>Metode Pembayaran</h5>
            <table class="info-table">
                <tr>
                    <td>Metode Pembayaran</td>
                    <td>: <?= ucwords(str_replace('_', ' ', $booking['payment_method'])) ?></td>
                </tr>
                <?php if (!empty($booking['payment_date'])): ?>
                <tr>
                    <td>Tanggal Pembayaran</td>
                    <td>: <?= formatDate($booking['payment_date'], 'd F Y H:i') ?> WIB</td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        <?php endif; ?>

        <!-- Important Notes -->
        <div class="important-note">
            <strong>⚠️ KETENTUAN PENTING:</strong>
            <ul class="text-start mt-2">
                <li>Harap bawa dan tunjukkan struk ini saat pengambilan peralatan</li>
                <li>Peralatan harus dikembalikan dalam kondisi baik dan bersih</li>
                <li>Kerusakan atau kehilangan akan dikenakan biaya penggantian</li>
                <li>Keterlambatan pengembalian akan dikenakan denda Rp 50.000/hari</li>
                <li>Simpan struk ini sebagai bukti pembayaran yang sah</li>
            </ul>
        </div>

        <!-- Footer -->
        <div class="receipt-footer">
            <p style="font-size: 16px; font-weight: 600; color: #2c3e50;">
                Terima kasih atas kepercayaan Anda!
            </p>
            <p style="color: #28a745; font-weight: 600;">
                Selamat berkemah dan nikmati petualangan Anda! 🏕️⛺
            </p>
            
            <hr style="margin: 20px 0; border-top: 1px solid #dee2e6;">
            
            <p class="mb-1"><small>Dicetak pada: <?= date('d F Y H:i:s') ?> WIB</small></p>
            <p class="mb-0"><small>Struk ini dicetak secara otomatis dan sah tanpa tanda tangan</small></p>
        </div>

        <!-- Print Buttons -->
        <div class="no-print">
            <button onclick="window.print()" class="btn btn-primary btn-lg me-2">
                <i class="bi bi-printer me-2"></i>Cetak Struk
            </button>
            <button onclick="window.close()" class="btn btn-secondary btn-lg">
                <i class="bi bi-x-circle me-2"></i>Tutup
            </button>
        </div>
    </div>

    <script>
        // Auto print saat halaman dibuka (opsional - hapus komentar jika ingin aktif)
        // window.onload = function() { 
        //     setTimeout(() => window.print(), 500); 
        // }
    </script>
</body>
</html>