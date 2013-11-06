#!/bin/bash

PROJECT_HOME="$(realpath $(dirname $0)/..)"
CREDENTIALS_OVERWRITE="$PROJECT_HOME/config/credentials_overwrite.sh"

DB_HOST="localhost"
DB_NAME="butigotest"
DB_USER="root"
DB_PASS="test"

# If you want to overwrite the variables in this file,
# please create a new file called credentials_overwrite.sh
# in the same folder with above variables
if [ -f $CREDENTIALS_OVERWRITE ]; then
    source $CREDENTIALS_OVERWRITE
fi
