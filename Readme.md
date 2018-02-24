# RPG Simulation
Built with [Laravel](https://laravel.com), [Vue.js](https://vuejs.org/)

## Development

To run, you must install [docker-compose](https://docs.docker.com/compose/). 

Copy the .env file and define App Secret. See Laravel 5.6 [installation guide](https://laravel.com/docs/5.6/installation)

```sh
$ cp .env.template .env 
```

With docker-compose installed, and after cloning this repo:

```sh
$ docker-compose up 
```

The application will run on [http://localhost:8000](http://localhost:8000)