# Manual QA checklist

These are test steps and expected outcomes, not completed manual-test results.
Record the WordPress version, theme, browser, viewport, date, and result when
running them. Use synthetic content in a disposable site.

| ID | Steps | Expected outcome | Result |
| --- | --- | --- | --- |
| M01 | Install the ZIP, activate the plugin, and insert `[reference_cards]` into a Shortcode block. | Published cards appear. No literal shortcode or PHP error appears. | Pending |
| M02 | Create three published posts, a newer draft, and a newer private post. Preview while signed out and as an administrator. | Both previews show published posts only. | Pending |
| M03 | Add a password to the newest published post. Preview again. | Protected content stays out of the cards. | Pending |
| M04 | Use `category="guides"` and then a missing slug. | The first selection shows guides. The second displays the empty-state message. | Pending |
| M05 | Use counts `1`, `0`, `-5`, `999`, and `abc`. | Counts obey 1, 1, 1, 12 maximum, and 3 respectively. | Pending |
| M06 | Mark an older post sticky. | Sticky status does not move it ahead of newer selected posts. | Pending |
| M07 | Place two shortcode blocks on the same page, with different categories. | Each block keeps its own selection. The surrounding page title/content stays intact. | Pending |
| M08 | Resize to 320, 375, 768, and 1280 CSS pixels. | Cards adapt to available width. No horizontal page overflow appears. | Pending |
| M09 | At 200% browser zoom, repeat the narrow-view test with a long article title. | Text stays readable. Title and excerpt wrap within the card. | Pending |
| M10 | Navigate with Tab, Shift+Tab, and Enter. | Each title link receives a visible focus indicator. Enter opens the associated post. | Pending |
| M11 | Inspect with a screen reader. | The section has a name. Link labels describe the posts. Dates and empty-state text remain understandable. | Pending |
| M12 | Use titles with `&`, quotation marks, accented Spanish text, and emoji. | Text renders safely with its intended characters. No active markup appears. | Pending |
| M13 | Open the browser network panel and reload. | One plugin stylesheet loads through WordPress. The plugin introduces no JavaScript or external API requests. | Pending |
| M14 | Deactivate the plugin, then reactivate and reload the shortcode page. | WordPress leaves the inactive shortcode unprocessed. Reactivation restores the cards on the next request. | Pending |

Automated tests verify database selection, bounds, escaping, WordPress lifecycle,
Loop context, and controlled error handling. They do not certify visual layout,
screen-reader behavior, compatibility with every theme, or production readiness.
