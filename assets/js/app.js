const toggle = document.querySelector('.nav-toggle');
const nav = document.querySelector('#primary-nav');

if (toggle && nav) {
  toggle.addEventListener('click', () => {
    const isOpen = nav.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', String(isOpen));
  });
}

const checkoutForm = document.querySelector('[data-checkout-form]');

if (checkoutForm) {
  const unitPrice = Number(checkoutForm.dataset.unitPrice || 0);
  const quantityInput = checkoutForm.querySelector('[data-quantity]');
  const subtotalNode = document.querySelector('[data-subtotal]');
  const deliveryNode = document.querySelector('[data-delivery-total]');
  const totalNode = document.querySelector('[data-order-total]');
  const formatter = new Intl.NumberFormat('en-ZA', {
    style: 'currency',
    currency: 'ZAR',
  });

  const formatMoney = (value) => formatter.format(value).replace('ZAR', 'R').trim();

  const updateTotals = () => {
    const quantity = Math.max(1, Number(quantityInput?.value || 1));
    const selectedDelivery = checkoutForm.querySelector('input[name="delivery"]:checked');
    const deliveryFee = Number(selectedDelivery?.dataset.deliveryFee || 0);
    const subtotal = unitPrice * quantity;

    if (subtotalNode) subtotalNode.textContent = formatMoney(subtotal);
    if (deliveryNode) deliveryNode.textContent = deliveryFee === 0 ? 'Free' : formatMoney(deliveryFee);
    if (totalNode) totalNode.textContent = formatMoney(subtotal + deliveryFee);
  };

  checkoutForm.addEventListener('input', updateTotals);
  checkoutForm.addEventListener('change', updateTotals);
  updateTotals();
}
