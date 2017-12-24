#!/bin/bash

cd $(dirname ${0})/..

COMMAND="${1}"

case "${COMMAND}" in
  help|--help|-h)
    cat <<HELP
Usage: $0 <command> [args]

Commands:
  test        Run the test suite
  help        Display this help message
HELP
    exit 1
    ;;

  test)
    docker-compose run --rm php "${@:1}"
    ;;

  *)
    echo "Unknown command ${COMMAND}"
    exit 1
    ;;
esac
