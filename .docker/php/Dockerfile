# Use a imagem oficial do PHP 8.3
FROM php:8.3-fpm

ARG user=renato.amado
ARG uid=1000

# Copie o arquivo php.ini personalizado para o container
COPY php.ini /usr/local/etc/php/conf.d/docker-php-config.ini

# Instale dependências do sistema
RUN apt update && apt install -y \
    zlib1g-dev \
    g++ \
    git \
    libicu-dev \
    zip \
    libzip-dev \
    unzip \
    && docker-php-ext-install \
    intl \
    opcache \
    pdo \
    pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && apt clean && rm -rf /var/lib/apt/lists/*

# Instale o Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instale o Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Alterar proprietário do diretório para o usuário renato.amado
RUN useradd -G www-data,root -u $uid -d /home/$user $user \
    && mkdir -p /home/$user/.composer \
    && chown -R $user:$user /home/$user

# Defina o diretório de trabalho
WORKDIR /var/www/symfony

# Corrigir permissões
RUN chown -R $user:$user /var/www/symfony

# Defina o usuário padrão para executar comandos no container
USER $user
