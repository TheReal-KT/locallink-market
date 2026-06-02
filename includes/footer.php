  </main>
  <footer class="site-footer">
    <div class="footer-lead">
      <p class="eyebrow">Local marketplace prototype</p>
      <strong>LocalLink Market</strong>
      <p>Responsive C2C marketplace prototype mapped to the ITECA Deliverable 2 scope, with buyer, seller, and admin journeys ready for backend integration.</p>
    </div>
    <div class="footer-columns">
      <div class="footer-column">
        <strong>Prototype Pages</strong>
        <div class="footer-links">
          <a href="<?php echo htmlspecialchars(app_url('products.php')); ?>">Browse listings</a>
          <a href="<?php echo htmlspecialchars(app_url('product.php?id=1')); ?>">Product details</a>
          <a href="<?php echo htmlspecialchars(app_url('checkout.php')); ?>">Checkout flow</a>
        </div>
      </div>
      <div class="footer-column">
        <strong>Marketplace Flow</strong>
        <div class="footer-links">
          <a href="<?php echo htmlspecialchars(app_url('register.php')); ?>">Create account</a>
          <a href="<?php echo htmlspecialchars(app_url('add-product.php')); ?>">List an item</a>
          <a href="<?php echo htmlspecialchars(app_url('seller-dashboard.php')); ?>">Manage seller tasks</a>
        </div>
      </div>
      <div class="footer-column footer-column-highlight">
        <strong>Current Build Status</strong>
        <p>Design prototype complete enough for screenshots. PHP sessions, MySQL queries, CRUD actions, and deployment still need implementation.</p>
      </div>
    </div>
  </footer>
  <script src="<?php echo htmlspecialchars(app_url('assets/js/app.js')); ?>"></script>
</body>
</html>
