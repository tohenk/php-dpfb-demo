HOW TO RUN THIS DEMO
====================

* Clone this project
* Get composer and install dependencies

```
php composer.phar install
```
* Update sub module

```
git submodule update --merge --remote -- www/cdn
```

* Run PHP internal web server

```
php -S localhost:80 -t www
```

* Browse the demo at localhost:80
