# Epayco Challenge

## Deploy local from docker (All services in one)

### Requirements

---

- Git
- Docker (It will install all these tools for you)
- docker-compose 

### Setup

---

Download zip file and extract it [latest pre-built api release](https://github.com/alnj2010/ePayco-challenge). Or clone the repository and cd into it.

Now we are ready to run the docker-compose command!

Make sure you are in the root directory and run:

```sh
$ docker-compose up -d

```

When the command is ready and the containers running, you can see three instances:

| Service   | LOCALHOST:PORT  |
| --------- | --------------- |
| `SOAP-SERVICE` | localhost:8000 |
| `REST-SERVICE`     | localhost:3000  |
| `MySQL`     | localhost:3306  |

The final step is running the migration

```sh
$ docker compose exec soap-service-container php artisan doctrine:migrations:migrate

```

### Endpoints

| METHOD   | ROUTE  |DESCRIPTION| BODY EXAMPLE|
| --------- | --------------- | --------------- | ---------------|
| `POST` | /api/register| it registers a new client |`{"document": "12345678","email": "a@email.com","phone": "04345345345","name": "myname"}`|
| `PUT`     | /api/charge  | it charges balance to client's wallet |`{"document":"12345678","phone": "04345345345","amount": 300}`|
| `GET`     | /api/check-balance  | it returns the client's balance |`{"document": "12345678","phone": "04345345345"}`|
| `POST`     | /api/purchase  | it sends confirmation email to client|`{"document": "12345678","phone": "04345345345","price":50}`|

NOTE: the file `epayco-challenge.postman_collection` you contain a postman collection.