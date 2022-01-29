# Share on Twitter
Automatically share WordPress posts on [Twitter](https://twitter.com/). You choose which Post Types are shared. (Sharing can still be disabled on a per-post basis.)

This plugin shares a lot of code with [Share on Mastodon](https://github.com/janboddez/share-on-mastodon) and [Share on Pixelfed](https://github.com/janboddez/share-on-pixelfed) and functions in much the same way.

It does not, however, automatically register itself as a Twitter client when it is first set up: in order to Tweet from your WordPress instance, you will therefore have to [sign up as a developer](https://developer.twitter.com/en/portal/dashboard) first, and register a new app and an access token and secret with **read + write access**. This might require you temporarily enable at least OAuth 1.0a (under your app's Settings). Directly generating a bearer token from the app Settings page will likely not give you write access.

While this is tedious, it's also the only way. Twitter requires client API keys are kept private, which means they can't be shipped as part of an open-source application.

By default, Tweets contain only a title and permalink, but they can be fully customized using the various filter hooks. Automatic tagging, full-text status updates, or entire Tweet threads: everything's possible.

The plugin will _soon_ allow you to choose between Twitter v2 API (which anyone has access to right away) and the older but default v1.1 API (which now requires Elevated access). Note, however, that image posting won't work without Elevated access. (Luckily, all it takes is you submitting second application form, which is typically processed instantly.)
