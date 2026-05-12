# ultimatePOS — VPS Deployment Guide

This guide covers a production deployment on a single Ubuntu 22.04 LTS VPS with
Nginx, PHP-FPM, MySQL 8, Redis, and Supervisor. Cloudflare sits in front as the
reverse proxy and handles TLS termination.

---

## Directory layout

```
docs/config/
├── nginx/
│   ├── nginx.conf              # Bare-metal (self-managed TLS)
│   ├── nginx-cloudflare.conf   # Cloudflare-locked variant (no TLS on origin)
│   └── cloudflare-ips.conf     # Cloudflare IP allowlist snippet
├── supervisor/
│   └── pos-workers.conf        # Queue workers + scheduler
├── scripts/
│   ├── deploy.sh               # Zero-downtime deployment script
│   └── update-cloudflare-ips.sh# Weekly cron to refresh CF IP allowlist
├── env.production              # .env template for production
└── deployment.md               # This file
```

---

## 1. Server prerequisites

```bash
apt update && apt upgrade -y
apt install -y nginx mysql-server redis-server supervisor \
    php8.1-fpm php8.1-cli php8.1-mysql php8.1-redis \
    php8.1-gd php8.1-mbstring php8.1-xml php8.1-curl \
    php8.1-zip php8.1-bcmath php8.1-intl php8.1-soap \
    git curl unzip certbot

# Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Node + npm (for asset compilation)
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
```

---

## 2. MySQL setup

```sql
-- Run as root in mysql
CREATE DATABASE afipos_central CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- This user needs CREATE DATABASE privilege so stancl/tenancy can create tenant DBs
CREATE USER 'afipos_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON afipos_central.* TO 'afipos_user'@'localhost';
GRANT CREATE, DROP ON *.* TO 'afipos_user'@'localhost';
FLUSH PRIVILEGES;
```

> The `CREATE` and `DROP` grants on `*.*` are required by stancl/tenancy to provision
> and delete tenant databases. Scope to `tenant%.*` if your MySQL version supports it.

---

## 3. Redis setup

```bash
# Set a password — edit /etc/redis/redis.conf
# requirepass STRONG_REDIS_PASSWORD_HERE
systemctl restart redis-server
systemctl enable redis-server
```

---

## 4. Application deployment

```bash
# Create web root
mkdir -p /var/www/pos
chown www-data:www-data /var/www/pos

# Clone repo
git clone https://github.com/your-org/Point-Of-Sale.git /var/www/pos
cd /var/www/pos

# Environment
cp .env.example .env
# Edit .env with values from docs/config/env.production
nano .env

# Dependencies
composer install --no-dev --optimize-autoloader
npm ci --omit=dev && npm run build

# Laravel bootstrap
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force

# Storage permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

---

## 5. Nginx setup

### Option A — Self-managed TLS (no Cloudflare proxy)

Get a wildcard certificate first:
```bash
# Requires DNS API credentials for the DNS-01 challenge.
# Example with Cloudflare DNS plugin:
pip install certbot-dns-cloudflare
certbot certonly \
  --dns-cloudflare \
  --dns-cloudflare-credentials /root/.secrets/cloudflare.ini \
  -d yourdomain.com \
  -d "*.yourdomain.com" \
  --agree-tos --email admin@yourdomain.com
```

`/root/.secrets/cloudflare.ini`:
```ini
dns_cloudflare_api_token = YOUR_CF_API_TOKEN_WITH_DNS_EDIT_SCOPE
```

Then install the Nginx config:
```bash
mkdir -p /etc/nginx/snippets
cp docs/config/nginx/nginx.conf /etc/nginx/sites-available/pos
ln -s /etc/nginx/sites-available/pos /etc/nginx/sites-enabled/pos
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx
```

### Option B — Cloudflare proxy (orange cloud, recommended)

Set Cloudflare SSL mode to **Full (strict)** in the dashboard.

```bash
# Install the Cloudflare IP allowlist script
cp docs/config/scripts/update-cloudflare-ips.sh /usr/local/bin/update-cloudflare-ips.sh
chmod +x /usr/local/bin/update-cloudflare-ips.sh
mkdir -p /etc/nginx/snippets

# Run it once to create the initial file
/usr/local/bin/update-cloudflare-ips.sh

# Schedule weekly refresh
echo "0 3 * * 0 root /usr/local/bin/update-cloudflare-ips.sh >> /var/log/update-cf-ips.log 2>&1" \
    > /etc/cron.d/cloudflare-ips

