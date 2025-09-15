### init
- `printf "UID=$(id -u)\nGID=$(id -g)" > .env`
- `docker-compose up -d`
- `docker-compose run shipmonk-packing-app bash`
- `composer install && bin/doctrine orm:schema-tool:create && bin/doctrine dbal:run-sql "$(cat data/packaging-data.sql)"`

### before run
- fill DB connection and BinPacking data to `.env` (use example from `.env.example`)

### run
- `php run.php "$(cat sample.json)"`

### adminer
- Open `http://localhost:8080/?server=mysql&username=root&db=packing`
- Password: secret

### Request
```json
{
    "products": [
        {
            "id": 1,
            "width": 3.4,
            "height": 2.1,
            "length": 3.0,
            "weight": 4.0
        }
    ]
}
```

### Response
```json
{
    "minimal_packaging": {
        "id": 4,
        "width": 5.5,
        "height": 6,
        "length": 7.5,
        "max_weight": 30,
        "volume_utilization": 26.54
    }
}
```
