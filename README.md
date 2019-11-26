# Oviir.eu

Website

## Based on pico

```
git clone https://github.com/sookoll/oviir.eu.git
cd oviir.eu/src
curl -sSL https://getcomposer.org/installer | php
php composer.phar install
cd ..
npm i
```
### Dev
```
docker pull webdevops/php-apache
docker run -v $(pwd)/src:/app -p 127.0.0.1:8000:80/tcp -it webdevops/php-apache
```
or
```
php -S localhost:8000 -t src/
```

Debugging IE on Safari

Develop -> User Agent -> Other

```
Internet Explorer 8 Windows XPMozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0)
```

Deploy

Update config file (miuview-api url, dev_mode)
```
npm run build
```
Copy content to server
