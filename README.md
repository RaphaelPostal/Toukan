# Toukan

SAAS de prise de commande
## Installation

### Step 1 : 
Clone the repo

### Step 2 :
in dev :
```bash
docker-compose up -d --build
```
or in production :
```bash
docker-compose up -d -f docker-compose.prod.yml --build
```

### Step 3 :
```bash
docker exec -t www_docker_toukan composer install
docker exec -t www_docker_toukan npm install
docker exec -t www_docker_toukan npm run build
docker exec -t www_docker_toukan php bin/console d:s:u -f
```