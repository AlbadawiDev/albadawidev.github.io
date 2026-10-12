# Reference Cards: WordPress customization and QA lab

A small, assisted personal portfolio demo by Daniel Albadawi. It contains original
demo code and synthetic data. It is not the First Cognition client plugin, a paid
client engagement, or evidence of employment.

## What it does

```text
[reference_cards]
[reference_cards count="3" category="guides"]
```

The shortcode renders article cards from published, unprotected WordPress posts.
Cards have a linked title, publication date, and a short plain-text excerpt. The
query accepts a category slug and a card count. It uses newest-first ordering,
excludes sticky-post promotion, and never fetches more than 12 posts. There is no
pagination total query, JavaScript, analytics, payment system, or external API.

| Input | Expected behavior |
| --- | --- |
| No attributes | Three latest published, unprotected posts |
| `count="1"` | One card |
| `count="0"` or a negative integer | One card |
| An integer above 12 | Twelve cards at most |
| Non-integer input | Default of three cards |
| `category="guides"` | Posts in the `guides` category, using the same limits |
| Missing category slug | Readable empty state |
| Nonempty category that sanitizes to an empty slug | Empty state, rather than a broad query |
| Controlled query exception | Generic unavailable state, without exception details |

The plugin passes post objects explicitly to WordPress template functions. It does
not call `the_post()` or replace `$wp_query`, so surrounding main and nested Loops
retain their context. Text, dates, and URLs use the appropriate WordPress escaping
functions. The frontend hook enqueues one small, namespaced stylesheet on every
frontend page. Loading it early also covers shortcodes rendered by themes or
widgets after the page head.

## Install the plugin

1. Build the ZIP:

   ```bash
   python3 wordpress-lab/scripts/package.py --output /tmp/reference-cards-1.0.0.zip
   ```

2. In a disposable WordPress site, open Plugins, Add New, Upload Plugin. Upload the
   ZIP and activate Reference Cards.
3. Create a few published posts and a `guides` category.
4. Add the shortcode to a Shortcode block. Preview the page on desktop and mobile.

The demo declares PHP 8.1+ and WordPress 6.6+. CI runs PHP 8.3, real MySQL 8.4, and
the actual current archive downloaded from `https://wordpress.org/latest.tar.gz`.
The integration report records the versions it genuinely executed, plus the
downloaded archive's SHA-256. A download filename or a search result is not a
compatibility-test result.

## Actual integration tests

The workflow is [wordpress-lab.yml](../.github/workflows/wordpress-lab.yml). It uses
a read-only GitHub token and an isolated MySQL service. It downloads official
WordPress into a temporary directory, installs it with `wp_install()`, activates
the actual plugin, then bootstraps a new PHP process for functional checks.
WordPress API functions and database queries are real. There are no WordPress
stubs, PHPUnit dependency, or production credentials.

The synthetic fixtures include 15 published guide posts, another category, a
draft, a private post, a password-protected post, a future scheduled post, and a
page. Checks cover:

- Plugin activation, `init` registration, shortcode output, and style enqueueing.
- Category filtering, public-post visibility, ordering, count bounds, and query
  settings that avoid pagination totals.
- Main-Loop and nested-Loop context, including a real `wp_reset_postdata()` after
  the nested Loop.
- HTML and URL escaping, empty titles, named links, and valid date attributes.
- Empty selections and a controlled exception injected through a real WordPress
  query hook. Fault injection does not replace the other database tests.

The workflow uploads these files as a run artifact:

```text
integration-results.json       Exact versions and every check result
integration.log                Test output
reference-cards-render.html    Actual WordPress output with bundled demo CSS
reference-cards-1.0.0.zip       Installable plugin
```

The HTML artifact is a static snapshot of an actual shortcode render. Its links
point to the disposable localhost fixture site. Browser layout and assistive
technology checks are separate manual checks, not claims made by the PHP tests.
Open the snapshot locally to inspect the layout.

## Run outside GitHub Actions

Use a fresh temporary WordPress directory and an empty MySQL database named
`wordpress_lab` or `wordpress_lab_<suffix>`. The installer refuses to overwrite
an existing WordPress configuration or install into an existing WordPress database.
The integration script also checks the database name and the lab site title before
creating or deleting synthetic fixtures.

```bash
export WP_ROOT=/tmp/reference-cards-wordpress/wordpress
export WP_LAB_ALLOW_INSTALL=1
export WP_LAB_DB_NAME=wordpress_lab
export WP_LAB_DB_USER=root
export WP_LAB_DB_PASSWORD=YOUR_DISPOSABLE_DATABASE_PASSWORD
export WP_LAB_DB_HOST=127.0.0.1:3306
export WP_LAB_ARTIFACTS=/tmp/reference-cards-artifacts

mkdir -p "$(dirname "$WP_ROOT")"
curl --fail --location https://wordpress.org/latest.tar.gz --output /tmp/wordpress-lab.tar.gz
tar -xzf /tmp/wordpress-lab.tar.gz -C "$(dirname "$WP_ROOT")"
cp -R wordpress-lab/reference-cards "$WP_ROOT/wp-content/plugins/reference-cards"
php wordpress-lab/tests/bootstrap-wordpress.php
php wordpress-lab/tests/integration.php
python3 wordpress-lab/scripts/package.py --output "$WP_LAB_ARTIFACTS/reference-cards-1.0.0.zip"
```

PHP needs the `mysqli` and `dom` extensions. The database must already exist.
Do not point these lab scripts at an existing site. The scripts deliberately write
synthetic content and remove WordPress's sample content in the isolated database.

## Evidence status

Use the completed workflow's report for pass counts and tested versions. This
README does not claim a test run that has not executed. The manual checklist is in
[QA-CHECKLIST.md](QA-CHECKLIST.md). GPL-2.0-or-later applies to the plugin code.

## WordPress references

- [Shortcode API](https://developer.wordpress.org/apis/shortcode/)
- [WP_Query](https://developer.wordpress.org/reference/classes/wp_query/)
- [Escaping output](https://developer.wordpress.org/apis/security/escaping/)
- [wp_enqueue_style](https://developer.wordpress.org/reference/functions/wp_enqueue_style/)
- [wp_install](https://developer.wordpress.org/reference/functions/wp_install/)
- [wp_reset_postdata](https://developer.wordpress.org/reference/functions/wp_reset_postdata/)
