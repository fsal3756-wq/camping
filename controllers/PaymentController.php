<?php
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/functions.php';

class PaymentController
{
    private $paymentModel;
    private $bookingModel;

    public function __construct()
    {
        $this->paymentModel = new Payment();
        $this->bookingModel = new Booking();
    }

    public function checkout($bookingId)
    {
        Auth::requireLogin();

        $user = Auth::user();
        $booking = $this->bookingModel->getById($bookingId);

        if (!$booking) {
            setFlashMessage('danger', 'Booking not found');
            redirect('/camping-rental-apps/views/booking/status.php');
        }

        // Check if user owns the booking
        if ($booking['user_id'] != $user['id']) {
            setFlashMessage('danger', 'Unauthorized access');
            redirect('/camping-rental-apps/views/booking/status.php');
        }

        // Check if payment already exists
        $existingPayment = $this->paymentModel->getByBookingId($bookingId);
        if ($existingPayment && $existingPayment['status'] === 'completed') {
            setFlashMessage('info', 'Payment already completed');
            redirect('/camping-rental-apps/views/booking/status.php');
        }

        return [
            'booking' => $booking,
            'payment' => $existingPayment
        ];
    }

    public function process()
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/camping-rental-apps/index.php');
        }

        $user = Auth::user();
        $bookingId = $_POST['booking_id'] ?? null;
        $paymentMethod = $_POST['payment_method'] ?? null;

        if (!$bookingId || !$paymentMethod) {
            setFlashMessage('danger', 'Please fill all required fields');
            redirect($_SERVER['HTTP_REFERER']);
        }

        // ✨ Validate payment method specific fields
        if ($paymentMethod === 'bank_transfer') {
            $bankName = $_POST['bank_name'] ?? '';
            $accountNumber = $_POST['account_number'] ?? '';
            $accountHolder = $_POST['account_holder'] ?? '';
            
            if (empty($bankName) || empty($accountNumber) || empty($accountHolder)) {
                setFlashMessage('danger', 'Please fill in all bank transfer details');
                redirect($_SERVER['HTTP_REFERER']);
            }
        } elseif ($paymentMethod === 'e_wallet') {
            $ewalletType = $_POST['ewallet_type'] ?? '';
            $ewalletPhone = $_POST['ewallet_phone'] ?? '';
            
            if (empty($ewalletType) || empty($ewalletPhone)) {
                setFlashMessage('danger', 'Please fill in all e-wallet details');
                redirect($_SERVER['HTTP_REFERER']);
            }
        }

        // Get booking
        $booking = $this->bookingModel->getById($bookingId);

        if (!$booking) {
            setFlashMessage('danger', 'Booking not found');
            redirect('/camping-rental-apps/views/booking/status.php');
        }

        // Check if user owns the booking
        if ($booking['user_id'] != $user['id']) {
            setFlashMessage('danger', 'Unauthorized access');
            redirect('/camping-rental-apps/views/booking/status.php');
        }

        // Check if payment already exists
        $existingPayment = $this->paymentModel->getByBookingId($bookingId);

        // ✨ PERUBAHAN UTAMA: Status langsung 'completed' untuk semua metode pembayaran
        // Jika ingin e_wallet tetap pending untuk verifikasi manual, ubah menjadi:
        // $paymentStatus = ($paymentMethod === 'bank_transfer') ? 'completed' : 'pending';
        $paymentStatus = 'completed';

        if ($existingPayment) {
            // Update existing payment
            $updated = $this->paymentModel->update($existingPayment['id'], [
                'payment_method' => $paymentMethod,
                'status' => $paymentStatus
            ]);

            $paymentId = $existingPayment['id'];
        } else {
            // Create new payment
            $paymentData = [
                'booking_id' => $bookingId,
                'user_id' => $user['id'],
                'amount' => $booking['total_price'],
                'payment_method' => $paymentMethod,
                'transaction_id' => generateTransactionId(),
                'status' => $paymentStatus  // ✨ Status langsung completed
            ];

            $paymentId = $this->paymentModel->create($paymentData);
        }

        if ($paymentId) {
            // ✨ Update booking status ke 'confirmed' dan payment_status ke 'completed'
            if ($paymentStatus === 'completed') {
                $this->bookingModel->updateStatus($bookingId, 'confirmed');
                $this->bookingModel->updatePaymentStatus($bookingId, 'completed');
            }

            setFlashMessage('success', 'Payment completed successfully! Your booking has been confirmed.');
            
            // ✨ Redirect ke status.php dengan booking_id
            redirect("/camping-rental-apps/views/booking/status.php?booking_id=$bookingId");
        } else {
            setFlashMessage('danger', 'Failed to process payment');
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    
    public function confirm($paymentId)
    {
        Auth::requireAdmin();

        if ($this->paymentModel->updateStatus($paymentId, 'completed')) {
            setFlashMessage('success', 'Payment confirmed successfully');
        } else {
            setFlashMessage('danger', 'Failed to confirm payment');
        }

        // ✅ BENAR: Gunakan APP_URL + path relatif — jangan hardcode /camping-rental-apps/...
        redirect(APP_URL . '/admin/payments.php');
    }

    public function reject($paymentId)
    {
        Auth::requireAdmin();

        if ($this->paymentModel->updateStatus($paymentId, 'failed')) {
            setFlashMessage('success', 'Payment rejected');
        } else {
            setFlashMessage('danger', 'Failed to reject payment');
        }

        redirect(APP_URL . '/admin/payments.php'); // ✅ sama di sini
    }

    public function detail($paymentId)
    {
        Auth::requireLogin();

        $user = Auth::user();
        $payment = $this->paymentModel->getById($paymentId);

        if (!$payment) {
            setFlashMessage('danger', 'Payment not found');
            redirect('/camping-rental-apps/views/booking/status.php');
        }

        // Check if user owns the payment or is admin
        if ($payment['user_id'] != $user['id'] && !Auth::isAdmin()) {
            setFlashMessage('danger', 'Unauthorized access');
            redirect('/camping-rental-apps/views/booking/status.php');
        }

        return $payment;
    }
}