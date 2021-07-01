<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="robots" content="noindex, nofollow, nosnippet">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bible Reading Plan Builder</title>
<?php
    if (!empty($_GET['build'])) {
         // require wp-load.php to use built-in WordPress functions
        require_once("../../wp-load.php");

        // Authorization token needed to access the API
        $auth_token = 'Authorization: Token {{ YOUR_KEY }}';

        // Import raw plan data from csv.
        $rows = array_map('str_getcsv', file('reading-plan-input.csv'));
        $header = array_shift($rows);
        $plan = [];

        // Set up URL pieces that will need to be sent to the API.
        $html_base_url = 'https://api.esv.org/v3/passage/html/?q=';

        // Optional parameters to customize the returned output.: https://api.esv.org/docs/passage-html/
        $html_options = '&include-passage-references=true&include-footnotes=false&inline-styles=true&wrapping-div=true&div-classes=ns-reading&include-book-titles=true&include-audio-link=false';
        $audio_base_url = 'https://api.esv.org/v3/passage/audio/?q=';

        foreach ($rows as $row) {
            $plan[] = array_combine($header, $row);
        }

        foreach ($plan as $reading) {
            // WordPress Post Information
            $postType = 'bible-readings';
            $userID = 5;
            $postStatus = 'future';

            $title = $reading['title'];
            $date = $reading['date'];
            $nt = $reading['new-testament'];
            $ot = $reading['old-testament'];
            $sermon = $reading['sermon'];
            $bonus = !empty($reading['bonus']) ? ';' . $reading['bonus'] : '';
            $html = $html_base_url . $nt . ';' . $ot . ';' . $sermon . $bonus . $html_options;
            $audio_api_url = $audio_base_url . $nt . ';' . $ot . ';' . $sermon . $bonus;
            $audio_url = '/bible/' . strtolower(date('F-d-Y', strtotime($date))) . '.mp3';
            $content = '
                <style>
                    .ns-reading {
                        margin: 0;
                        padding: 0;
                        width: 100%;
                    }
                </style>
                <audio src="' . $audio_url . '" controls preload="auto" style="width: 100%;"></audio>
                <br>
                <a href="" target="_blank" rel="noopener" style="margin: 2em 0;">Discuss today\'s reading.</a>
                <br>
            ';

            // Build text of the passage
            $scripture = curl_init($html);

            curl_setopt($scripture, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($scripture, CURLOPT_USERAGENT, 'NorthStarBibleReadingPlanBuilder/1.0 (kirk@northstarbaptistchurch.org)');
            curl_setopt($scripture, CURLOPT_HTTPHEADER,
                [
                    'Content-Type: application/json',
                    $auth_token
                ]
            );

            $data = curl_exec($scripture);

            curl_close($scripture);

            $decoded_scripture = json_decode($data, true);

            foreach ($decoded_scripture['passages'] as $passage) {
                $content .= $passage;
            }

            echo $content;

            // Download audio file
            $audio = curl_init($audio_api_url);

            curl_setopt($audio, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($audio, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($audio, CURLOPT_HTTPHEADER, [$auth_token]);
            curl_setopt($audio, CURLOPT_FOLLOWLOCATION, true);

            $result = curl_exec($audio);

            curl_close($audio);

            // This is where the audio files will be stored on the server.
            file_put_contents('../' . strtolower(date('F-d-Y', strtotime($date))) . '.mp3', $result);

            $new_post = [
                'post_title' => $title,
                'post_content' => $content,
                'post_status' => $postStatus,
                'post_date' => date('Y-m-d H:i:s', strtotime($date)),
                'post_author' => $userID,
                'post_type' => $postType,
                'comment_status' => 'closed',
                'ping_status' => 'closed',
            ];

            wp_insert_post($new_post);
        }

        echo '<br>==========================<br>';
        echo 'Finished.';
        echo '<br>==========================<br>';
        echo '<a href="/bible/update-plan/">Done</a>';
    } else {
?>
</head>
<body>
    <p>Once you have updated <code>reading-plan-input.csv</code> and uploaded it to the server, click this button to begin building the reading plan.</p>
    <form action="index.php" method="get">
        <input type="hidden" name="build" value="run">
        <input type="submit" value="Build Bible Reading Plan">
    </form>
</body>
</html>
<?php
    }
?>
