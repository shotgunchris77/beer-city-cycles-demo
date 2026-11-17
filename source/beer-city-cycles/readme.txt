=== Beer City Cycles ===
Requires at least: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later

Block theme and merch store for Beer City Cycles, Milwaukee, WI. Needs the free WooCommerce plugin for the store.

== Where to edit things ==
* Notification email and Instagram link: Settings > Beer City. Order emails, low stock alerts and the contact form all go to that one address.
* Events and rides: Dashboard > Events & Rides > Add New. Fill in the "Event details" box (date, time, venue, address, type) and set the poster as the Featured image. Events drop off the upcoming list the day after they happen.
* Merch: Products > edit a product. Each size is a variation with its own price, weight and stock quantity. When a size hits zero it shows as sold out and cannot be ordered.
* Orders: WooCommerce > Orders. Each order also sends one email to the notification address.
* Shipping: WooCommerce > Settings > Shipping > United States > USPS Ground Advantage (packaging weight, handling fee). Rates come from data/usps-rates.json (USPS retail price tables) and data/usps-zones-532.json (zones from Milwaukee, ZIP 532). Update usps-rates.json when USPS changes prices.
* Taking payment: add a gateway under WooCommerce > Settings > Payments (Stripe, Square or PayPal plugins).
* Page text: Pages > Home, About, Events & Rides, Contact.

== Credits ==
Fonts: Big Shoulders Display, Yellowtail, Barlow (SIL Open Font License / Apache 2.0).
Artwork and logos: Beer City Cycles.
