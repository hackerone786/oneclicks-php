FROM php:8.2-apache

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    && docker-php-ext-install curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite
RUN a2enmod headers

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Create required directories and set permissions
RUN mkdir -p /var/www/html/cache \
    && mkdir -p /var/www/html/limit-reached \
    && mkdir -p /var/www/html/expired \
    && mkdir -p /var/www/html/media \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/cache

# Configure Apache
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Set default environment variables (can be overridden at runtime)
ENV API_URL=https://your-api-url.com
ENV API_KEY=your-api-key-here
ENV CACHE_EXPIRY=3600
ENV DEBUG=false

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"] 