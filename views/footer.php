<!-- Flash Messages -->
<?php
$flash = getFlashMessage();
if ($flash):
?>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div class="toast show align-items-center text-white bg-<?= $flash['type'] ?> border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Footer -->
<footer class="bg-dark text-white mt-auto py-5">
    <div class="container">
        <div class="row g-4">
            <!-- About Section -->
            <div class="col-lg-4 col-md-6">
                <h5 class="mb-3">
                    <i class="bi bi-tree-fill me-2"></i><?= APP_NAME ?>
                </h5>
                <p class="text-white-50 mb-3">
                    Platform rental peralatan camping terpercaya untuk petualangan Anda di Cirebon dan sekitarnya.
                </p>
                <div class="d-flex gap-3">
                    <a href="https://www.facebook.com/a" class="text-white-50 hover-primary"
                        title="Facebook" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-facebook fs-5"></i>
                    </a>

                    <a href="https://www.instagram.com/aryaf26_" class="text-white-50 hover-primary"
                        title="Instagram" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-instagram fs-5"></i>
                    </a>

                    <a href="https://twitter.com/a" class="text-white-50 hover-primary" title="Twitter"
                        target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-twitter fs-5"></i>
                    </a>

                    <a href="https://wa.me/6285889660387" class="text-white-50 hover-primary" title="WhatsApp"
                        target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-whatsapp fs-5"></i>
                    </a>

                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6">
                <h5 class="mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="<?= APP_URL ?>/index.php" class="text-white-50 text-decoration-none hover-primary">
                            <i class="bi bi-chevron-right me-1"></i>Catalog
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?= APP_URL ?>/views/booking/status.php"
                            class="text-white-50 text-decoration-none hover-primary">
                            <i class="bi bi-chevron-right me-1"></i>My Bookings
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-white-50 text-decoration-none hover-primary">
                            <i class="bi bi-chevron-right me-1"></i>About Us
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-white-50 text-decoration-none hover-primary">
                            <i class="bi bi-chevron-right me-1"></i>FAQ
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Services -->
            <div class="col-lg-2 col-md-6">
                <h5 class="mb-3">Our Services</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="#" class="text-white-50 text-decoration-none hover-primary">
                            <i class="bi bi-chevron-right me-1"></i>Equipment Rental
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-white-50 text-decoration-none hover-primary">
                            <i class="bi bi-chevron-right me-1"></i>Camping Guide
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-white-50 text-decoration-none hover-primary">
                            <i class="bi bi-chevron-right me-1"></i>Terms & Conditions
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-white-50 text-decoration-none hover-primary">
                            <i class="bi bi-chevron-right me-1"></i>Privacy Policy
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="col-lg-4 col-md-6">
                <h5 class="mb-3">Contact Us</h5>
                <ul class="list-unstyled">
                    <li class="mb-3">
                        <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                        <span class="text-white-50">Jl. Sukasari No. 02<br>
                            <span class="ms-4">Mandirancan, Kuningan 45558</span></span>
                    </li>
                    <li class="mb-3">
                        <i class="bi bi-telephone-fill text-primary me-2"></i>
                        <a href="tel:+6281234567890" class="text-white-50 text-decoration-none hover-primary">
                            +62 858-8966-0387
                        </a>
                    </li>
                    <li class="mb-3">
                        <i class="bi bi-envelope-fill text-primary me-2"></i>
                        <a href="mailto:info@campingrental.com"
                            class="text-white-50 text-decoration-none hover-primary">
                            info@campingrental.com
                        </a>
                    </li>
                    <li class="mb-3">
                        <i class="bi bi-clock-fill text-primary me-2"></i>
                        <span class="text-white-50">Senin - Sabtu: 08:00 - 20:00</span>
                    </li>
                    <li class="mb-0">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#mapModal">
                            <i class="bi bi-map me-1"></i>Lihat Peta
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Bottom Footer -->
        <hr class="border-secondary my-4">

        <div class="text-center text-white-50">
            <p class="mb-1">
                &copy; <?= date('Y') ?> <strong><?= APP_NAME ?></strong>. All rights reserved.
            </p>
            <p class="mb-0">
                Created by <strong>Suthan Arya Fauzan</strong>
                <!-- Optional -->
                <!-- &mdash; Made with <i
                    class="bi bi-heart-fill text-danger"></i> in Cirebon, Indonesia -->
            </p>
        </div>

    </div>
</footer>

<!-- Map Modal -->
<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mapModalLabel">
                    <i class="bi bi-geo-alt-fill text-primary me-2"></i>Lokasi Kami di Kuningan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Google Maps Embed -->
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3961.2467091234!2d108.5420442!3d-6.768281!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6f1c331675386f%3A0x4bb63e5e36268cc6!2sMasjid%20Sumur%20Tetes!5e0!3m2!1sid!2sid!4v1732752000!5m2!1sid!2sid"
                    width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy">
                </iframe>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <p class="mb-1 small"><i class="bi bi-geo-alt me-2"></i><strong>Alamat:</strong></p>
                            <p class="mb-0 text-muted small">Jl. Sukasari No. 02, Mandirancan, Kuningan 45558</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <a href="https://www.google.com/maps/@-6.7676937,108.5416954,20.88z?entry=ttu&g_ep=EgoyMDI1MTEyMy4xIKXMDSoASAFQAw%3D%3D"
                                target="_blank" class="btn btn-primary btn-sm">
                                <i class="bi bi-compass me-1"></i>Buka di Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?= APP_URL ?>/assets/js/main.js"></script>

<?php if (isset($additionalJS)): ?>
    <?= $additionalJS ?>
<?php endif; ?>

<script>
    // Auto hide toast after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const toasts = document.querySelectorAll('.toast');
        toasts.forEach(toast => {
            setTimeout(() => {
                const bsToast = bootstrap.Toast.getInstance(toast);
                if (bsToast) {
                    bsToast.hide();
                } else {
                    toast.classList.remove('show');
                }
            }, 5000);
        });
    });
</script>
</body>

</html>