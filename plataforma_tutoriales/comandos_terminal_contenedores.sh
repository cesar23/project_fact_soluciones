

docker exec mariadb1 bash -c "mysql -u root -p12345 drop ifexist database smart1"
docker exec mariadb1 bash -c "mysql -u root -p12345 drop ifexist database tenancy_*"

docker exec mariadb1 bash -c "mysql -u root -pWPsOd4xPLL4nGRnOAHJp -e 'DROP DATABASE IF EXISTS smart1;'"
docker exec mariadb1 bash -c "mysql -u root -pWPsOd4xPLL4nGRnOAHJp -e 'tenancy_%';'"
docker exec mariadb1 bash -c "mysql -u root -pWPsOd4xPLL4nGRnOAHJp -e 'SHOW DATABASES LIKE 'tenancy_%';;'"

docker exec mariadb1 mysql -u root -pWPsOd4xPLL4nGRnOAHJp -e "DROP DATABASE IF EXISTS smart1;"
docker exec mariadb1 mysql -u root -pWPsOd4xPLL4nGRnOAHJp -e "SHOW DATABASES LIKE 'tenancy_%';"

SHOW DATABASES;


# :::::::: 1. clouflare


docker compose -f stack-facturador-smart/smart1/docker-compose.yml down
docker compose -f stack-facturador-smart/smart1/docker-compose.yml build fpm1 --no-cache
docker compose -f stack-facturador-smart/smart1/docker-compose.yml up -d


docker compose -f stack-facturador-smart/smart1/docker-compose.yml up -d
docker compose -f stack-facturador-smart/utils/docker-compose.yml up -d
docker compose -f stack-facturador-smart/cloudflare/docker-compose.yml up -d
docker compose -f stack-facturador-smart/npm/docker-compose.yml up -d



docker compose -f stack-facturador-smart/smart1/docker-compose.yml down
docker compose -f stack-facturador-smart/utils/docker-compose.yml down
docker compose -f stack-facturador-smart/cloudflare/docker-compose.yml down
docker compose -f stack-facturador-smart/npm/docker-compose.yml down



docker exec fpm1 bash -c "composer install"


docker compose build fpm1 --no-cache