A Github Webhook for TeamworkPM
==========================

To use, clone to a Web server. Then:

1. Copy `config.TEMPLATE.php` to `config.php`
2. In `config.php`, change user token to the user's token that will post on Github's behalf.
3. Change the URL to your TeamworkPM's url (including the resource you'd like the commits to post to, e.g. /task/) and you're ready to go.
4. In the `Settings > Web hooks` page for your project on GitHub, choose `Add webhook`.
5. Set `Content type` to `application/x-www-form-urlencoded`. Leave `Secret` blank.

When committing to Github you can reference a TeamworkPM task via it's ID with `#`.  E.g. `"Fixes #123456"` where #123456 is the TeamworkPM Task ID (or as the API calls it, the Teamwork Resource ID - https://yoursite.teamworkpm.net/tasks/123456).