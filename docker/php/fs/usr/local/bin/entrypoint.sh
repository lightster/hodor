#!/bin/bash

ENV="${1}"
COMMAND="${2}"

wait_for_it ()
{
    local host=$1
    local port=$2

    bash /usr/local/bin/wait-for-it.sh -q "${host}:${port}"
    local exit_code=$?
    if [ $exit_code -eq 0 ]; then
        return
    fi

    echo "Unable to connect to '${host}' port ${port}"
    exit $exit_code
}

wait_on_depends ()
{
    if [ "docker" != "${ENV}" ]; then
        return
    fi

    wait_for_it postgres 5432
    wait_for_it rabbitmq 5672
}

case "${COMMAND}" in
    test)
        wait_on_depends
        php vendor/bin/phpunit
        ;;

    install)
        composer install

        if [ "docker" == "${ENV}" ]; then
            php /hodor/bin/hodor.php test:generate-config --postgres-host=postgres --rabbitmq-host=rabbitmq
        else
            php /hodor/bin/hodor.php test:generate-config
        fi
        ;;

    bash)
        /bin/bash
        ;;

    *)
        echo $"Usage: $0 {docker|shell} {test|install|bash}"
        exit 1
esac
