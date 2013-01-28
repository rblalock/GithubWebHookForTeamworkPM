<?php
/**
 * GitHub Post to TeamworkPM
 * @version 1.1
 */

// CHANGE ME - Root URL for the Teamwork API call
$URL = ''; // e.g. https://yoursite.teamworkpm.net/tasks/

// CHANGE ME - TeamworkPM user token used to post a comment on behalf of the Github commit
$USER_TOKEN = '';

try {
    // Convert the post data
    $data = stripslashes( $_POST['payload'] );
    $postdata = json_decode($data);

    if($_POST['payload'] && $postdata) {
        // Iterate through each commit to see if we have a related task
        foreach ($postdata->commits as $commit) {
            // Format message data
            $commidID   = $commit->id;
            $comment    = $commit->message;
            $url        = $commit->url;
            $timestamp  = $commit->timestamp;
            $author     = $commit->author->name;

            // Get any commit messages that have a # tag (points ot a resource ID in Teamwork)
            preg_match_all('/#([A-Za-z0-9_]+)/', $comment, $matches);
            // Remove the first index since it's the original
            $resourceID = array_pop($matches);

            // Format the message that will post to Teamwork
            $body = $comment . "\n\n" .
                    $url . "\n\n" .
                    "committed by " . $author . " on " . date('m/d/Y', strtotime($timestamp));
            $params = array(
                'comment' => array(
                    'body' => $body
                )
            );
            $postData = json_encode($params);

            if(count($resourceID) > 0) {
                // Iterate through each hash tag / resource and make a request
                foreach ($resourceID as $resource) {
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
                        CURLOPT_HTTPHEADER => $headers
                    ));
                    curl_setopt($c, CURLOPT_URL, $URL . $resource . '/comments.json');
                    curl_setopt($c, CURLOPT_POSTFIELDS, $postData );

                    $response = curl_exec($c);
                    $httpCode = curl_getinfo($c, CURLINFO_HTTP_CODE);
                    curl_close($c);
                }
            }
        }
    }

} catch (Exception $e) {
    print_r($e);
}
