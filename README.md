RepoVid
=======

Wraps [Gource][] to produce videos of the history of multiple git repos, encoded with avconv.
If you can think of a better name than 'repovid', let me know.

Installation
------------
1. Install gource, avconv, and ideally an h.264 encoder library - I used libavcodec-extra-53 on ubuntu
2. Install Dependencies using [Composer][]
3. `cp config.json.dist config.json`
4. Edit the config to suit your setup

Running
-------
`php repovid.php v:g`

Cron
----
Gource requires a video window to run, so if you want to run this app cron cron you'll need to specify the display to use:
`DISPLAY=:0`
I'm sure it'd be possible to make it run in a virtual frame buffer, but I've not managed to get it to work yet.

[Gource]: http://code.google.com/p/gource/
[Composer]: http://getcomposer.org/download/