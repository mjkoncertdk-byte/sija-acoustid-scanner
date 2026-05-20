FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    libchromaprint-tools \
    ffmpeg \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . /app

CMD php -S 0.0.0.0:$PORT -t /app
