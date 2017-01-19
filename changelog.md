# 1.8.0
* Replaces the `iconv` transliteration implementation with
  `transliterator_transliterate` from `intl`.

# 1.7.0
* 1.7.0 - add `Str::slugify`
* 1.7.1 - deprecated slugify, because it already existed under a different name
* 1.7.2 - fixes some documentation issues

# 1.6.0
* 1.6.0 - add `Str::truncate`

# 1.5.0
* 1.5.0 - add `Str::rolenize`
* 1.5.1 - remove `Str::rolenize` (it did not belong here, and was tagged by accident)
* 1.5.2 
  * Documentation: Made the code for Net::isLocalIpv4() a bit better legible
  * Fix: When systemizing a name, "soft hyphens" should be removed

# 1.4.0
* 1.4.0 - Adds better detection of links in plain text.
* 1.4.1 - Fixes some edge cases related to link detection
* 1.4.2 - Fixes even more edge cases related to link detection

# 1.3.0
* Add `Xml::format()`

# 1.2.0
* 1.2.0 - add `Net::isLocalIpv4()`
* 1.2.1 - fix encoding issue; assume UTF-8
* 1.2.2 - fixes link detection
* 1.2.3 - moves the implementation of createLinks() to a separate public method

# 1.1.0
* 1.1.0 - adds `Zicht\Util\Mutex` - a simple mutex implementation using `flock`

# 1.0.0
* 1.0.0 - first stable release
* 1.0.1 - add `Str::ascii()` and `Str::systemize()`
* 1.0.2 - add `Html::fromText()` and `Html::filter()`
* 1.0.3 - add `Zicht\Util\TreeTools`
* 1.0.4 - add `Zicht\Util\Url`
