<?php
/**
 * ============================================
 * Prevent Directory Listing
 * ============================================
 * 
 * Location: 
 * - assets/uploads/documents/ktp/index.php
 * - assets/uploads/documents/sim/index.php
 * - assets/uploads/documents/receipts/index.php
 * 
 * Purpose:
 * Block access to this directory and show 403 error
 * 
 * DO NOT confuse this with:
 * - /camping-rental-apps/index.php (main homepage)
 * 
 * This file is ONLY for security in upload folders!
 * ============================================
 */

// Send 403 Forbidden header
header("HTTP/1.0 403 Forbidden");

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Exit with error message
exit('Access Denied');