/*
 * Optional helper for the standalone copy. Keeps the cart, stock, orders and outbox in this browser
 * so a refresh does not empty them. Leave this file out and the site still works, it just starts
 * fresh on every load.
 */
window.BCC_KEEP = (function () {
	try {
		var s = window.localStorage;
		s.setItem('bcc_probe', '1'); s.removeItem('bcc_probe');
		return { get: function (k) { return s.getItem(k); }, set: function (k, v) { s.setItem(k, v); } };
	} catch (e) { return null; }
}());
