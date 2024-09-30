# Epayco Challenge

## Deploy local from docker (All services in one)

### Requirements

---

- Git
- Docker (It will install all these tools for you)
- docker-compose 

### Setup

---

Download zip file and extract it [latest pre-built api release](https://github.com/alnj2010/Regcheq-challenge). Or clone the repository and cd into it.

You must copy the docker env file (`.env.DOCKER`) and rename it to `.env`

```sh
$ cp .env.DOCKER ./.env

```

Now we are ready to run the docker-compose command!

Make sure you are in the root of the api release directory and run:

```sh
$ docker-compose up

```

When the command is ready and the containers running, you can see three instances:

| Service   | LOCALHOST:PORT  |
| --------- | --------------- |
| `MONGODB` | localhost:27017 |
| `API`     | localhost:8000  |

The final step is running the database makefile:

```sh
$ make database-provision -C ./regcheq-db/

```

The latter will fill the databse with documents to test.

Open http://localhost:8000/api-docs/ to view the apidocs in your browser.

