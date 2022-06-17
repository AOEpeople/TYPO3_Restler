#!/bin/bash

chmod +x .Build/vendor/symplify/easy-coding-standard/vendor/squizlabs/php_codesniffer/bin/phpcs
.Build/vendor/symplify/easy-coding-standard/vendor/squizlabs/php_codesniffer/bin/phpcs --config-set installed_paths "$(pwd)/.Build/vendor/phpcompatibility/php-compatibility/PHPCompatibility"
.Build/vendor/symplify/easy-coding-standard/vendor/squizlabs/php_codesniffer/bin/phpcs --config-set ignore_warnings_on_exit 1 > /dev/null 2>&1
.Build/vendor/symplify/easy-coding-standard/vendor/squizlabs/php_codesniffer/bin/phpcs --config-set ignore_errors_on_exit 1 > /dev/null 2>&1
.Build/vendor/symplify/easy-coding-standard/vendor/squizlabs/php_codesniffer/bin/phpcs --config-set report_width 200 > /dev/null 2>&1
