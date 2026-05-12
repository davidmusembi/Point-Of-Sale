# Caddy Setup — ultimatePOS Test Server

## Prerequisites

```bash
# Verify Caddy is installed
caddy version

# Install the Cloudflare DNS plugin (needed for wildcard cert via DNS-01 challenge)
caddy add-package github.com/caddy-dns/cloudflare

# If caddy was installed via apt, rebuild with xcaddy instead:
# apt install golang
# go install github.com/caddyserver/xcaddy/cmd/xcaddy@latest
# xcaddy build --with github.com/caddy-dns/cloudflare
# mv caddy /usr/bin/caddy
```

## File placement

```bash
# Caddyfile
sudo cp docs/config/caddy/Caddyfile /etc/caddy/Caddyfile

# Environment (API token) — restrict permissions before writing the token
sudo cp docs/config/caddy/caddy.env /etc/caddy/caddy.env
sudo chmod 600 /etc/caddy/caddy.env
sudo chown caddy:caddy /etc/caddy/caddy.env
# Edit the file and paste your Cloudflare API token
sudo nano /etc/caddy/caddy.env
```

## Wire the env file into the systemd unit

```bash
sudo systemctl edit caddy
```

Add:
```ini
[Service]
EnvironmentFile=/etc/caddy/caddy.env
```

```bash
sudo systemctl daemon-reload
```

## PHP-FPM pool (Option A — runs as developer)

```bash
sudo cp docs/config/caddy/php-fpm-pos.conf /etc/php/8.1/fpm/pool.d/pos.conf
# Disable the default www pool to avoid conflicts
sudo mv /etc/php/8.1/fpm/pool.d/www.conf /etc/php/8.1/fpm/pool.d/www.conf.disabled
sudo systemctl restart php8.1-fpm
```

## Start / reload

```bash
# Validate config
caddy validate --config /etc/caddy/Caddyfile

# Reload (zero-downtime)
sudo systemctl reload caddy

# Full restart if reload fails
sudo systemctl restart caddy

# Watch logs
journalctl -u caddy -f
```

## Verify wildcard cert

```bash
# Caddy should automatically obtain a wildcard cert via Cloudflare DNS-01.
# Check cert status:
curl -sI https://yourdomain.com | grep -i "server\|x-caddy"
curl -sI https://test-tenant.yourdomain.com | grep -i "server"
```

## Cloudflare dashboard settings

| Setting | Value |
|---------|-------|
| SSL/TLS mode | **Full (strict)** |
| `yourdomain.com` A record | VPS IP — **Proxied** (orange cloud) |
| `*.yourdomain.com` A record | VPS IP — **Proxied** (orange cloud) |
| Always Use HTTPS | On |
| Minimum TLS Version | TLS 1.2 |

## Log locations

| Log | Path |
|-----|------|
| Caddy system log | `journalctl -u caddy` |
| Central app access | `/var/log/caddy/pos-central-access.log` |
| Tenant access | `/var/log/caddy/pos-tenant-access.log` |
| PHP-FPM errors | `/var/log/php-fpm/pos-error.log` |
| Laravel app log | `/var/www/pos/storage/logs/laravel.log` |
