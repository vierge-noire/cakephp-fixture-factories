#!/bin/bash

DRIVER=$1;
USER=$2;
PWD=$3;
HOST=$4;
DRIVER_NAMESPACE='Cake\Database\Driver\'

echo "Starting PHPUNIT tests"

if [ -n "$DRIVER" ]; then
  DRIVER="$DRIVER_NAMESPACE$DRIVER"
  export DB_DRIVER=$DRIVER
  echo "With driver: $DRIVER"
else
  echo "Using default driver $DB_DRIVER"
fi

if [ -n "$HOST" ]; then
  export DB_HOST=$HOST
  echo "On host: $HOST"
else
  echo "Using default host"
fi

if [ -n "$USER" ]; then
  export DB_USER=$USER
  echo "With user: $USER"
else
  echo "Using default user $DB_USER"
fi

if [ -n "$PWD" ]; then
  export DB_PWD=$PWD
  echo "With password: $PWD"
else
  echo "Using default password $DB_PWD"
fi

./vendor/bin/phpunit