# domix Extension for Joomla

![](https://img.shields.io/static/v1?label=Joomla&message=3.X&style=flat&logo=joomla&logoColor=orange&color=blue)
![](https://img.shields.io/github/release/z-index-net/joomla-plugin-system-domix.svg)
![](https://img.shields.io/github/downloads/z-index-net/joomla-plugin-system-domix/total.svg)
![](https://img.shields.io/badge/Maintained%3F-no-red.svg)
![](https://img.shields.io/github/license/z-index-net/joomla-plugin-system-domix.svg)


Debugging functions for Joomla developers as System Plugin.

Debug output is only visible for configured IPs (useful on live sites).

### Examples

- `domix($obj);` print
- `domixM($obj)` mail to configured mail
- `domixD($obj);` print `var_dump`
- `domixDB();` database debugging, output sql query for copy/paste and errors
- `domixCT();` function call trace

#### Note
Original developed by [mediahof](https://bitbucket.org/mediahof/joomla-plugin-system-domix).
