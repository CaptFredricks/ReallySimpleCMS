# DOMtags Changelog

**Legend**
- N = new file
- D = deprecated file
- R = renamed file
- X = removed file
- M = minor change

**Versions**
- X.x.x (major release)
- x.X.x (feature update release)
- x.x.X (standard/minor release)
- x.x.x.X (bug fix/emergency patch release)

## Version 1.0.3 (2023-11-06)

**Bug fixes:**
- The label arg for the central `constructTag` isn't properly constructed into a wrapping tag

**Modified files:**
- class-dom-tag.php

## Version 1.0.2.1 (2023-09-30)

**Bug fixes:**
- The new `strong` tag can't be used by the `domTag` function

**Modified files:**
- dom-tags.php (M)

## Version 1.0.2 (2023-09-30)

- Added author data to core files
- Added support for the following tags and their parameters:
  - `b`, `strong`

**Bug fixes:**
- The return statement of the `DivTag::props` method ends with a comma instead of a semicolon

**Modified files:**
- dom-tags/class-div-tag.php (M)
- dom-tags/class-strong-tag.php (N)

## Version 1.0.1 (2023-09-21)

- Added support for the following tags and their parameters:
  - `abbr`, `h1-h6`, `section`
- Whitelisted the `pattern` and `required` parameters for the `input` tag
- Dom tags can now be created without directly invoking the individual classes
- New functions:
  - `dom-tags.php` (`domTag`)

**Modified files:**
- class-dom-tag.php (M)
- dom-tags.php
- dom-tags/class-abbr-tag.php (N)
- dom-tags/class-heading-tag.php (N)
- dom-tags/class-input-tag.php (M)
- dom-tags/class-section-tag.php (N)

## Version 1.0.0 (2023-02-15)

- Set up core files and initial tags
- The following tags and their parameters are supported:
  - `a`, `br`, `button`, `div`, `em`, `fieldset`, `form`, `hr`, `i`, `img`, `input`, `label`, `li`, `ol`, `option`, `p`, `select`, `span`, `textarea`, `ul`

**Modified files:**
- LICENSE.md (N)
- README.md (N)
- changelog.md (N)
- class-dom-tag.php (N)
- dom-tags.php (N)
- dom-tags/class-anchor-tag.php (N)
- dom-tags/class-button-tag.php (N)
- dom-tags/class-div-tag.php (N)
- dom-tags/class-em-tag.php (N)
- dom-tags/class-fieldset-tag.php (N)
- dom-tags/class-form-tag.php (N)
- dom-tags/class-image-tag.php (N)
- dom-tags/class-input-tag.php (N)
- dom-tags/class-label-tag.php (N)
- dom-tags/class-list-item-tag.php (N)
- dom-tags/class-list-tag.php (N)
- dom-tags/class-option-tag.php (N)
- dom-tags/class-paragraph-tag.php (N)
- dom-tags/class-select-tag.php (N)
- dom-tags/class-separator-tag.php (N)
- dom-tags/class-span-tag.php (N)
- dom-tags/class-textarea-tag.php (N)
- dom-tags/interface-dom-tag.php (N)