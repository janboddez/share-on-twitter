# Share on Twitter
Automatically share WordPress posts on [Twitter](https://twitter.com/). You choose which Post Types are shared. (Sharing can still be disabled on a per-post basis.)

This plugin shares a lot of code with [Share on Mastodon](https://github.com/janboddez/share-on-mastodon) and [Share on Pixelfed](https://github.com/janboddez/share-on-pixelfed) and functions in much the same way.

It does not, however, automatically register itself as a Twitter client when it is first set up: in order to Tweet from your WordPress instance, you will therefore have to [sign up as a developer](https://developer.twitter.com/en/portal/dashboard) first, and register a new app and an access token and secret with **read + write access**. This might require you temporarily enable at least OAuth 1.0a (under your app's Settings).

While this is tedious, it's also the only way. Twitter requires client API keys are kept private, which means they can't be shipped as part of an open-source application.

By default, Tweets contain only a title and permalink. And the Featured Image, if applicable. Tweets can, however, be fully customized using the various filter hooks. Automatic tagging, full-text status updates, or entire Tweet threads: everything's possible.

Twitter's v2 API (which anyone has access to right away) is now supported, too. Posting images, however, still uses the older v1.1 API (which now requires "Elevated access"). Luckily, Elevated access isn't all that hard to get. Other than that, both API versions should work just fine.
