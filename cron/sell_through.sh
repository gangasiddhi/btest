#!/bin/bash

PROJECT_HOME="$(realpath $(dirname $0)/..)"

CREDENTIALS_PATH="$PROJECT_HOME/config/credentials.sh"
SQL_PATH="$PROJECT_HOME/cron/sql/sell_through.sql"
REPORT_PATH="$PROJECT_HOME/temp/reports/sell_through.csv"

SQL_TEMPLATE=$(<"$SQL_PATH")
SQL_SCRIPT="${SQL_TEMPLATE/REPORT_PATH/$REPORT_PATH}"

NOW=$(date +"%d.%m.%Y, %H:%M")
MAIL_FROM="Butigo Postman <postman@butigo.com>"
MAIL_TO="managers@butigo.com"
SUBJECT="Sell Through Report of $NOW"
MESSAGE="
Dear All,

You can find Sell Through report of $NOW attached.

Best Regards,
Butigo IT
"

source "$CREDENTIALS_PATH"

if [ -f "$REPORT_PATH" ]; then
    rm "$REPORT_PATH"
fi

mysql -u "$DB_USER" -p"$DB_PASS" -h "$DB_HOST" -e "$SQL_SCRIPT" "$DB_NAME"

if [ -f $REPORT_PATH ]; then
    echo "Sell Through report has successfully been created! Mailing relevant people.."

    EMAIL="$MAIL_FROM" mutt -s "$SUBJECT" -a "$REPORT_PATH" -- "$MAIL_TO" <<< "$MESSAGE"
fi
