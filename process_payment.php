<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/controllers/PaymentController.php';

$controller = new PaymentController();
$controller->process();