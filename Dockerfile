FROM php:8.3-fpm-bookworm
RUN apt-get update && apt-get install -y --no-install-recommends \
    libsqlite3-dev libonig-dev libxml2-dev unzip git \
    && docker-php-ext-install pdo_sqlite mbstring pcntl posix dom xml xmlwriter \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /app
COPY deploy/entrypoint.sh /usr/local/bin/chat-entrypoint
RUN sed -i 's/\r$//' /usr/local/bin/chat-entrypoint && chmod +x /usr/local/bin/chat-entrypoint
ENTRYPOINT ["chat-entrypoint"]
CMD ["php-fpm"]
