<?php $currentUser = app_current_user(); ?>
  </main>
  <footer class="site-footer">
    <div class="footer-grid">
      <div>
        <h3>LocalLink Market</h3>
        <p>Simple ecommerce pages, email and password login, checkout, and a small admin dashboard.</p>
      </div>
      <div>
        <h3>Store</h3>
        <a href="<?php echo htmlspecialchars(app_url('products.php')); ?>">Browse products</a>
        <a href="<?php echo htmlspecialchars(app_url('product.php?id=1')); ?>">Featured product</a>
        <a href="<?php echo htmlspecialchars(app_url('checkout.php?product_id=1')); ?>">Checkout</a>
      </div>
      <div>
        <h3>Account</h3>
        <a href="<?php echo htmlspecialchars(app_url($currentUser ? app_dashboard_path_for_user($currentUser) : 'login.php')); ?>">
          <?php echo $currentUser ? 'My account' : 'Login'; ?>
        </a>
        <a href="<?php echo htmlspecialchars(app_url($currentUser ? 'logout.php' : 'register.php')); ?>">
          <?php echo $currentUser ? 'Logout' : 'Register'; ?>
        </a>
        <?php if ($currentUser !== null && app_is_admin($currentUser)): ?>
          <a href="<?php echo htmlspecialchars(app_url('admin/dashboard.php')); ?>">Admin dashboard</a>
        <?php endif; ?>
      </div>
    </div>
    <p class="footer-note">&copy; <?php echo date('Y'); ?> LocalLink Market. Prices are shown in ZAR.</p>
  </footer>
  <script src="<?php echo htmlspecialchars(app_url('assets/js/app.js')); ?>"></script>
</body>
</html>
