/* Beer City Cycles front-end script: nav highlight, size buttons, product thumbnails, preview-mode link notice. */
(function () {
	'use strict';

	/* Mark the current page in the header menu. */
	document.querySelectorAll('.bcc-header .wp-block-navigation-item__content').forEach(function (a) {
		try {
			var u = new URL(a.href, location.href);
			var here = location.pathname.replace(/\/+$/, '');
			var there = u.pathname.replace(/\/+$/, '');
			if (u.origin === location.origin && there && (here === there || here.indexOf(there + '/') === 0)) {
				a.setAttribute('aria-current', 'page');
			}
		} catch (e) {}
	});

	/* Close the mobile menu when tapping outside it. */
	document.addEventListener('click', function (e) {
		var c = document.querySelector('.bcc-header .wp-block-navigation__responsive-container.is-menu-open');
		if (!c || c.contains(e.target) || e.target.closest('.wp-block-navigation__responsive-container-open')) { return; }
		var x = c.querySelector('.wp-block-navigation__responsive-container-close');
		if (x) { x.click(); }
	}, true);

	/* Product thumbnails swap the main image. */
	document.querySelectorAll('.bcc-thumb').forEach(function (b) {
		b.addEventListener('click', function () {
			var main = document.getElementById('bcc-product-img');
			if (!main) { return; }
			main.removeAttribute('srcset');
			main.src = b.getAttribute('data-full');
			document.querySelectorAll('.bcc-thumb').forEach(function (t) { t.classList.toggle('is-on', t === b); });
		});
	});

	/* Size buttons: replace the size drop-down with buttons and cross out sold-out sizes. */
	document.querySelectorAll('.bcc-product form.variations_form').forEach(function (form) {
		var variations = [];
		try { variations = JSON.parse(form.getAttribute('data-product_variations') || '[]') || []; } catch (e) {}
		form.querySelectorAll('table.variations select').forEach(function (sel) {
			var name = sel.getAttribute('name');
			var cell = sel.parentNode;
			var wrap = document.createElement('div');
			wrap.className = 'bcc-pills';
			wrap.setAttribute('role', 'group');
			var note = document.createElement('p');
			note.className = 'bcc-pill-note';
			note.setAttribute('aria-live', 'polite');
			var pills = [];
			[].slice.call(sel.options).forEach(function (opt) {
				if (!opt.value) { return; }
				var v = variations.filter(function (x) { return x.attributes && x.attributes[name] === opt.value; })[0];
				var inStock = !variations.length || (v && v.is_in_stock && v.is_purchasable);
				var b = document.createElement('button');
				b.type = 'button';
				b.className = 'bcc-pill';
				b.textContent = opt.textContent;
				b.setAttribute('aria-pressed', 'false');
				if (!inStock) {
					b.disabled = true;
					b.setAttribute('aria-label', opt.textContent + ', sold out');
					b.title = 'Sold out';
				}
				b.addEventListener('click', function () {
					sel.value = opt.value;
					if (window.jQuery) { window.jQuery(sel).trigger('change'); } else { sel.dispatchEvent(new Event('change', { bubbles: true })); }
					pills.forEach(function (p) { p.classList.toggle('is-on', p === b); p.setAttribute('aria-pressed', p === b ? 'true' : 'false'); });
					note.textContent = '';
				});
				pills.push(b);
				wrap.appendChild(b);
			});
			if (!pills.length) { return; }
			cell.classList.add('bcc-has-pills');
			cell.insertBefore(wrap, sel);
			cell.appendChild(note);
			var out = pills.filter(function (p) { return p.disabled; }).length;
			note.textContent = out === pills.length ? 'Every size is sold out.' : 'Pick a size.' + (out ? ' Crossed out sizes are sold out.' : '');
			if (sel.value) {
				pills.forEach(function (p) { if (p.textContent === sel.options[sel.selectedIndex].textContent) { p.classList.add('is-on'); p.setAttribute('aria-pressed', 'true'); note.textContent = ''; } });
			}
		});
	});

	/* Demo preview only: the preview runs inside a protected frame, so outside sites cannot open.
	   Show the link with a copy button instead. On the live site links open normally. */
	if (window.self !== window.top) {
		var modal = null;
		var build = function () {
			modal = document.createElement('div');
			modal.className = 'bcc-ext';
			modal.setAttribute('role', 'dialog');
			modal.setAttribute('aria-modal', 'true');
			modal.innerHTML = '<div class="bcc-ext-box"><p class="bcc-ext-eyebrow">Preview mode</p><h3 class="bcc-ext-title"></h3><p class="bcc-ext-copy">This demo runs inside a protected preview, so outside sites like Instagram and Google Maps cannot open from here. On the live site this link opens normally.</p><input class="bcc-ext-url" readonly><div class="bcc-ext-actions"><button type="button" class="bcc-ext-btn bcc-ext-do">Copy link</button><button type="button" class="bcc-ext-btn bcc-ext-close">Close</button></div></div>';
			document.body.appendChild(modal);
			modal.addEventListener('click', function (e) {
				if (e.target === modal || e.target.classList.contains('bcc-ext-close')) { modal.classList.remove('is-on'); }
			});
			modal.querySelector('.bcc-ext-do').addEventListener('click', function () {
				var input = modal.querySelector('.bcc-ext-url');
				var btn = this;
				var ok = function () { btn.textContent = 'Copied'; };
				input.focus(); input.select(); input.setSelectionRange(0, 9999);
				var done = false;
				try { done = document.execCommand('copy'); } catch (x) {}
				if (done) { ok(); return; }
				if (navigator.clipboard) { navigator.clipboard.writeText(input.value).then(ok, function () { btn.textContent = 'Select and copy'; }); } else { btn.textContent = 'Select and copy'; }
			});
			document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { modal.classList.remove('is-on'); } });
		};
		document.addEventListener('click', function (e) {
			var a = e.target.closest && e.target.closest('a[href]');
			if (!a) { return; }
			var u;
			try { u = new URL(a.href, location.href); } catch (x) { return; }
			if (!/^https?:$/.test(u.protocol) || u.origin === location.origin) { return; }
			e.preventDefault();
			e.stopPropagation();
			if (!modal) { build(); }
			var h = u.hostname;
			modal.querySelector('.bcc-ext-title').textContent = /instagram/.test(h) ? 'Opens Instagram' : (/google|maps|apple/.test(h) ? 'Opens directions' : 'Opens an outside site');
			modal.querySelector('.bcc-ext-url').value = u.href;
			modal.querySelector('.bcc-ext-do').textContent = 'Copy link';
			modal.classList.add('is-on');
		}, true);
	}
})();
