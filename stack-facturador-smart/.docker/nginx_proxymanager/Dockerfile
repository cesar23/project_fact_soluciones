FROM python:3.11-slim AS certbot-plugins

RUN pip install --no-cache-dir \
    certbot-dns-duckdns \
    certbot-dns-cloudflare

# ------------------------------

FROM jc21/nginx-proxy-manager:latest

# Copiar plugins desde la etapa anterior
COPY --from=certbot-plugins /usr/local/lib/python3.11/site-packages /opt/certbot/lib/python3.11/site-packages