A Github Webhook for TeamworkPM
==========================

First you need to change two variables: 
1. Change user token to the user's token that will post on Github's behalf. 
2. Change the URL to your TeamworkPM's url (including the resource you'd like the commits to post to, e.g. /task/) and you're ready to go.

When committing to Github you can reference a TeamworkPM task via it's ID with `#`.  E.g. "Fixes #123456" where #123456 is the TeamworkPM Task ID (or as the API calls it, the Teamwork Resource ID - https://yoursite.teamworkpm.net/tasks/123456).