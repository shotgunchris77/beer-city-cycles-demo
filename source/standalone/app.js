/*
 * Beer City Cycles - standalone copy.
 *
 * The WordPress site does the store work with WooCommerce. This copy has no server, so this script
 * does the same jobs in the browser so the whole thing can be clicked through anywhere:
 *   - page switching (one HTML file, no reloads)
 *   - merch with stock per size, cart, checkout and an order confirmation
 *   - USPS Ground Advantage shipping from Milwaukee, from the same price and zone tables as the WordPress site
 *   - an owner screen with orders, stock per size and the emails each order would send
 * Nothing leaves the browser: no card is charged and no email is sent.
 */
(function () {
	'use strict';

	var D = window.BCC_DATA;
	var SIZES = D.sizes;
	var LOW = 2; // "Only N left" at or below this

	/* ---------- storage: memory, plus the optional keep.js helper so a refresh does not empty the cart ---------- */
	var mem = {};
	var keep = window.BCC_KEEP || null;
	function load(key, fallback) {
		if (key in mem) { return mem[key]; }
		try {
			var raw = keep ? keep.get('bcc_' + key) : null;
			if (raw !== null) { mem[key] = JSON.parse(raw); return mem[key]; }
		} catch (e) {}
		mem[key] = fallback;
		return fallback;
	}
	function save(key, value) {
		mem[key] = value;
		try { if (keep) { keep.set('bcc_' + key, JSON.stringify(value)); } } catch (e) {}
	}
	function freshStock() {
		var s = {};
		D.products.forEach(function (p) { s[p.slug] = {}; SIZES.forEach(function (z, i) { s[p.slug][z] = p.stock[i]; }); });
		return s;
	}
	var stock = load('stock', freshStock());
	var cart = load('cart', []);
	var orders = load('orders', []);
	var outbox = load('outbox', []);
	var shipTo = load('shipto', '');
	var flash = null; // one-time notice for the next screen

	/* ---------- helpers ---------- */
	function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
	function money(n) { return '$' + (Math.round(n * 100) / 100).toFixed(2); }
	function product(slug) { return D.products.filter(function (p) { return p.slug === slug; })[0]; }
	function left(slug, size) { return Math.max(0, parseInt((stock[slug] || {})[size], 10) || 0); }
	function inCart(slug, size) { var n = 0; cart.forEach(function (l) { if (l.slug === slug && l.size === size) { n += l.qty; } }); return n; }
	function cartCount() { return cart.reduce(function (n, l) { return n + l.qty; }, 0); }
	function cartSubtotal() { return cart.reduce(function (n, l) { return n + l.qty * product(l.slug).price; }, 0); }
	function cartOunces() {
		var oz = cart.reduce(function (n, l) { return n + l.qty * D.weights[l.size]; }, 0);
		return oz > 0 ? oz + D.packagingOz : 0;
	}
	function today() {
		try { return new Intl.DateTimeFormat('en-CA', { timeZone: 'America/Chicago', year: 'numeric', month: '2-digit', day: '2-digit' }).format(new Date()); } catch (e) { return new Date().toISOString().slice(0, 10); }
	}
	function stamp() {
		try { return new Date().toLocaleString('en-US', { timeZone: 'America/Chicago', month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' }); } catch (e) { return new Date().toString(); }
	}
	function sku(slug, size) { return 'BCC-' + slug.toUpperCase() + '-' + size; }

	/* ---------- USPS Ground Advantage from Milwaukee (same lookups as inc/shipping.php) ---------- */
	function uspsZone(zip, oz) {
		zip = String(zip || '').replace(/\D/g, '');
		if (zip.length < 5) { return 0; }
		var z5 = parseInt(zip.slice(0, 5), 10), z3 = parseInt(zip.slice(0, 3), 10), i, r;
		for (i = 0; i < D.zones.zip5.length; i++) { r = D.zones.zip5[i]; if (z5 >= r[0] && z5 <= r[1] && (!r[3] || oz < 16)) { return r[2]; } }
		for (i = 0; i < D.zones.zip3.length; i++) { r = D.zones.zip3[i]; if (z3 >= r[0] && z3 <= r[1]) { return r[2]; } }
		return 0;
	}
	function uspsTable() {
		var d = today(), pick = null;
		D.rates.tables.forEach(function (t) { if (t.effective <= d && (!pick || t.effective > pick.effective)) { pick = t; } });
		return pick;
	}
	function uspsRate(oz, zone) {
		var t = uspsTable();
		if (!t || zone < 1 || zone > 9 || oz <= 0) { return null; }
		if (oz < 16) {
			var tiers = ['4', '8', '12', '15.999'];
			for (var i = 0; i < tiers.length; i++) { if (oz <= parseFloat(tiers[i]) && t.oz[tiers[i]]) { return t.oz[tiers[i]][zone - 1]; } }
		}
		var lb = String(Math.max(1, Math.ceil(oz / 16)));
		return t.lb[lb] ? t.lb[lb][zone - 1] : null;
	}
	function shippingFor(zip) {
		var oz = cartOunces(), zone = uspsZone(zip, oz), rate = zone ? uspsRate(oz, zone) : null;
		return { zip: String(zip || '').replace(/\D/g, '').slice(0, 5), zone: zone, oz: oz, rate: rate };
	}

	/* ---------- notices ---------- */
	var ICON_OK = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M16.7 7.1l-6.3 8.5-3.3-2.5-.9 1.2 4.5 3.4L17.9 8z"></path></svg>';
	var ICON_BAD = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M12 3.2c-4.8 0-8.8 3.9-8.8 8.8 0 4.8 3.9 8.8 8.8 8.8 4.8 0 8.8-3.9 8.8-8.8 0-4.8-4-8.8-8.8-8.8zm0 16c-4 0-7.2-3.3-7.2-7.2C4.8 8 8 4.8 12 4.8s7.2 3.3 7.2 7.2c0 4-3.2 7.2-7.2 7.2zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"></path></svg>';
	function notice(n) {
		if (!n) { return ''; }
		return '<div class="wc-block-components-notice-banner ' + (n.bad ? 'is-error' : 'is-success') + '" role="' + (n.bad ? 'alert' : 'status') + '">' + (n.bad ? ICON_BAD : ICON_OK) + '<div class="wc-block-components-notice-banner__content"><span>' + n.html + '</span>' + (n.link ? ' <a class="sa-btn sa-btn-small" href="' + n.link[0] + '">' + esc(n.link[1]) + '</a>' : '') + '</div></div>';
	}
	function takeFlash() { var f = flash; flash = null; return notice(f); }

	/* ---------- merch grid ---------- */
	function sizeStrip(p) {
		return '<ul class="bcc-sizes" aria-label="Sizes">' + SIZES.map(function (z) {
			var has = left(p.slug, z) > 0;
			return '<li class="' + (has ? '' : 'is-out') + '"' + (has ? '' : ' title="Sold out"') + '>' + z + (has ? '' : '<span class="screen-reader-text"> sold out</span>') + '</li>';
		}).join('') + '</ul>';
	}
	function anyLeft(p) { return SIZES.some(function (z) { return left(p.slug, z) > 0; }); }
	function renderMerch() {
		document.querySelectorAll('.bcc-merch').forEach(function (el) {
			if (!el.hasAttribute('data-count')) { el.setAttribute('data-count', el.querySelectorAll('.bcc-card').length || D.products.length); }
			var n = parseInt(el.getAttribute('data-count'), 10);
			el.innerHTML = D.products.slice(0, n).map(function (p) {
				var has = anyLeft(p), url = '#/product/' + p.slug;
				return '<article class="bcc-card' + (has ? '' : ' is-soldout') + '"><a class="bcc-card-img" href="' + url + '"><img width="1100" height="1100" src="assets/images/' + p.images[0] + '" alt="' + esc(p.name) + '" loading="lazy" decoding="async">' + (has ? '' : '<span class="bcc-badge">Sold out</span>') + '</a><div class="bcc-card-body"><h3 class="bcc-card-title"><a href="' + url + '">' + esc(p.name) + '</a></h3><p class="bcc-card-price">' + money(p.price) + '</p>' + sizeStrip(p) + '</div></article>';
			}).join('');
		});
	}

	/* ---------- product ---------- */
	var picked = null;
	function viewProduct(slug) {
		var p = product(slug);
		if (!p) { return viewMissing(); }
		picked = null;
		var soldOut = !anyLeft(p);
		var h = '<div class="bcc-product">';
		var f = takeFlash();
		if (f) { h += '<div class="bcc-product-notices">' + f + '</div>'; }
		h += '<div class="bcc-product-media"><div class="bcc-product-main"><img width="1100" height="1100" src="assets/images/' + p.images[0] + '" alt="' + esc(p.name) + '" id="bcc-product-img"></div>';
		if (p.images.length > 1) {
			h += '<div class="bcc-product-thumbs">' + p.images.map(function (img, i) { return '<button type="button" class="bcc-thumb' + (i ? '' : ' is-on') + '" data-full="assets/images/' + img + '" aria-label="Show image ' + (i + 1) + '"><img src="assets/images/' + img + '" alt="" width="300" height="300"></button>'; }).join('') + '</div>';
		}
		h += '</div><div class="bcc-product-info"><p class="bcc-back"><a href="#/merch">&larr; All merch</a></p><h1 class="bcc-product-title">' + esc(p.name) + '</h1><p class="bcc-product-price">' + money(p.price) + '</p><div class="bcc-product-desc"><p>' + esc(p.desc) + '</p></div>';
		if (soldOut) {
			h += '<p class="bcc-soldout-note">Sold out. Watch <a href="' + D.instagram + '" target="_blank" rel="noopener">' + esc(D.handle) + '</a> for the next run.</p>';
		} else {
			var out = SIZES.filter(function (z) { return left(slug, z) < 1; }).length;
			h += '<form class="sa-add" data-slug="' + slug + '"><span class="sa-label" id="sa-size-label">Size</span><div class="bcc-pills" role="group" aria-labelledby="sa-size-label">' + SIZES.map(function (z) {
				var has = left(slug, z) > 0;
				return '<button type="button" class="bcc-pill" data-size="' + z + '" aria-pressed="false"' + (has ? '' : ' disabled aria-label="' + z + ', sold out" title="Sold out"') + '>' + z + '</button>';
			}).join('') + '</div><p class="bcc-pill-note" aria-live="polite">Pick a size.' + (out ? ' Crossed out sizes are sold out.' : '') + '</p><p class="stock" hidden></p><div class="woocommerce-variation-add-to-cart"><div class="quantity"><label class="screen-reader-text" for="sa-qty">' + esc(p.name) + ' quantity</label><input type="number" id="sa-qty" class="sa-input qty" name="quantity" value="1" min="1" step="1" inputmode="numeric" autocomplete="off"></div><button type="submit" class="single_add_to_cart_button disabled">Add to cart</button></div></form>';
		}
		h += '<ul class="bcc-product-points"><li>Ships from Milwaukee by USPS Ground Advantage. Shipping is worked out at checkout from your ZIP code.</li><li>U.S. orders only for now.</li><li>Questions on sizing or an order? <a href="#/contact">Get in touch</a>.</li></ul><div class="bcc-product-long"><p>' + esc(D.note) + '</p></div></div></div>';
		return { title: p.name, html: h };
	}
	function pickSize(form, size) {
		var slug = form.getAttribute('data-slug'), n = left(slug, size) - inCart(slug, size);
		picked = size;
		form.querySelectorAll('.bcc-pill').forEach(function (b) { var on = b.getAttribute('data-size') === size; b.classList.toggle('is-on', on); b.setAttribute('aria-pressed', on ? 'true' : 'false'); });
		form.querySelector('.bcc-pill-note').textContent = '';
		var st = form.querySelector('.stock'), have = left(slug, size);
		st.hidden = false;
		st.textContent = have <= LOW ? 'Only ' + have + ' left in stock' : 'In stock';
		var q = form.querySelector('.qty');
		q.max = Math.max(1, n);
		if (parseInt(q.value, 10) > n) { q.value = Math.max(1, n); }
		form.querySelector('.single_add_to_cart_button').classList.remove('disabled');
	}
	function addToCart(form) {
		var slug = form.getAttribute('data-slug'), p = product(slug);
		if (!picked) { form.querySelector('.bcc-pill-note').textContent = 'Pick a size first.'; return; }
		var qty = Math.max(1, parseInt(form.querySelector('.qty').value, 10) || 1), have = left(slug, picked), held = inCart(slug, picked);
		if (have < 1) {
			flash = { bad: true, html: 'You cannot add &quot;' + esc(p.name) + ' &ndash; ' + picked + '&quot; to the cart because it is sold out.' };
		} else if (qty + held > have) {
			flash = { bad: true, html: 'You cannot add that amount of &quot;' + esc(p.name) + ' &ndash; ' + picked + '&quot; to the cart. There ' + (have === 1 ? 'is' : 'are') + ' ' + have + ' in stock' + (held ? ' and you already have ' + held + ' in your cart' : '') + '.', link: held ? ['#/cart', 'View cart'] : null };
		} else {
			var line = cart.filter(function (l) { return l.slug === slug && l.size === picked; })[0];
			if (line) { line.qty += qty; } else { cart.push({ slug: slug, size: picked, qty: qty }); }
			save('cart', cart);
			flash = { html: (qty > 1 ? qty + ' &times; ' : '') + '&ldquo;' + esc(p.name) + '&rdquo; ' + (qty > 1 ? 'have' : 'has') + ' been added to your cart.', link: ['#/cart', 'View cart'] };
		}
		render();
	}

	/* ---------- cart ---------- */
	function shippingRow(editable) {
		if (!cart.length) { return ''; }
		var s = shipTo ? shippingFor(shipTo) : null, cell;
		if (s && s.rate !== null) {
			cell = '<strong>USPS Ground Advantage: ' + money(s.rate) + '</strong><p class="sa-ship-note">Shipping from Milwaukee to ZIP ' + esc(s.zip) + ' (USPS zone ' + s.zone + ').</p>';
		} else if (s) {
			cell = '<p class="sa-ship-note sa-ship-bad">No USPS shipping found for ZIP ' + esc(shipTo) + '. Check the ZIP code. U.S. addresses only.</p>';
		} else {
			cell = '<p class="sa-ship-note">Enter your ZIP code to see shipping.</p>';
		}
		if (editable) {
			cell += '<form class="sa-ship-form"><label class="screen-reader-text" for="sa-zip">ZIP code</label><input class="sa-input" id="sa-zip" inputmode="numeric" autocomplete="postal-code" maxlength="10" placeholder="ZIP code" value="' + esc(shipTo) + '"><button type="submit" class="sa-btn sa-btn-small sa-btn-ghost">' + (shipTo ? 'Update' : 'Calculate') + '</button></form>';
		}
		return '<tr><th scope="row">Shipping</th><td>' + cell + '</td></tr>';
	}
	function totalNow() {
		var s = shipTo ? shippingFor(shipTo) : null;
		return cartSubtotal() + (s && s.rate !== null ? s.rate : 0);
	}
	function viewCart() {
		var h = '<h1 class="bcc-page-title">Cart</h1><div class="sa-store">' + takeFlash();
		if (!cart.length) {
			return { title: 'Cart', html: h + '<p class="sa-empty">Your cart is empty.</p><p><a class="sa-btn" href="#/merch">Shop merch</a></p></div>' };
		}
		h += '<div class="sa-lines">' + cart.map(function (l, i) {
			var p = product(l.slug);
			return '<div class="sa-line"><a href="#/product/' + l.slug + '"><img src="assets/images/' + p.images[0] + '" alt="" width="64" height="64"></a><div class="sa-line-name"><a href="#/product/' + l.slug + '">' + esc(p.name) + ' &ndash; ' + l.size + '</a><small>' + money(p.price) + ' each</small></div><div class="sa-line-qty"><label class="screen-reader-text" for="sa-q' + i + '">Quantity</label><input class="sa-input sa-qty" id="sa-q' + i + '" data-line="' + i + '" type="number" min="1" max="' + left(l.slug, l.size) + '" step="1" value="' + l.qty + '" inputmode="numeric"></div><div class="sa-line-sub">' + money(p.price * l.qty) + '</div><button type="button" class="sa-remove" data-remove="' + i + '" aria-label="Remove ' + esc(p.name) + ' ' + l.size + '">&times;</button></div>';
		}).join('') + '</div>';
		h += '<div class="sa-side"><h2>Cart totals</h2><table class="sa-table"><tbody><tr><th scope="row">Subtotal</th><td>' + money(cartSubtotal()) + '</td></tr>' + shippingRow(true) + '<tr class="sa-total-row"><th scope="row">Total</th><td>' + money(totalNow()) + '</td></tr></tbody></table><a class="sa-btn sa-btn-wide" href="#/checkout">Proceed to checkout</a></div></div>';
		return { title: 'Cart', html: h };
	}

	/* ---------- checkout ---------- */
	var form = load('form', {});
	var FIELDS = [
		['first', 'First name', 'text', 'given-name', true], ['last', 'Last name', 'text', 'family-name', true],
		['address1', 'Street address', 'text', 'address-line1', true, 'wide'], ['address2', 'Apartment, suite, unit <span>(optional)</span>', 'text', 'address-line2', false, 'wide'],
		['city', 'Town / City', 'text', 'address-level2', true], ['state', 'State', 'state', 'address-level1', true],
		['zip', 'ZIP Code', 'text', 'postal-code', true], ['phone', 'Phone', 'tel', 'tel', true],
		['email', 'Email address', 'email', 'email', true, 'wide'], ['notes', 'Order notes <span>(optional)</span>', 'area', 'off', false, 'wide']
	];
	function reviewTable() {
		return '<table class="sa-table"><thead><tr><th scope="col">Product</th><th scope="col">Subtotal</th></tr></thead><tbody>' + cart.map(function (l) {
			var p = product(l.slug);
			return '<tr><td>' + esc(p.name) + ' &ndash; ' + l.size + ' <strong>&times; ' + l.qty + '</strong></td><td>' + money(p.price * l.qty) + '</td></tr>';
		}).join('') + '<tr><th scope="row">Subtotal</th><td>' + money(cartSubtotal()) + '</td></tr>' + shippingRow(false) + '<tr class="sa-total-row"><th scope="row">Total</th><td>' + money(totalNow()) + '</td></tr></tbody></table>';
	}
	function viewCheckout() {
		if (!cart.length) {
			return { title: 'Checkout', html: '<h1 class="bcc-page-title">Checkout</h1><div class="sa-store">' + takeFlash() + '<p class="sa-empty">Your cart is empty.</p><p><a class="sa-btn" href="#/merch">Shop merch</a></p></div>' };
		}
		if (!form.zip && shipTo) { form.zip = shipTo; }
		var h = '<h1 class="bcc-page-title">Checkout</h1><div class="sa-store">' + takeFlash() + '<form class="sa-checkout" novalidate><div><h2>Shipping details</h2><div class="bcc-form">';
		FIELDS.forEach(function (f) {
			var id = 'sa-f-' + f[0], v = esc(form[f[0]] || ''), input;
			if (f[2] === 'state') {
				input = '<select id="' + id + '" name="' + f[0] + '" autocomplete="' + f[3] + '"><option value="">Select a state</option>' + D.states.map(function (s) { return '<option value="' + s[0] + '"' + (form.state === s[0] ? ' selected' : '') + '>' + esc(s[1]) + '</option>'; }).join('') + '</select>';
			} else if (f[2] === 'area') {
				input = '<textarea id="' + id + '" name="' + f[0] + '" rows="3" placeholder="Notes about your order, like special notes for delivery.">' + v + '</textarea>';
			} else {
				input = '<input type="' + f[2] + '" id="' + id + '" name="' + f[0] + '" autocomplete="' + f[3] + '" value="' + v + '"' + (f[0] === 'zip' ? ' inputmode="numeric" maxlength="10"' : '') + '>';
			}
			h += '<p class="bcc-field' + (f[5] ? ' bcc-field-wide' : '') + '"><label for="' + id + '">' + f[1] + '</label>' + input + '<span class="sa-err" data-err="' + f[0] + '" hidden></span></p>';
		});
		h += '<p class="bcc-field bcc-field-wide" style="margin:0;color:var(--bcc-ash);font-size:0.92rem">United States (US) only for now.</p></div></div><div><h2>Your order</h2><div id="sa-review">' + reviewTable() + '</div><div class="sa-pay"><strong>Order now, pay by payment request</strong><p>No card is charged on this site yet. Place the order and Beer City Cycles will send a payment request to your email. Your sizes are held as soon as the order is placed.</p></div><button type="submit" class="sa-btn sa-btn-wide">Place order</button><p class="sa-fine">Your details are only used to fill and ship this order.</p></div></form></div>';
		return { title: 'Checkout', html: h };
	}
	function readForm(el) {
		FIELDS.forEach(function (f) { var i = el.querySelector('[name="' + f[0] + '"]'); if (i) { form[f[0]] = i.value.trim(); } });
		save('form', form);
	}
	function placeOrder(el) {
		readForm(el);
		var errs = {};
		FIELDS.forEach(function (f) { if (f[4] && !form[f[0]]) { errs[f[0]] = 'This is needed.'; } });
		if (form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(form.email)) { errs.email = 'Enter a working email address.'; }
		if (form.phone && form.phone.replace(/\D/g, '').length < 10) { errs.phone = 'Enter a 10 digit phone number.'; }
		var ship = null;
		if (form.zip) {
			shipTo = form.zip; save('shipto', shipTo);
			ship = shippingFor(form.zip);
			if (ship.rate === null) { errs.zip = 'No USPS shipping found for that ZIP code. U.S. addresses only.'; }
		}
		// Stock is checked again at the last second, the same as the WordPress store does.
		var short = cart.filter(function (l) { return l.qty > left(l.slug, l.size); });
		if (short.length) {
			flash = { bad: true, html: 'Not enough stock for ' + short.map(function (l) { return esc(product(l.slug).name) + ' &ndash; ' + l.size + ' (' + left(l.slug, l.size) + ' left)'; }).join(', ') + '. Change the quantity in your cart.', link: ['#/cart', 'View cart'] };
			return render();
		}
		el.querySelectorAll('[data-err]').forEach(function (s) {
			var k = s.getAttribute('data-err'), i = el.querySelector('[name="' + k + '"]');
			s.hidden = !errs[k]; s.textContent = errs[k] || '';
			if (i) { i.classList.toggle('sa-bad', !!errs[k]); if (errs[k]) { i.setAttribute('aria-invalid', 'true'); } else { i.removeAttribute('aria-invalid'); } }
		});
		document.getElementById('sa-review').innerHTML = reviewTable();
		var first = Object.keys(errs)[0];
		if (first) { var bad = el.querySelector('[name="' + first + '"]'); if (bad) { bad.focus(); } return; }

		var id = 1001 + orders.length;
		var items = cart.map(function (l) { var p = product(l.slug); return { slug: l.slug, name: p.name, size: l.size, sku: sku(l.slug, l.size), qty: l.qty, price: p.price }; });
		var order = { id: id, date: stamp(), items: items, subtotal: cartSubtotal(), shipping: ship.rate, zone: ship.zone, oz: ship.oz, total: cartSubtotal() + ship.rate, customer: JSON.parse(JSON.stringify(form)) };
		var alerts = [];
		items.forEach(function (it) {
			stock[it.slug][it.size] = left(it.slug, it.size) - it.qty;
			var n = stock[it.slug][it.size];
			if (n <= 0) { alerts.push({ subject: '[Beer City Cycles] Product out of stock', body: it.name + ' - ' + it.size + ' (' + it.sku + ') is out of stock.' }); }
			else if (n <= LOW) { alerts.push({ subject: '[Beer City Cycles] Product low in stock', body: it.name + ' - ' + it.size + ' (' + it.sku + ') is low in stock. There are ' + n + ' left.' }); }
		});
		orders.unshift(order);
		alerts.forEach(function (a) { mail(D.email, a.subject, a.body); });
		mail(D.email, '[Beer City Cycles]: New order #' + id, orderText(order, true));
		mail(order.customer.email, 'Your Beer City Cycles order has been received!', orderText(order, false));
		cart = []; form.notes = '';
		save('stock', stock); save('orders', orders); save('cart', cart); save('form', form);
		go('/order/' + id);
	}

	/* ---------- emails (kept on the owner screen instead of being sent) ---------- */
	function mail(to, subject, body) {
		outbox.unshift({ to: to, subject: subject, body: body, time: stamp() });
		outbox = outbox.slice(0, 60);
		save('outbox', outbox);
	}
	function addressLines(c) {
		return [c.first + ' ' + c.last, c.address1, c.address2, c.city + ', ' + c.state + ' ' + c.zip].filter(Boolean);
	}
	function orderText(o, owner) {
		var c = o.customer, L = [];
		if (owner) { L.push('You have a new order from ' + c.first + ' ' + c.last + '.', ''); }
		else { L.push('Hi ' + c.first + ',', '', 'We got your order and your items are held. Watch your email for a payment request from Beer City Cycles. The order ships once it is paid.', ''); }
		L.push('ORDER #' + o.id + ' (' + o.date + ')', '');
		o.items.forEach(function (it) { L.push(it.qty + ' x ' + it.name + ' - Size ' + it.size + (owner ? '  [' + it.sku + ']' : '') + '   ' + money(it.price * it.qty)); });
		L.push('', 'Subtotal:  ' + money(o.subtotal), 'Shipping:  ' + money(o.shipping) + ' via USPS Ground Advantage' + (owner ? ' (zone ' + o.zone + ', ' + o.oz + ' oz with packaging)' : ''), 'Total:     ' + money(o.total), 'Payment:   Order now, pay by payment request', '');
		L.push(owner ? 'SHIP TO' : 'SHIPPING TO');
		L = L.concat(addressLines(c));
		if (owner) { L.push('', 'CONTACT', 'Name:  ' + c.first + ' ' + c.last, 'Email: ' + c.email, 'Phone: ' + c.phone); }
		if (c.notes) { L.push('', 'ORDER NOTES', c.notes); }
		if (!owner) { L.push('', 'Questions? Reply to this email.', 'Beer City Cycles. Milwaukee, WI. Party, drink beer, put down miles.'); }
		return L.join('\n');
	}

	/* ---------- order received ---------- */
	function viewOrder(id) {
		var o = orders.filter(function (x) { return String(x.id) === String(id); })[0];
		if (!o) { return viewMissing(); }
		var c = o.customer;
		var h = '<h1 class="bcc-page-title">Order received</h1><div class="sa-store"><p class="sa-thanks">Thank you. Your order has been received.</p><ul class="sa-overview"><li>Order number: <strong>' + o.id + '</strong></li><li>Date: <strong>' + esc(o.date) + '</strong></li><li>Email: <strong>' + esc(c.email) + '</strong></li><li>Total: <strong>' + money(o.total) + '</strong></li><li>Payment method: <strong>Order now, pay by payment request</strong></li></ul><p>We got your order and your items are held. Watch your email for a payment request from Beer City Cycles. The order ships once it is paid.</p>';
		h += '<h2 class="sa-gap">Order details</h2><table class="sa-table"><thead><tr><th scope="col">Product</th><th scope="col">Total</th></tr></thead><tbody>' + o.items.map(function (it) { return '<tr><td>' + esc(it.name) + ' &ndash; ' + it.size + ' <strong>&times; ' + it.qty + '</strong></td><td>' + money(it.price * it.qty) + '</td></tr>'; }).join('') + '<tr><th scope="row">Subtotal:</th><td>' + money(o.subtotal) + '</td></tr><tr><th scope="row">Shipping:</th><td>' + money(o.shipping) + ' <small>via USPS Ground Advantage</small></td></tr><tr><th scope="row">Payment method:</th><td>Order now, pay by payment request</td></tr><tr class="sa-total-row"><th scope="row">Total:</th><td>' + money(o.total) + '</td></tr></tbody></table>';
		h += '<h2 class="sa-gap">Shipping address</h2><address class="sa-address">' + addressLines(c).map(esc).join('<br>') + '<br>' + esc(c.phone) + '<br>' + esc(c.email) + '</address>';
		h += '<p class="bcc-notice" style="margin-top:2rem"><strong>Demo preview.</strong> This order took the sizes out of stock and wrote two emails: one to Beer City Cycles and one to the customer.<em><a href="#/owner">See the order, the stock and the emails on the owner screen</a></em></p></div>';
		return { title: 'Order received', html: h };
	}

	/* ---------- owner screen ---------- */
	function viewOwner() {
		var h = '<h1 class="bcc-page-title">Owner dashboard</h1><div class="sa-store">' + takeFlash() + '<p class="sa-lede">This is a simple stand-in for the WordPress dashboard, so you can see what happens behind the counter. Place a test order or send the contact form, then look here. ' + (keep ? 'Everything stays in this browser.' : 'Nothing is saved, so a refresh starts the demo over.') + '</p><div class="sa-owner-nav"><button type="button" class="sa-btn sa-btn-small sa-btn-ghost" data-jump="sa-orders">Orders</button><button type="button" class="sa-btn sa-btn-small sa-btn-ghost" data-jump="sa-stock">Merch and stock</button><button type="button" class="sa-btn sa-btn-small sa-btn-ghost" data-jump="sa-outbox">Email outbox</button><button type="button" class="sa-btn sa-btn-small" data-reset="1">Reset the demo</button></div>';
		h += '<h2 id="sa-orders">Orders</h2>';
		if (!orders.length) { h += '<p class="sa-empty">No orders yet. <a href="#/merch">Place a test order</a>.</p>'; }
		else {
			h += '<div class="sa-scroll"><table class="sa-table"><thead><tr><th scope="col">Order</th><th scope="col">Customer</th><th scope="col">Items</th><th scope="col">Total</th></tr></thead><tbody>' + orders.map(function (o) {
				var c = o.customer;
				return '<tr><td><a href="#/order/' + o.id + '">#' + o.id + '</a><br><small>' + esc(o.date) + '</small></td><td>' + addressLines(c).map(esc).join('<br>') + '<br><small>' + esc(c.email) + '<br>' + esc(c.phone) + '</small></td><td>' + o.items.map(function (it) { return it.qty + ' &times; ' + esc(it.name) + ' &ndash; ' + it.size; }).join('<br>') + '</td><td>' + money(o.total) + '<br><small>incl. ' + money(o.shipping) + ' shipping</small></td></tr>';
			}).join('') + '</tbody></table></div>';
		}
		h += '<h2 class="sa-gap" id="sa-stock">Merch and stock</h2><p class="sa-lede">How many of each size are on the shelf. An order takes its sizes off automatically, and a size at 0 cannot be bought. Type a number to restock.</p><div class="sa-scroll"><table class="sa-table sa-stock"><thead><tr><th scope="col">Item</th>' + SIZES.map(function (z) { return '<th scope="col">' + z + '</th>'; }).join('') + '</tr></thead><tbody>' + D.products.map(function (p) {
			return '<tr><th scope="row"><a href="#/product/' + p.slug + '">' + esc(p.name) + '</a></th>' + SIZES.map(function (z) { var n = left(p.slug, z); return '<td><input class="sa-input' + (n ? '' : ' is-zero') + '" type="number" min="0" step="1" inputmode="numeric" value="' + n + '" data-stock="' + p.slug + '|' + z + '" aria-label="' + esc(p.name) + ' ' + z + ' in stock"></td>'; }).join('') + '</tr>';
		}).join('') + '</tbody></table></div>';
		h += '<h2 class="sa-gap" id="sa-outbox">Email outbox</h2><p class="sa-lede">This copy cannot send real email, so every email the site would send is kept here, newest first. On the live site order emails, low stock alerts and contact form messages go to <strong>' + esc(D.email) + '</strong>, and the customer gets their own confirmation.</p>';
		if (!outbox.length) { h += '<p class="sa-empty">Nothing yet. Place a test order or send the contact form, then come back.</p>'; }
		else { h += outbox.map(function (m) { return '<div class="sa-mail"><p><span>To:</span> <strong>' + esc(m.to) + '</strong> &nbsp; <span>Sent:</span> ' + esc(m.time) + '</p><p><span>Subject:</span> <strong>' + esc(m.subject) + '</strong></p><pre>' + esc(m.body) + '</pre></div>'; }).join(''); }
		return { title: 'Owner dashboard', html: h + '</div>' };
	}
	function viewMissing() {
		return { title: 'Not found', html: '<h1 class="bcc-page-title">Wrong turn</h1><div class="sa-store"><p class="sa-lede">That page is not here.</p><p><a class="sa-btn" href="#/">Back to the start</a></p></div>' };
	}

	/* ---------- routing ---------- */
	var current = null;
	var storeSlot = document.getElementById('sa-store-slot');
	function parse() {
		var h = (window.location.hash || '').replace(/^#/, '');
		if (h.charAt(0) !== '/') { return '/'; }
		return h.length > 1 ? h.replace(/\/+$/, '') : '/';
	}
	function render() {
		var path = current || '/', key = path, built = null, m;
		if ((m = path.match(/^\/product\/([a-z0-9-]+)$/))) { built = viewProduct(m[1]); }
		else if (path === '/cart') { built = viewCart(); }
		else if (path === '/checkout') { built = viewCheckout(); }
		else if ((m = path.match(/^\/order\/(\d+)$/))) { built = viewOrder(m[1]); }
		else if (path === '/owner') { built = viewOwner(); }
		else if (!Array.prototype.some.call(document.querySelectorAll('.sa-view'), function (v) { return v.getAttribute('data-route') === key; })) { built = viewMissing(); }
		if (built) { storeSlot.innerHTML = built.html; key = '__store'; }
		var title = 'Beer City Cycles';
		document.body.classList.toggle('home', key === '/');
		document.querySelectorAll('.sa-view').forEach(function (v) {
			var on = v.getAttribute('data-route') === key;
			v.classList.toggle('is-on', on);
			if (on && v.getAttribute('data-title')) { title = v.getAttribute('data-title'); }
		});
		document.title = built ? built.title + ' \u2013 Beer City Cycles' : title;
		renderMerch();
		var n = cartCount();
		document.querySelectorAll('.bcc-cart-count').forEach(function (c) { c.textContent = n; c.classList.toggle('has-items', n > 0); });
		document.querySelectorAll('.bcc-cart-link').forEach(function (a) { a.setAttribute('aria-label', 'Cart, ' + n + ' items'); });
		document.querySelectorAll('.bcc-header .wp-block-navigation-item__content').forEach(function (a) {
			var to = (a.getAttribute('href') || '').replace(/^#/, '');
			var on = to.length > 1 && (path === to || path.indexOf(to + '/') === 0 || (to === '/merch' && /^\/product\//.test(path)) || (to === '/events' && /^\/event\//.test(path)));
			if (on) { a.setAttribute('aria-current', 'page'); } else { a.removeAttribute('aria-current'); }
		});
		closeMenu();
	}
	function go(path, keepScroll) {
		current = path;
		render();
		try { if (parse() !== path) { window.location.hash = path; } } catch (e) {}
		if (!keepScroll) { window.scrollTo(0, 0); }
	}
	window.addEventListener('hashchange', function () {
		var p = parse();
		if (p !== current) { current = p; render(); window.scrollTo(0, 0); }
	});

	/* ---------- mobile menu ---------- */
	function menu() { return document.querySelector('.bcc-header .wp-block-navigation__responsive-container'); }
	function closeMenu() {
		var c = menu();
		if (c) { c.classList.remove('is-menu-open', 'has-modal-open'); }
		document.documentElement.classList.remove('has-modal-open');
	}
	function openMenu() {
		var c = menu();
		if (!c) { return; }
		c.classList.add('is-menu-open', 'has-modal-open');
		document.documentElement.classList.add('has-modal-open');
		var x = c.querySelector('.wp-block-navigation__responsive-container-close');
		if (x) { x.focus(); }
	}

	/* ---------- outside links inside a preview frame ---------- */
	var framed = false;
	try { framed = window.self !== window.top; } catch (e) { framed = true; }
	var modal = null;
	function linkNotice(u) {
		if (!modal) {
			modal = document.createElement('div');
			modal.className = 'bcc-ext';
			modal.setAttribute('role', 'dialog');
			modal.setAttribute('aria-modal', 'true');
			modal.innerHTML = '<div class="bcc-ext-box"><p class="bcc-ext-eyebrow">Preview mode</p><h3 class="bcc-ext-title"></h3><p class="bcc-ext-copy">This preview runs inside a protected frame, so outside sites like Instagram and Google Maps may not open from here. On the live site this link opens normally.</p><input class="bcc-ext-url" readonly><div class="bcc-ext-actions"><a class="bcc-ext-btn bcc-ext-open" target="_blank" rel="noopener">Try to open</a><button type="button" class="bcc-ext-btn bcc-ext-do">Copy link</button><button type="button" class="bcc-ext-btn bcc-ext-close">Close</button></div></div>';
			document.body.appendChild(modal);
			modal.addEventListener('click', function (e) { if (e.target === modal || e.target.classList.contains('bcc-ext-close')) { modal.classList.remove('is-on'); } });
			modal.querySelector('.bcc-ext-do').addEventListener('click', function () {
				var input = modal.querySelector('.bcc-ext-url'), btn = this, ok = function () { btn.textContent = 'Copied'; }, done = false;
				input.focus(); input.select(); input.setSelectionRange(0, 9999);
				try { done = document.execCommand('copy'); } catch (x) {}
				if (done) { ok(); return; }
				if (navigator.clipboard) { navigator.clipboard.writeText(input.value).then(ok, function () { btn.textContent = 'Select and copy'; }); } else { btn.textContent = 'Select and copy'; }
			});
		}
		var h = u.hostname;
		modal.querySelector('.bcc-ext-title').textContent = /instagram/.test(h) ? 'Opens Instagram' : (/google|maps|apple/.test(h) ? 'Opens directions' : 'Opens an outside site');
		modal.querySelector('.bcc-ext-url').value = u.href;
		modal.querySelector('.bcc-ext-open').href = u.href;
		modal.querySelector('.bcc-ext-do').textContent = 'Copy link';
		modal.classList.add('is-on');
	}

	/* ---------- events ---------- */
	document.addEventListener('click', function (e) {
		var t = e.target;
		if (t.closest('.wp-block-navigation__responsive-container-open')) { openMenu(); return; }
		if (t.closest('.wp-block-navigation__responsive-container-close')) { closeMenu(); return; }
		var a = t.closest('a[href]');
		if (a) {
			var href = a.getAttribute('href');
			if (href.indexOf('#/') === 0) { e.preventDefault(); go(href.slice(1).replace(/(.)\/+$/, '$1')); return; }
			if (a.classList.contains('bcc-ext-open')) { return; }
			var u = null;
			try { u = new URL(a.href, window.location.href); } catch (x) {}
			if (framed && u && /^https?:$/.test(u.protocol) && u.origin !== window.location.origin) { e.preventDefault(); linkNotice(u); }
			return;
		}
		var pill = t.closest('.bcc-pill');
		if (pill && !pill.disabled) { pickSize(pill.closest('form'), pill.getAttribute('data-size')); return; }
		var thumb = t.closest('.bcc-thumb');
		if (thumb) {
			var main = document.getElementById('bcc-product-img');
			if (main) { main.src = thumb.getAttribute('data-full'); }
			document.querySelectorAll('.bcc-thumb').forEach(function (x) { x.classList.toggle('is-on', x === thumb); });
			return;
		}
		var rm = t.closest('[data-remove]');
		if (rm) { cart.splice(parseInt(rm.getAttribute('data-remove'), 10), 1); save('cart', cart); render(); return; }
		var jump = t.closest('[data-jump]');
		if (jump) { var el = document.getElementById(jump.getAttribute('data-jump')); if (el) { el.scrollIntoView({ block: 'start' }); window.scrollBy(0, -90); } return; }
		if (t.closest('[data-reset]')) {
			stock = freshStock(); cart = []; orders = []; outbox = []; shipTo = ''; form = {};
			save('stock', stock); save('cart', cart); save('orders', orders); save('outbox', outbox); save('shipto', shipTo); save('form', form);
			flash = { html: 'The demo is back to how it started: stock restored, orders and emails cleared.' };
			render(); window.scrollTo(0, 0);
		}
	});
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') { closeMenu(); if (modal) { modal.classList.remove('is-on'); } }
	});
	document.addEventListener('submit', function (e) {
		var f = e.target;
		if (f.classList.contains('sa-add')) { e.preventDefault(); addToCart(f); }
		else if (f.classList.contains('sa-ship-form')) { e.preventDefault(); shipTo = f.querySelector('input').value.trim(); save('shipto', shipTo); render(); }
		else if (f.classList.contains('sa-checkout')) { e.preventDefault(); placeOrder(f); }
		else if (f.classList.contains('bcc-form')) { e.preventDefault(); sendContact(f); }
	});
	document.addEventListener('input', function (e) {
		var t = e.target;
		if (t.classList && t.classList.contains('sa-bad')) {
			t.classList.remove('sa-bad'); t.removeAttribute('aria-invalid');
			var s = t.form && t.form.querySelector('[data-err="' + t.name + '"]');
			if (s) { s.hidden = true; s.textContent = ''; }
		}
	});
	document.addEventListener('change', function (e) {
		var t = e.target;
		if (t.hasAttribute('data-line')) {
			var l = cart[parseInt(t.getAttribute('data-line'), 10)], have = left(l.slug, l.size), want = Math.max(1, parseInt(t.value, 10) || 1);
			if (want > have) { want = have; flash = { bad: true, html: 'Only ' + have + ' of ' + esc(product(l.slug).name) + ' &ndash; ' + l.size + ' in stock.' }; }
			l.qty = want; save('cart', cart); render();
		} else if (t.hasAttribute('data-stock')) {
			var k = t.getAttribute('data-stock').split('|'), n = Math.max(0, parseInt(t.value, 10) || 0);
			stock[k[0]][k[1]] = n; save('stock', stock);
			t.value = n; t.classList.toggle('is-zero', n === 0);
			cart = cart.filter(function (c) { return left(c.slug, c.size) > 0; });
			cart.forEach(function (c) { c.qty = Math.min(c.qty, left(c.slug, c.size)); });
			save('cart', cart);
		} else if (t.name === 'zip' && t.closest('.sa-checkout')) {
			shipTo = t.value.trim(); save('shipto', shipTo);
			document.getElementById('sa-review').innerHTML = reviewTable();
		} else if (t.closest('.sa-checkout')) { readForm(t.closest('.sa-checkout')); }
	});

	/* ---------- contact form ---------- */
	function sendContact(f) {
		var v = function (n) { var i = f.querySelector('[name="' + n + '"]'); return i ? i.value.trim() : ''; };
		var old = f.parentNode.querySelector('.bcc-notice');
		if (old) { old.parentNode.removeChild(old); }
		var box = document.createElement('div');
		if (v('bcc_website')) { return; } // spam trap
		if (!v('bcc_name') || !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v('bcc_from')) || !v('bcc_message')) {
			box.className = 'bcc-notice is-bad'; box.setAttribute('role', 'alert');
			box.textContent = 'Fill in your name, a working email and a message, then send it again.';
		} else {
			mail(D.email, '[Beer City Cycles] ' + v('bcc_topic') + ' from ' + v('bcc_name'), ['New message from the Beer City Cycles website', '', 'Name:  ' + v('bcc_name'), 'Email: ' + v('bcc_from'), v('bcc_phone') ? 'Phone: ' + v('bcc_phone') : '', 'About: ' + v('bcc_topic'), '', v('bcc_message'), '', '(Reply goes straight to ' + v('bcc_from') + ')'].filter(function (x, i, a) { return x !== '' || a[i - 1] !== ''; }).join('\n'));
			box.className = 'bcc-notice is-ok'; box.setAttribute('role', 'status');
			box.innerHTML = '<strong>Message sent.</strong> We will get back to you soon.<em>Demo preview: the email was kept on the <a href="#/owner">owner screen</a> instead of being sent.</em>';
			f.reset();
		}
		f.parentNode.insertBefore(box, f);
		box.scrollIntoView({ block: 'center' });
	}

	current = parse();
	render();
})();
