# How to Run This Demo

* Clone this project
* Get composer and install dependencies

```sh
php composer.phar install
```
* Update sub module

To update sub module, issue this command only _once_:

```sh
git submodule update --init --merge --remote -- www/cdn
```

Otherwise, do this to update the sub module ever since:

```sh
git submodule update --merge --remote -- www/cdn
```

* Run PHP internal web server

```sh
php -S localhost:80 -t www
```

* Browse the demo at localhost:80
