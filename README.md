# ttrss-plugin-gdorn_comics
Yet more ways of pulling comics, alt text and salient bits of blogs into Tiny Tiny RSS entries.  I got sick of trying to wrangle feedmod and feediron into doing what I wanted and then realized it's just easier to do it in code and skip the JSON config middleman entirely.

## Features

It's really just a quick and dirty content rewriting engine with code already written for about a dozen webcomics.  Examples:
* Joy of Tech
* Girls with Slingshots
* CTRL+ALT+DEL (Sillies)
* Three Panel Soul
* Two Lumps
* Timothy Winchester
* Awkward Zombie
* Scenes From A Multiverse
* XKCD
* Questionable Content
* Least I Could Do

You'll note several of these already have working RSS feeds with comics embedded.  I'm also working to standardize the display of alt/title tags (making the Android client easier to use) and adjust the layout of some that get squished or have excessive padding.

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
