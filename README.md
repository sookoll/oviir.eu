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
npm start
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
Deploy to server (NB! all content will be cleared, so be careful not to lose configs in server!!!)
```
npm run deploy
```
Deploy partial content
```
npm run deploy -- content/**/* themes/**/*
```
