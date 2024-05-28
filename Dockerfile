FROM php:8.3-cli-alpine as sio_test

# Обновление индекса и установка необходимых пакетов
RUN apk update && apk upgrade && apk add --no-cache \
    git \
    zip \
    bash \
    sqlite \
    sqlite-dev

# Установка PHP расширений
RUN docker-php-ext-install pdo pdo_sqlite

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Настройка пользователя для PHP приложения
ARG USER_ID=1000
RUN adduser -u ${USER_ID} -D -H app
USER app

# Копирование файлов приложения
COPY --chown=app . /app
WORKDIR /app

EXPOSE 8337

CMD ["php", "-S", "0.0.0.0:8337", "-t", "public"]
