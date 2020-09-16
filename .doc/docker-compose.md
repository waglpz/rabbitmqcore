# Docker Documentation

* [Docker compose](docker-compose.md)
* [Development](development.md)
* [Database](db.md)
* [APP](app.md)

## Installation

The project requires [Docker] and [Docker Compose] to be installed.
Please refer to the online documentation for installation instructions 
depending on your host OS.

Described is installation on Ubuntu

[Docker]: https://docs.docker.com/install/linux/docker-ce/ubuntu/
[Docker compose]: https://docs.docker.com/compose/install/

_(!) all commands run from project directory_

## Preparation needs before building images

Before building docker services stack, please run from project directory 
(if this was not already done from composer post script):
###### Bash
```bash
printf "APPUID=$(id -u)\nAPPUGID=$(id -g)\n" > .env
```
###### Fish
```fish
printf APPUID=(id -u)\nAPPUGID=(id -g)\n > .env
```

After them setting up all necessary values of ports in the `.env` file.
These should be mapped from the host APP/DB to docker service accordingly to your needs.
Eg if on your host an Apache server running on port 80 then you should 
make a change in `.env`. For eg `APPPORT=8081`, so get Apache from Docker 
not in conflict with Apache on the host.


### Compiling docker service images

###### Bash and Fish same
```bash
docker-compose build --parallel --force-rm --no-cache --pull
```

### Start the service stack

```bash
docker-compose up -d
```

### Display docker services state
```bash
docker-compose ps
```

### Expose logs from running docker services
```bash
docker-compose logs --tail 10 -f
```

## Working with the services

Any commands can be executed from within the container using shell, 
or by using `docker-compose exec`.

To get a shell prompt inside of the running APP container:
###### Bash
```bash
docker-compose exec --user $(id -u):$(id -g) app bash
```
###### Fish
```fish
docker-compose exec --user (id -u):(id -g) app bash
```

Example command to run from APP container shell and host:

```bash
php -v
# or outside service container
docker-compose exec --user $(id -u):$(id -g) app php -v
```

## Accessing the services

There are few containers running in the current setup.
The docker compose environment creates a network using the `10.120.*.0/24` subnet.
For current IP name mapping look in `docker-compose.yml`. These should be added 
local in `/etc/hosts` to. Afterwords you cann access services by the  domain name.


