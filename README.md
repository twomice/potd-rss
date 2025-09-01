# potd-rss
## Purpose
Builds a single-element RSS feed for Wikipedia's [Picture of The Day](https://en.wikipedia.org/wiki/Wikipedia:Picture_of_the_day).

## License and copyright
potd-rss: Builds a single-element RSS feed for Wikipedia's Picture of The Day  
Copyright (C) 2025, Allen Shaw

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
    

## Background
This work is intended to provide an online source from which my family hompage
(currently running on protopage.com) can easily embed the Wikipedia Picture of
The Day ("POTD").

I thought of creating a low-content HTML page to embed on protopage, but using an
RSS feed gives me slightly better layout, so I'm going this way.


The resulting RSS feed will always have exactly one item, the most recent POTD.

## Be nice to Wikipedia
Wikipedia graciously allows automated access to its resources by scripts such as this one, under
its [Robot policy](https://wikitech.wikimedia.org/wiki/Robot_policy). This author aims to conform
this repo to that policy, and encourages you to do the same. (Note that because this script is not
making edits to Wikipedia content, the author doesn't believe it's covered by Wikipedia's similarly
named [Bot policy](https://en.wikipedia.org/wiki/Wikipedia:Bot_policy).

If you're aware of ways this repo fails to confirm with the Robot policy, please file an issue
on the repo (see below).

## Installation and usage
- Download this repo.
- Copy config.php.dist to config.php, and edit per comments in that file.
  - Ensure `$config['outputFile']` is the path to a file that's publicly available via http(s).
  - Ensure the Wikipedia page indicated by `$config['wikiPageTitle']` has the appropriate content
    for parseing by this repo (see below).
- Configure a cron job to run daily, calling `/path/to/php /path/to/potd-rss/potd-rss-builder.php`
- Point your RSS consumer (e.g. a Protopage widget) to the URL represented by `$config['outputFile']`.

## Output, debugging, and inspection
Since it's expected to run under cron, potd-rss-builder.php aims to print zero output. Instead,
upon each invocation, it creates the following files, which may be inspected for debugging
purposes.
- `current/log_purgeOutput.txt`: The output (typically in JSON format) of the cache-purge
  operation on the User Page indicated by `$config['wikiPageTitle']`. This file may contain
  an informative error message, if for some reason this cache-purge operation failed.
- `current/fetched_htmlFile.html`: The actual HTML content of the User Page indicated by
  `$config['wikiPageTitle']`. This file is then parsed in creating the RSS feed.
- `$config['outputFile']`: The actual RSS feed file, at the configured path.

## Wikipedia page content

The Wikipedia page indicated by `$config['wikiPageTitle']` is expected to contain specific
DOM elements. An example of the resulting page content is avaiable in this repo as example.html.
This can easily be achieved by using the following template as the content of a Wikipedia
User Page.

```
Page cache purged at: {{#time:Y-m-d}} {{CURRENTTIME}}

POTD URL: 
<span id="potdfeeder-url">https://en.wikipedia.org/wiki/Wikipedia:Picture_of_the_day/{{CURRENTMONTHNAME}}_{{CURRENTYEAR}}#{{CURRENTDAY}}
</span>

<div id="potdfeeder-potd">
{{POTD}}
</div>
<div id="potdfeeder-title">
{{POTD/{{#time:Y-m-d}}|title}}
</div>
<div id="potdfeeder-caption">
{{POTD/{{#time:Y-m-d}}|caption}}
</div>
```

FWIW, I did first think of simply accessing the current day's POTD page directly, and
retrieving the image, description, and page URL from there. But I found that the POTD
page has a highly variable DOM structure, making it hard to parse predictably. This
User Page approach helps to standardize that DOM structure, for easier parsing.

## Issues and support
Issues may be filed in [the repo's issue tracker](https://github.com/twomice/potd-rss/issues).

If you find a bug, you're essentially on your own, but who knows, you might get some
help by filing an issue.

If you find some way this repo is not conforming to Wikipedia usage policies, please do file an
issue, citing the exact policy by URL and the exact way in which this repo does not conform.