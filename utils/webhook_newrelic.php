<?php

require(dirname(__FILE__) . '/../config/config.inc.php');

/**
 * GitHub Web Hook that parses commit information and uses
 * that data to notify New Relic for the deployment. This
 * helps us to track differences between deployments.
 */

$newRelicAppId = 2383876;
$payload = $_POST['payload'];
$log = Logger::getLogger(__FILE__);
$log->info('Notifying New Relic About New Deployment...');

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

$session = curl_init();

curl_setopt($session, CURLOPT_SSL_VERIFYHOST, true);
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($session, CURLOPT_POST, true);

$data = array(
    "deployment[application_id]" => $newRelicAppId,
    "deployment[description]" => "New Deployment by GitHub Master Commit",
    "deployment[revision]" => $payload->head_commit->id,
    "deployment[changelog]" => $payload->head_commit->message,
    "deployment[user]" => sprintf('%s <%s>', $payload->pusher->name, $payload->pusher->email),
);

$headers = array(
    'x-api-key:196bcd21c4b2fa5d5c1a6b80ddf7b3bb47a82b1218f1ff0'
);

$url = 'https://rpm.newrelic.com/deployments.xml';

curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
curl_setopt($session, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($session, CURLOPT_URL, $url);

$response = curl_exec($session);

if (curl_errno($session)) {
    $log->error('Notifying New Relic About New Deployment has failed due to: ' . curl_error($session));
}

curl_close($session);

$log->info('Successfully notified New Relic About New Deployment!');
