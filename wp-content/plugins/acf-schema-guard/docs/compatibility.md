# Compatibility

The v1 compatibility contract is:

| Component | Supported versions |
| --- | --- |
| WordPress | 6.4 and newer |
| PHP | 8.1, 8.2, and 8.3 |
| ACF Free | 6.2 and newer |
| ACF PRO | 6.2 and newer (optional) |

ACF Free is sufficient for the core workflow. ACF PRO is optional and only
affects which field types are available to a site.

Test releases on PHP 8.1, 8.2, and 8.3 before publishing. The plugin does not
modify ACF definitions, post meta, or content when ACF is unavailable.