# Install the Cloudflare-locked Nginx config
cp docs/config/nginx/nginx-cloudflare.conf /etc/nginx/sites-available/pos
ln -s /etc/nginx/sites-available/pos /etc/nginx/sites-enabled/pos
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx
```

DNS records needed in Cloudflare (proxy enabled — orange cloud):
```
A     yourdomain.com      → VPS_IP   (proxied)
A     *.yourdomain.com    → VPS_IP   (proxied)
```

---

## 6. Supervisor (queue workers)

```bash
cp docs/config/supervisor/pos-workers.conf /etc/supervisor/conf.d/pos-workers.conf
supervisorctl reread
supervisorctl update
supervisorctl status
```

Expected output:
```
pos-scheduler:pos-scheduler         RUNNING
pos-worker-default:pos-worker-default_00  RUNNING
pos-worker-default:pos-worker-default_01  RUNNING
pos-worker-tenants:pos-worker-tenants_00  RUNNING
```

---

## 7. Laravel TrustHosts middleware

Enable in `app/Http/Kernel.php` to prevent host-header injection:

```php
protected $middleware = [
    \App\Http\Middleware\TrustHosts::class,  // must be first
    \App\Http\Middleware\TrustProxies::class,
    // ...
];
```

In `app/Http/Middleware/TrustHosts.php`:
```php
public function hosts(): array
{
    return [
        'yourdomain.com',
        '*.yourdomain.com',
        $this->allSubdomainsOfApplicationUrl(),
    ];
}
```

In `app/Http/Middleware/TrustProxies.php` (when behind Cloudflare):
```php
protected $proxies = '*';
protected $headers = Request::HEADER_X_FORWARDED_FOR
    | Request::HEADER_X_FORWARDED_HOST
    | Request::HEADER_X_FORWARDED_PORT
    | Request::HEADER_X_FORWARDED_PROTO;
```

---

## 8. Cron (if not using Supervisor for scheduler)

```bash
# Alternative to pos-scheduler in supervisor
crontab -u www-data -e
# Add:
* * * * * cd /var/www/pos && php artisan schedule:run >> /dev/null 2>&1
```

---

## 9. Deployments (ongoing)

```bash
chmod +x docs/config/scripts/deploy.sh
cp docs/config/scripts/deploy.sh /usr/local/bin/pos-deploy
chmod +x /usr/local/bin/pos-deploy

# Deploy
pos-deploy

# Deploy without running migrations (hotfix)
pos-deploy --skip-migrations
```

The deploy script:
1. Pulls latest code from `main`
2. Runs `composer install` and `npm run build`
3. Puts the app in maintenance mode
4. Runs central + tenant migrations
5. Warms config/route/view caches
6. Restarts queue workers gracefully
7. Brings the app back online

---

## 10. Tenant subdomain provision checklist

When a new business is created via the superadmin portal:

| Step | Handled by |
|------|-----------|
| `Tenant` record created in central DB | `Business::create_business()` |
| Tenant database created (`tenant{uuid}`) | `TenancyServiceProvider` → `CreateDatabase` job |
| Tenant migrations run | `TenancyServiceProvider` → `MigrateDatabase` job |
| Business + owner seeded into tenant DB | `SeedTenantData` job |
| Domain record created (`slug.yourdomain.com`) | `Business::create_business()` |
| DNS resolves the subdomain | Wildcard `A` record (already in place) |
| Nginx routes the subdomain | Wildcard server block (already in place) |
| Laravel identifies tenant | `InitializeTenancyBySubdomain` middleware |

**No Nginx config changes are needed per tenant provision.** The wildcard handles all subdomains automatically.

---

## Security hardening checklist

- [ ] `APP_DEBUG=false` in production `.env`
- [ ] `TrustHosts` middleware enabled
- [ ] Nginx `return 444` default server block in place
- [ ] Cloudflare origin lock active (`include cloudflare-ips.conf`) or self-managed TLS with wildcard cert
- [ ] `update-cloudflare-ips.sh` cron scheduled weekly
- [ ] Redis password set in `redis.conf`
- [ ] MySQL user has minimum required grants
- [ ] `storage/` and `.env` are not web-accessible (Nginx `deny` rules in place)
- [ ] `TenantCouldNotBeIdentifiedException` returns a clean 404 (see `app/Exceptions/Handler.php`)
- [ ] Fail2ban configured on SSH and Nginx 444 responses
