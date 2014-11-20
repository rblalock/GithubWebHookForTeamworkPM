<?php
/**
 * GitHub Post to TeamworkPM
 * @version 1.2
 */
require_once 'config.php';


$headers = @apache_request_headers();
$github_event = @$headers[ 'X-GitHub-Event' ];
if('ping' == $github_event) {
    echo 'Ping. URL: ' . COMMENT_URL;
}

try {
    // Convert the post data
    $data = stripslashes( $_POST['payload'] );
    $postdata = json_decode($data);

    if($_POST['payload'] && $postdata) {
        // Iterate through each commit to see if we have a related task
        foreach ($postdata->commits as $commit) {
            // Format message data
            $commitID   = $commit->id;
            $comment    = $commit->message;
            $url        = $commit->url;
            $timestamp  = $commit->timestamp;
            $author     = $commit->author->name;
            $repo_name  = $commit->repository->full_name;

            // Get any commit messages that have a # tag (points ot a resource ID in Teamwork)
            preg_match_all('/#([A-Za-z0-9_]+)/', $comment, $matches);
            // Remove the first index since it's the original
            $resourceID = array_pop($matches);

            // Format the message that will post to Teamwork
            $body = strtr(COMMENT_TEMPLATE, array(
                        '{COMMENT}' => $comment,
                        '{URL}'     => $url,
                        '{COMMIT_NAME}' => $repo_name .'@'. substr($commitID, 0, 7),
                        '{AUTHOR}'  => $author,
                        '{DATE}'    => date(DATE_FORMAT, strtotime($timestamp)) ));
            $params = array(
                'comment' => array(
                    'body' => $body
                )
            );
            $postData = json_encode($params);

            if(count($resourceID) > 0) {
                // Iterate through each hash tag / resource and make a request
                foreach ($resourceID as $resource) {
                    if($resource < MIN_RESOURCE_ID) {
                        echo "Task #$resource: skipping" . PHP_EOL;
                        continue;
                    }

                    // Create the comment
                    $c = curl_init();
                    $headers = array(
                        'Authorization: BASIC '. base64_encode($USER_TOKEN . ':xxxzzz'),
                        'Content-Type: application/json',
                        'Accept: application/json'
                    );
                    curl_setopt_array($c, array(
                        CURLOPT_RETURNTRANSFER => TRUE,
                        CURLOPT_HEADER         => TRUE,
                        CURLOPT_SSL_VERIFYHOST => FALSE,
                        CURLOPT_SSL_VERIFYPEER => FALSE,
                        CURLOPT_POST => TRUE,
                        CURLOPT_PROXY => HTTP_PROXY,
                        CURLOPT_HTTPHEADER => $headers
                    ));
                    $teamwork_url = sprintf( COMMENT_URL, $resource );
                    curl_setopt($c, CURLOPT_URL, $teamwork_url);
                    curl_setopt($c, CURLOPT_POSTFIELDS, $postData );

                    $response = curl_exec($c);
                    $httpCode = curl_getinfo($c, CURLINFO_HTTP_CODE);
                    curl_close($c);

                    echo "Task #$resource ($httpCode): " . $teamwork_url . PHP_EOL;
                }
            }
        }
    }

} catch (Exception $e) {
    print_r($e);
}
