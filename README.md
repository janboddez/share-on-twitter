# Share on Twitter
Automatically share WordPress posts on [Twitter](https://twitter.com/). You choose which Post Types are shared. (Sharing can still be disabled on a per-post basis.)

This plugin shares a lot of code with [Share on Mastodon](https://github.com/janboddez/share-on-mastodon) and [Share on Pixelfed](https://github.com/janboddez/share-on-pixelfed) and functions in much the same way.

It does not, however, automatically register itself as a Twitter client when it is first set up: in order to Tweet from your WordPress instance, you will therefore have to [sign up as a developer](https://developer.twitter.com/en/portal/dashboard) first, and register a new app with read + write access. Only then will you be given a set of API secrets that allow WordPress to post on your behalf.

While this is tedious, it's also the only way. Twitter requires client API keys are kept private, which means they can't be shipped as part of an open-source application.

By default, Tweets contain only a title and permalink, but they can be fully customized using the various filter hooks. Automatic tagging, full-text status updates, or entire Tweet threads: everything's possible.
