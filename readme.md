# WPSF Framework
A Lightweight and easy-to-use WordPress Options Framework. It is a free framework for building theme options. Save your time!

## Screenshot
[![WPSF Framework Screenshot](http://wpsf.github.io/s3/theme-modern.jpg)](https://wpsf.github.io/s3/front-animation.gif)

## [Documentation](https://wpsf.gitbooks.io/docs/)
Read the documentation for details [documentation](https://wpsf.gitbooks.io/docs/)

## Note
The Framework still in development stage.

Documentation is still in-progress.

The Framework based on some [CodeStar Framework](https://github.com/Codestar/codestar-framework). The fields configs desgin also based on CodeStar Framework. 


## Installation
##### A) Usage as Theme
* Download zip file from github repository
* Extract download zip on `themename/wpsf-framework` folder under your theme directory
* Add framework include code on your theme `themename/functions.php` file

```php
require_once dirname( __FILE__ ) .'/wpsf-framework/wpsf-framework.php';
// -(or)-
require_once get_template_directory() .'/wpsf-framework/wpsf-framework.php';
```

* Yay! Right now you are ready to configure framework, metaboxes, taxonomies, wp customize, shortcoder
* Take a look for config files from `themename/wpsf-framework/config` folder
* Read for more from [documentation](https://wpsf.gitbooks.io/docs/)

##### B) Usage as Plugin
* Download zip file from github repository
* **Way1** Extract download zip on `wp-content/plugins/wpsf-framework` folder under your plugin directory
* **Way2** Upload zip file from `wordpess plugins panel -> add new -> upload plugin`
* Active WPSF Framework plugin from wordpress plugins panel
* Yay! Right now you are ready to configure framework, metaboxes, taxonomies, wp customize, shortcoder
* Take a look for config files from `wp-content/plugins/wpsf-framework/config` folder also you can manage config files from theme directory. see overriding files method.
* Read for more from [documentation](https://wpsf.gitbooks.io/docs/)


## Overriding Files
You can override an existing file without change `themename/wpsf-framework` folder. just create one `themename/wpsf-framework-override` folder on your theme directory. for eg:

```php
themename/wpsf-framework-override/config/framework.config.php
themename/wpsf-framework-override/functions/constants.php
themename/wpsf-framework-override/fields/text/text.php
```

## Features
- Options Framework
- Metabox Framework
- Taxonomy Framework
- WP Customize Framework
- Shortcode Generator
- Supports Child Themes
- Validate Fields
- Sanitize Fields
- Localization
- Fields Dependencies
- Supports Multilangual Fields
- Reset/Restore/Export/Import Options
- and so much more...

## Options Fields
- Text
- Textarea
- Checkbox
- Radio
- Select
- Number
- Icons
- Group
- Image
- Upload
- Gallery
- Sorter
- Wysiwyg
- Switcher
- Background
- Color Picker
- Multi Checkbox
- Checkbox Image Select
- Radio Image Select
- Typography
- Backup
- Heading
- Sub Heading
- Fieldset
- Notice
- and **extendable** fields

## License
WPSF Framework is **free** to use both personal and commercial. If you used commercial, **please credit**.
Read more about GNU [license.txt](http://www.gnu.org/licenses/gpl-3.0.txt)

## The Latest Updates
#### 0.5Beta
* First Version

See [changelog](CHANGELOG.md)

## Contributers
* [@chandrika1892](http://github.com/chandrika1892)


## CSS Libs / Framework Used
| Lib/Framework  | Repo Link |
| ------------- | ------------- |
| Animate CSS  | [daneden/animate.css](https://github.com/daneden/animate.css) |
| MagicInput  | [jaywcjlove/magic-input](https://github.com/jaywcjlove/magic-input)

## Javascript Libs / Framework Used
| Lib/Framework  | Repo Link |
| ------------- | ------------- |
| jQuery Actual  | [dreamerslab/jquery.actual](https://github.com/dreamerslab/jquery.actual) |
| Chosen Select  | [harvesthq/chosen](https://harvesthq.github.io/chosen/) |
| Select2  | [select2/select2](https://select2.org/) |
| Selectize  | [selectize/selectize](https://selectize.github.io/selectize.js/) |
| FlatPickr  | [flatpickr](https://flatpickr.js.org/) |
| inputToArray.js  | [varunsridharan/jquery-inputtoarray](https://github.com/varunsridharan/jquery-inputtoarray) |
| WP JS Hooks  | [carldanley/WP-JS-Hooks](https://github.com/carldanley/WP-JS-Hooks) |
| #### * Note : All Bootstrap Source Taken From V3.3.7  |  |
| JS Button  | [Bootstrap](https://getbootstrap.com) |
| ToolTip  | [Bootstrap](https://getbootstrap.com) |
| Popover  | [Bootstrap](https://getbootstrap.com) |
| Transition  | [Bootstrap](https://getbootstrap.com) |

#### Additional Selectize Plugins
| Plugin/Theme  | Issue Link | Source Code|
| ------------- | ------------- | ------------- |
| click_to_edit  | [Selectize Pull #946](https://github.com/selectize/selectize.js/pull/946) | [Source Code](https://github.com/krissalvador27/selectize.js/blob/5fe5862cb0d918c3f500c53c04f979e8d401a1db/src/plugins/click_to_edit/plugin.js) |
| condensed_dropdown  | [Selectize Pull #944](https://github.com/selectize/selectize.js/pull/944) | [Source Code](https://github.com/rantav/selectize.js/tree/0e45bf604acfd507d150561e39fe83b758cac24b/src/plugins/condensed_dropdown) |
| dark_theme  | [Selectize Pull #447](https://github.com/selectize/selectize.js/pull/447) | [Source Code](https://github.com/mistic100/selectize.js/tree/3bdf50a5e5850905aaf203eab679dc8fe9fae2d7/dist/css) |
| bootstrap4_theme | [Selectize Issue #905](https://github.com/selectize/selectize.js/issues/905) | [Source Code](https://github.com/papakay/selectize-bootstrap-4-style) |

#### 3rd Party Cloned Features
| 3rdParty | Link |
| -------- | ---- |
| TextLimiter | [wpmetabox/text-limiter](https://github.com/wpmetabox/text-limiter)
| Field Columns | [Meta Box Columns](https://metabox.io/plugins/meta-box-columns/)
