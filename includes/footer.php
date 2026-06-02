  </main>
  <footer class="site-footer">
    <div class="footer-main">
      <div class="footer-brand">
        <a class="footer-logo" href="<?php echo htmlspecialchars(app_url('index.php')); ?>" aria-label="LocalLink Market home">
          <span class="brand-mark">LL</span>
          <span>LocalLink Market</span>
        </a>
        <p>A local C2C marketplace for everyday buyers and independent sellers across South Africa.</p>
      </div>
      <div class="footer-columns">
        <div class="footer-column">
          <strong>Shop</strong>
          <div class="footer-links">
            <a href="<?php echo htmlspecialchars(app_url('products.php')); ?>">Browse listings</a>
            <a href="<?php echo htmlspecialchars(app_url('product.php?id=1')); ?>">Featured item</a>
            <a href="<?php echo htmlspecialchars(app_url('checkout.php')); ?>">Checkout</a>
          </div>
        </div>
        <div class="footer-column">
          <strong>Account</strong>
          <div class="footer-links">
            <a href="<?php echo htmlspecialchars(app_url('login.php')); ?>">Sign in</a>
            <a href="<?php echo htmlspecialchars(app_url('register.php')); ?>">Create account</a>
            <a href="<?php echo htmlspecialchars(app_url('buyer-dashboard.php')); ?>">Order history</a>
          </div>
        </div>
        <div class="footer-column">
          <strong>Sell</strong>
          <div class="footer-links">
            <a href="<?php echo htmlspecialchars(app_url('add-product.php')); ?>">List an item</a>
            <a href="<?php echo htmlspecialchars(app_url('seller-dashboard.php')); ?>">Seller dashboard</a>
            <a href="<?php echo htmlspecialchars(app_url('admin/dashboard.php')); ?>">Marketplace admin</a>
          </div>
        </div>
        <div class="footer-column footer-contact">
          <strong>Support</strong>
          <p>Need help with a listing, order, or seller account?</p>
          <a href="mailto:support@locallink.market">support@locallink.market</a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <span>&copy; <?php echo date('Y'); ?> LocalLink Market</span>
      <span>Prices in ZAR</span>
      <span>Serving local buyers and sellers</span>
    </div>
  </footer>
  <script src="<?php echo htmlspecialchars(app_url('assets/js/app.js')); ?>"></script>
</body>
</html>
