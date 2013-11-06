#!/bin/bash

projectHome="$(realpath $(dirname $0)/..)"

mailFrom="Butigo Postman <postman@butigo.com>"
mailTo="managers@butigo.com"
subject="Selenium Test Results"

mocha="$projectHome/test/mocha -C"
testResults="$($mocha)"

message="
Dear All,

You may find latest Selenium Test Results below for your attention.

Best Regards,
Butigo IT

-------------------------------------
$testResults
"

EMAIL="$mailFrom" mutt -s "$subject" -- "$mailTo" <<< "$message"
