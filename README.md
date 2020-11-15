# Section Model Builder for Symphony CMS

An extension for [Symphony CMS][ext-Symphony CMS] that allows developers to build Classmapper Model files for sections from the command line.

-   [Installation](#installation)
-   [Requirements](#dependencies)
-   [Dependencies](#dependencies)
-   [Basic Usage](#basic-usage)
-   [Support](#support)
-   [Contributing](#contributing)
-   [License](#license)

## Installation

Clone the latest version to your `/extensions` folder and run composer to install required packaged with

### Manually (git + composer)
```bash
$ git clone https://github.com/pointybeard/symext-section-model-builder.git sectionmodelbuilder
$ composer update -vv --profile -d ./sectionmodelbuilder
```
After finishing the steps above, enable "Section Model Builder" though the administration interface or, if using [Orchestra][ext-Orchestra], with `bin/extension enable sectionmodelbuilder`.

### With Orchestra

1. Add the following extension defintion to your `.orchestra/build.json` file in the `"extensions"` block:

```json
{
    "name": "sectionmodelbuilder",
    "repository": {
        "url": "https://github.com/pointybeard/symext-section-model-builder.git"
    }
}
```

2. Run the following command to rebuild your Extensions

```bash
$ bin/orchestra build \
    --skip-import-sections \
    --database-skip-import-data \
    --database-skip-import-structure \
    --skip-create-author \
    --skip-skip-seeders \
    --skip-git-reset \
    --skip-composer \
    --skip-postbuild
```

# Requirements

- This extension works with PHP 7.4 or above.
- The [Console Extension for Symphony CMS][req-console] must also be installed.

# Dependencies

This extension depends on the following Composer libraries:

-   [PHP Helpers][dep-helpers]
-   [Symphony Section Class Mapper][dep-classmapper]
-   [Symphony CMS: Extended Base Class Library][dep-symphony-extended]
-   [Symphony CMS: Section Builder][dep-section-builder]

## Usage

This extension provides a new command called "create". Use it via the Symphony Console like so:

    symphony -t cba17882 sectionmodelbuilder create --help

For more information on installing and using commands with the [Console Extension for Symphony CMS][req-console], see <https://github.com/pointybeard/console/blob/master/README.md>

## Support

If you believe you have found a bug, please report it using the [GitHub issue tracker][ext-issues],
or better yet, fork the library and submit a pull request.

## Contributing

We encourage you to contribute to this project. Please check out the [Contributing to this project][doc-CONTRIBUTING] documentation for guidelines about how to get involved.

## Author
-   Alannah Kearney - http://github.com/pointybeard
-   See also the list of [contributors][ext-contributor] who participated in this project

## License
"Section Model Builder for Symphony CMS" is released under the MIT License. See [LICENCE][doc-LICENCE] for details.

[doc-CONTRIBUTING]: https://github.com/pointybeard/symext-section-model-builder/blob/master/CONTRIBUTING.md
[doc-LICENCE]: http://www.opensource.org/licenses/MIT
[req-console]: https://github.com/pointybeard/console
[dep-helpers]: https://github.com/pointybeard/helpers
[dep-classmapper]: https://github.com/pointybeard/symphony-classmapper
[dep-symphony-extended]: https://github.com/pointybeard/symphony-extended
[dep-section-builder]: https://github.com/pointybeard/symphony-section-builder
[ext-issues]: https://github.com/pointybeard/symext-section-model-builder/issues
[ext-Symphony CMS]: http://getsymphony.com
[ext-Orchestra]: https://github.com/pointybeard/orchestra
[ext-contributor]: https://github.com/pointybeard/symext-section-model-builder/contributors
[ext-docs]: https://github.com/pointybeard/symext-section-model-builder/blob/master/.docs/toc.md
