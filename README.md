# ttrss-plugin-gdorn_comics
Yet more ways of pulling comics, alt text and salient bits of blogs into Tiny Tiny RSS entries.  I got sick of trying to wrangle feedmod and feediron into doing what I wanted and then realized it's just easier to do it in code and skip the JSON config middleman entirely.

## Installation

Like most plugins, it has to go in /plugins, and it has to have a specific dirname.  To get this:

```
cd /path/to/ttrss/
git clone https://github.com/georgedorn/ttrss-plugin-gdorn_comics plugins/gdorn_comics
```

## Contributing

It's fairly easy; copy an example similar to your needs, modify it, test it, submit a PR.  You can run the plugin either when an entry is rendered (i.e. as part of the request/response cycle) which is potentially very slow but great for debugging, or you can run it when the articles are initially downloaded (which runs it asynchronously if you've gone with the cron job route) which removes the speed concern but makes debugging a serious pain.

See init.php for how to switch modes.

PRs welcome.  I think this is far simpler than having hundreds of plugins, one per feed.  The file will get big, but whatever.
