<?php

require(dirname(__FILE__) . '/../config/config.inc.php');

/**
 * GitHub Web Hook that parses modified & deleted files and
 * sends a purge request to the CDN. The URL of this hook is
 * entered into the settings of the GitHub project.
 */

$payload = $_POST['payload'];
$log = Logger::getLogger(__FILE__);
$log->info('CDN Invalidation (due to GitHub Commit) Started!');

if (! isset($payload)) {
    $msg = 'Payload Not Found';

    header('HTTP/1.1 400 Bad Request');

    $log->fatal($msg);

    die($msg);
}

try {
    $payload = json_decode($payload);
} catch (Exception $e) {
    $msg = 'Unable to parse JSON data';

    header('HTTP/1.1 500 Server Error');

    $log->fatal($msg);

    die($msg);
}

if ($payload->ref !== 'refs/heads/master') {
    $msg = 'Only commits to master are considered. Skipping this one..';
    $log->info($msg);
    die($msg);
}

$paths = array();
$session = curl_init();
$valid_extensions = array('js', 'css', 'jpg', 'png', 'gif', 'txt');

curl_setopt($session, CURLOPT_SSL_VERIFYHOST, true);
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

foreach ($payload->commits as $commit) {
    foreach ($commit->modified as $path) {
        if (! in_array($path, $paths)) {
            $extension = pathinfo($path, PATHINFO_EXTENSION);

            if (! in_array($extension, $valid_extensions)) {
                $log->debug('Unknown extension, skipping: ' . $path);

                continue;
            }

            $url = sprintf('https://purge.mncdn.com/?username=%s&pass=%s&file=%s',
                MEDIANOVA_USERNAME, MEDIANOVA_PASSWORD, $path);
            $paths[] = $path;

            $log->info(sprintf("Invalidating from CDN: %s\n", $path));

            curl_setopt($session, CURLOPT_URL, $url);

            $response = curl_exec($session);

            if (curl_errno($session)) {
                $log->error('Invalidation failed due to error: ' . curl_error($session));
            }
        }
    }

    foreach ($commit->removed as $path) {
        if (! in_array($path, $paths)) {
            $extension = pathinfo($path, PATHINFO_EXTENSION);

            if (! in_array($extension, $valid_extensions)) {
                $log->debug('Unknown extension, skipping: ' . $path);

                continue;
            }

            $url = sprintf('https://purge.mncdn.com/?username=%s&pass=%s&file=%s',
                MEDIANOVA_USERNAME, MEDIANOVA_PASSWORD, $path);
            $paths[] = $path;

            $log->info(sprintf("Invalidating from CDN: %s\n", $path));

            curl_setopt($session, CURLOPT_URL, $url);

            $response = curl_exec($session);

            if (curl_errno($session)) {
                $log->error('Invalidation failed due to error: ' . curl_error($session));
            }
        }
    }
}

curl_close($session);

$log->info('CDN Invalidation (due to GitHub Commit) Ended!');
