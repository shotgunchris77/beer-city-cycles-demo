# Beer City Cycles - Website Demo

A working demo of the Beer City Cycles website. Choppers and Dynas out of Milwaukee, WI. Party, drink beer, put down miles.

**Open the WordPress demo:** https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/shotgunchris77/beer-city-cycles-demo/main/beer-city-cycles-demo.zip

The demo runs in WordPress Playground in your browser. Nothing is saved between visits, and it takes about 30 to 60 seconds to load because it installs WordPress and WooCommerce each time.

## What's inside
- Home, About, Events & Rides, Merch and Contact pages
- Events post type for shows, parties and rides (Dashboard > Events & Rides), with the 2027 After Party and Bike Show loaded
- Merch store on WooCommerce: every size is its own stock count, so a size that hits zero cannot be sold
- Shipping worked out at checkout as USPS Ground Advantage from Milwaukee, by ZIP code and package weight
- Every order emails the shop with the items, sizes, quantities and the customer's name, email, phone and address. The customer gets a confirmation too
- Contact form that emails the shop
- Links to [@beercitycycles](https://www.instagram.com/beercitycycles/)

## Trying it out
1. Open Merch, pick a shirt and a size, add it to the cart and check out with any U.S. address.
2. The strip at the bottom of every page links to the owner screens: Orders, Merch and stock, Events and rides, Demo outbox and Settings.
3. Dashboard > Demo Outbox shows the emails the site would have sent. The preview cannot send real email.

## Notes
- Prices, sizes, weights and stock counts are placeholders. Change them under Products.
- No card payments yet. Checkout uses a stand-in "Order now, pay by payment request" method until Stripe, Square or PayPal is added on the live site.
- Shipping prices are the USPS retail tables in `source/beer-city-cycles/data/usps-rates.json`. Update that file when USPS changes prices. U.S. addresses only.
- The shop email and Instagram link are under Settings > Beer City.
- `beer-city-cycles-demo.zip` holds `blueprint.json` and `theme.zip`. Editable source is in `source/`.
