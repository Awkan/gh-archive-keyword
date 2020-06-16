GH Archive Keyword
==================

## Prerequisites

- Docker
- Docker compose
- Make

## Installation

- **Environment files**

Create your specific `.env` files

```bash
cp .env.dist .env
```

These env files should already work since default values are development values.
However you can edit them in order to make value match with your own. 

- **Initialize project to start development**

The manipulation is simple since a Make script is available
```bash
make initialize
```

Your project must be started and you're now ready to dev ! :computer:

## Basic usage

### Import data from GH Archive

To import data from GH Archive, you can use following line : 
```bash
bin/console app:import:archive <date> # <date> is a YYYY-MM-DD format

# Can be launch thanks to docker
docker-compose exec php bin/console app:import:archive <date>
```

### Check agregated data

If you wanna get some stats on imported data, an endpoint was made for it.
```bash
curl http://127.0.0.1:8080/dashboard
```

Some query parameters are available in order to filter results :

| Parameter | Value      |
|-----------|------------|
| keyword   | `string`   |
| date      | YYYY-MM-DD |

