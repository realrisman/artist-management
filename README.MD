# Installation
Project is based on [Symfony](http://symfony.com/) and [Angular](https://angular.io/) ([Angular-cli](https://cli.angular.io/))

First, [get Composer](https://getcomposer.org/download/), if you don't already use it.

Next, run the following command:

```bash
composer install
```
Then create enviroment file `.env` (see `.env.sample` for reference).

Create Database
```bash
php bin/console doctrine:database:create
```

Run Migration
```bash
php bin/console doctrine:migrations:migrate
```

Run Server
```bash
php bin/console server:run
```

Then switch to `frontend` directory and run
```bash
npm install
```

To run local development
```bash
ng serve --proxy=proxy.conf.json --delete-output-path=false
```

To create production build run
```bash
ng build --prod
```
add&commit generated files from `public` directory

# Installation via Docker
First, [install Docker Desktop](https://www.docker.com/products/docker-desktop), if you don't already use it.

Next, [install Docker Compose](https://docs.docker.com/compose/install/). If you're on Windows or Mac you can skip this step because Docker Desktop includes Compose along with other Docker apps.

Make sure you have created `.env` (see `.env.dist` for reference) and modified credentials.

Then run the following command inside the project directory to build containers

```bash
docker-compose up -d --build
```

Each time you want to run containers without building exclude `--build command`.

Next, SSH into php container. Run

```bash
docker exec -it artist-management-php /bin/bash
```

and run

```bash
composer install
```

Then create the database. Run
```bash
php bin/console doctrine:migrations:migrate
```
to create tables in the database.

# Built-in commands
Create user with given username
```bash
php bin/console app:create-user [username]
```