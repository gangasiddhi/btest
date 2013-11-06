#! /bin/bash

SERIES=$1
START=$2
END=$3

if [ -z "$SERIES" -o -z "$START" -o -z "$END" ]; then
    echo "Usage: ./mikro.sh BL 213132 434144"
    exit 1
fi

SERIES_LC=$(echo $SERIES | awk '{print tolower($0)}')
SERIES_UC=$(echo $SERIES | awk '{print toupper($0)}')

for i in $(seq $START $END); do
    path1="./download/orders_xml/all/$SERIES_UC$i.xml"
    path2="./download/orders_xml/all/$SERIES_LC$i.xml"
    newPath="./download/orders_xml/mikro/$SERIES_UC$i.xml"

    if [[ -f $path1 ]]; then
        cp -v $path1 $newPath
    elif [[ -f $path2 ]]; then
        cp -v $path2 $newPath
    else
        echo "$path1 cannot be found. Searching for it.."
    fi
done
