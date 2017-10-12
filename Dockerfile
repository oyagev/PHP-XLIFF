FROM php:7.0-cli

# Install packages

RUN groupadd oht \
    && useradd --home /home/oht -m -N --uid 1000 oht -g oht\
    && mkdir /workspace \
    && chown -R oht:oht /workspace \
    && apt-get clean \
    && apt-get update \
    && apt-get install -y git

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/ \
    && ln -s /usr/local/bin/composer.phar /usr/local/bin/composer

USER oht
WORKDIR /home/oht
