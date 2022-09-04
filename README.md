# NOTES

- https://packagist.org/packages/jenssegers/proxy (Proxy library that forwards requests to the desired url and returns the response)
- `composer require jenssegers/proxy`
- Run by executing : `php -S 127.0.0.1:4044` - Any port value
- *CONFIGS* values in index.php 
- Configuration added in `.env`
- **Cross-Origin Request Headers(CORS) with PHP headers**
- - https://stackoverflow.com/questions/8719276/cross-origin-request-headerscors-with-php-headers
- - https://stackoverflow.com/questions/14467673/enable-cors-in-htaccess

## Cryptojs / Reverse Proxy / API Server


- **Static** Crypto.js  [https://github.com/fadilxcoder/cryoto-js.git] --> **PHP** Reverse proxy --> **PHP** HFX Api server [https://github.com/fadilxcoder/hfx-api.git]
- Internally, launch php server : `php -S 127.0.0.1:3052 -t public/` then, `lt --port 3052 --subdomain api-hfx` + *Add `'Bypass-Tunnel-Reminder'` in request header to bypass tunnel landing page*
