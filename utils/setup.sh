#!/bin/bash
#
# Handles things you should do after a fresh clone from Github
#
# Author: Alper Kanat <alper@butigo.com>

# ------------------------------------------------------------------------
# VARIABLES
# ------------------------------------------------------------------------

PROJECT_HOME="$(realpath $(dirname $0)/..)"
ENV_FILE="$PROJECT_HOME/config/environment.inc.php"

APACHE_USER=$(ps axho user,comm|grep -E "httpd|apache"|uniq|grep -v "root"|awk 'END {if ($1) print $1}')

FILES_TO_BE_COPIED=(
    'settings.inc.php'
    'cron_settings.inc.php'
    'modules_list.xml'
    'collection*.xml'
    'config.xml'
    'links.xml'
    'de.php'
    'en.php'
    'es.php'
    'fr.php'
    'it.php'
    'tr.php'
)

FOLDERS_TO_BE_COPIED=(
    'blog'
    'ct/tmp'
    'img/c'
    'img/l'
    'img/p'
    'img/tmp'
    'download'
    'upload'
)

IMAGE_PERMISSIONS=(
    'img/c'
    'img/l'
    'img/p'
    'img/tmp'
)

PERMISSIONS=(
    'collection*.xml'
    'config.xml'
    'links.xml'
    'de.php'
    'en.php'
    'es.php'
    'fr.php'
    'it.php'
    'tr.php'
    'slides*'
)

# ------------------------------------------------------------------------
# FUNCTIONS
# ------------------------------------------------------------------------

foldersToBeCopied() {
    echo "Below files should be copied from wherever they exist:"
    echo

    for file in ${FILES_TO_BE_COPIED[@]}; do
        echo $file
    done

    echo
    echo "Below folders should be copied to their respective locations:"
    echo

    for folder in ${FOLDERS_TO_BE_COPIED[@]}; do
        echo $folder
    done
}

