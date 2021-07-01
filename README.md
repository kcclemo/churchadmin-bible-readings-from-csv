# Bible Reading Plan Builder for ChurchAdmin
Generates Bible Reading Posts in WordPress for the ChurchAdmin plugin based on CSV input using esv.org's API.

## To Get Started
1) Obtain an API key from esv.org's API documentation site:
https://api.esv.org/account/create-application/

2) Update the path to `wp-load.php`.
3) Replace `{{ YOUR_KEY }}` with your actual API key.
4) Customize the path to where the audio files are saved by updating the `$audio_url` variable along with the `file_put_contents` line a little lower down.
This path should be in relation to where the script is currently stored.

5) Visit the URL where index.php is located and click the "Build Bible Reading Plan" button.
6) The output should be the resulting text and audio that have been retrieved.
7) Log into your WordPress dashboard and confirm that the Bible Readings have been generated correctly.

Tip: You may want to password protect the directory in which this script is stored to prevent anonymous users from finding it and re-generating your posts over and over again.
