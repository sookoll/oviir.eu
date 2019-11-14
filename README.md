# Oviir.eu

Website

## Based on pico

```
git clone ...
cd oviir.eu/src
curl -sSL https://getcomposer.org/installer | php
php composer.phar install
cd ..
docker pull webdevops/php-apache
docker run -v $(pwd)/src:/app -p 127.0.0.1:8000:80/tcp -it webdevops/php-apache
```