copyFromOldToNew() {
    oldProjectPath="$1"

    if [[ -z $oldProjectPath || ( ${#FILES_TO_BE_COPIED[@]} == 0 && ${#FOLDERS_TO_BE_COPIED[@]} == 0 ) ]]; then
        return
    fi

    for f in ${FILES_TO_BE_COPIED[@]}; do
        for fp in $(find $oldProjectPath -name $f); do
            newPath="$PROJECT_HOME${fp/$oldProjectPath/}"

            echo "$fp -> $newPath"

            cp $fp $newPath
        done
    done

    for f in ${FOLDERS_TO_BE_COPIED[@]}; do
        oldPath="$oldProjectPath/$f/*"
        newPath="$PROJECT_HOME/$f"

        echo "$oldPath -> $newPath"

        cp -r $oldPath $newPath
    done

    # copying module slide directories..
    for f in $(find "$oldProjectPath/modules" -name 'slides*'); do
        fp=${f/$oldProjectPath\//}
        newPath="$PROJECT_HOME/`dirname $fp`"

        echo "$f -> $newPath"

        cp -r $f $newPath
    done

    # copying modules images etc..
    for f in $(find "$oldProjectPath/modules" -name '*.jpg' -o -name '*.png' -o -name '*.gif'|grep -v slides); do
        fp=${f/$oldProjectPath\//}
        newPath="$PROJECT_HOME/$fp"

        echo "$f -> $newPath"

        cp -r $f $newPath
    done
}

fixPermissions() {
    CT_TMP="$PROJECT_HOME/ct/tmp"
    DOWNLOAD_DIR="$PROJECT_HOME/download"
    DOWNLOAD_SUBFOLDERS=(
        "acc"
        "gc"
        "temp"
        "orders_xml"
        "product-feed"
    )
    HTACCESS="$PROJECT_HOME/.htaccess"
    LOG_DIR="$PROJECT_HOME/log"
    LOG_SUBDIRS=('order_xml_logs' 'pgf' 'pgg' 'pgtw' 'pgy')
    MODULE_DIR="$PROJECT_HOME/modules"
    MODULES_LIST_FILE="$PROJECT_HOME/config/modules_list.xml"
    SITEMAP="$PROJECT_HOME/sitemap.xml"
    SMARTY1_CACHE="$PROJECT_HOME/tools/smarty/cache"
    SMARTY1_COMPILE="$PROJECT_HOME/tools/smarty/compile"
    SMARTY2_CACHE="$PROJECT_HOME/tools/smarty_v2/cache"
    SMARTY2_COMPILE="$PROJECT_HOME/tools/smarty_v2/compile"
    THEMES_CACHE="$PROJECT_HOME/themes/butigo/cache"
    UPLOAD_DIR="$PROJECT_HOME/upload"

    # ct/tmp permissions
    echo "Fixing permissions of: $CT_TMP"
    chmod 777 $CT_TMP

    # modules list file permissions
    echo "Fixing permissions of: $MODULES_LIST_FILE"
    chgrp $APACHE_USER $MODULES_LIST_FILE
    chmod g+wst $MODULES_LIST_FILE

    # log folder permissions
    echo "Fixing permissions of: $LOG_DIR"
    chgrp $APACHE_USER $LOG_DIR
    chmod g+wst $LOG_DIR

    for subdir in ${LOG_SUBDIRS[@]}; do
        temp="$LOG_DIR/$subdir"

        echo "Fixing permissions of: $temp"

        chgrp $APACHE_USER $temp
        chmod g+wst $temp
    done

    # themes cache permissions
    echo "Fixing permissions of: $THEMES_CACHE"
    chgrp $APACHE_USER $THEMES_CACHE
    chmod g+wst $THEMES_CACHE

    # smarty cache permissions
    echo "Fixing permissions of: $SMARTY1_CACHE"
    echo "Fixing permissions of: $SMARTY1_COMPILE"
    chgrp $APACHE_USER $SMARTY1_CACHE
    chgrp $APACHE_USER $SMARTY1_COMPILE
    chmod g+wst $SMARTY1_CACHE
    chmod g+wst $SMARTY1_COMPILE

    echo "Fixing permissions of: $SMARTY2_CACHE"
    echo "Fixing permissions of: $SMARTY2_COMPILE"
    chgrp $APACHE_USER $SMARTY2_CACHE
    chgrp $APACHE_USER $SMARTY2_COMPILE
    chmod g+wst $SMARTY2_CACHE
    chmod g+wst $SMARTY2_COMPILE

    # .htaccess file permissions
    echo "Fixing permissions of: $HTACCESS"
    touch $HTACCESS
    chgrp $APACHE_USER $HTACCESS
    chmod g+wst $HTACCESS

    # sitemap.xml
    echo "Fixing permissions of: $SITEMAP"
    touch $SITEMAP
    chgrp $APACHE_USER $SITEMAP
    chmod g+wst $SITEMAP

    # download folder permissions
    for f in ${DOWNLOAD_SUBFOLDERS[@]}; do
        tmp="$DOWNLOAD_DIR/$f"

        echo "Fixing permissions of: $tmp"
        chgrp -R $APACHE_USER $tmp
        chmod g+wst $tmp
    done

    # upload folder permissions
    echo "Fixing permissions of: $UPLOAD_DIR"
    chown -R logistics_ftp:logistics_ftp $UPLOAD_DIR
    chmod g+wst $UPLOAD_DIR

    # module permissions
    for dir in $MODULE_DIR/*; do
        echo "Fixing permissions of: $dir"
        chgrp $APACHE_USER $dir
        chmod g+wst $dir
    done

    # image permissions
    for dir in ${IMAGE_PERMISSIONS[@]}; do
        fp="$PROJECT_HOME/$dir"

        echo "Fixing permissions of: $fp"

        chgrp -R $APACHE_USER $fp
        chmod -R g+wst $fp
    done

    # find and correct file permissions
    for pattern in ${PERMISSIONS[@]}; do
        find $PROJECT_HOME -name $pattern \
            -exec echo "Fixing permissions of: {}" \; \
            -exec chgrp -R $APACHE_USER '{}' \; \
            -exec chmod -R g+wst '{}' \;
    done
}

# ------------------------------------------------------------------------
# APPLICATION LOGIC
# ------------------------------------------------------------------------

if [ -z $APACHE_USER ]; then
    echo 'APACHE_USER cannot be found! Exiting..'
    exit 1
fi

echo -n 'Do you have an existing/running copy of Butigo? (y/n): '

read existing

if [[ $existing == 'y' ]]; then
    echo
    echo -n "Enter its path if you would like to copy all necessary files from there: "

    read oldCopyPath

    if [ -d $oldCopyPath -a -d "$oldCopyPath/modules" ]; then
        echo
        echo "Your existing copy of Butigo has been verified! Starting copying.."
        echo

        copyFromOldToNew $oldCopyPath

        BASE_URL="define('__PS_BASE_URI__', '\/~$(whoami)\/$(basename $PROJECT_HOME)\/');"
        SETTINGS_FILE="$PROJECT_HOME/config/settings.inc.php"

        sed -i "3s/.*/$BASE_URL/" $SETTINGS_FILE

        echo
    else
        echo
        echo "Given path is not a Butigo copy! Skipping copying from old copy.."
        echo "Nonetheless here is the list of files need to be copied:"
        echo

        foldersToBeCopied

        echo
    fi
else
    echo
    echo "You need to find those files from someone else and copy into yours then!"
    echo "Here is the list of files need to be copied:"
    echo

    foldersToBeCopied

    echo
fi

fixPermissions

if [ ! -f $ENV_FILE ]; then
    echo
    echo -n "Is this a development machine? (y/n): "

    read isDev

    if [[ $isDev == "y" ]]; then
    cat > $ENV_FILE <<EOF
<?php

define('_BU_ENV_', 'development');

?>
EOF
    fi

    echo
fi

echo '-----------------------------------------------------------------------'
echo
echo 'Your local copy of Butigo has successfully installed!'
echo
echo 'Things to do now:'
echo
echo '1) Go to BackOffice and generate .htaccess file in Tools/Generators'
