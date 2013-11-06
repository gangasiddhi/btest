#!/bin/bash

PROJECT_HOME="$(realpath $(dirname $0)/..)"

CREDENTIALS_PATH="$PROJECT_HOME/config/credentials.sh"
SQL_PATH="$PROJECT_HOME/modules/moderation/sql/moderation_report.sql"
REPORT_PATH="$PROJECT_HOME/temp/reports/moderation_report.csv"

SQL_TEMPLATE=$(<"$SQL_PATH")
SQL_SCRIPT="${SQL_TEMPLATE/REPORT_PATH/$REPORT_PATH}"

NOW=$(date +"%d.%m.%Y, %H:%M")
MAIL_FROM="Butigo Postman <postman@butigo.com>"
MAIL_TO="operations@butigo.com"
SUBJECT="Moderation Report of $NOW"
MESSAGE="
Dear All,

You can find moderation report of $NOW attached.

Best Regards,
Butigo IT
"

source "$CREDENTIALS_PATH"

if [ -f "$REPORT_PATH" ]; then
    rm "$REPORT_PATH"
fi

mysql -u "$DB_USER" -p"$DB_PASS" -h "$DB_HOST" -e "$SQL_SCRIPT" "$DB_NAME"

if [ -f $REPORT_PATH ]; then
    echo "Moderation report has successfully been created! Mailing relevant people.."

    EMAIL="$MAIL_FROM" mutt -s "$SUBJECT" -a "$REPORT_PATH" -- "$MAIL_TO" <<< "$MESSAGE"
fi
