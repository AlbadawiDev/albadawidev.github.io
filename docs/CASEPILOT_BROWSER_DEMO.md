# CasePilot browser demo

`casepilot-demo.html` is a standalone, Spanish-language preview of the CasePilot support workflow. It runs on GitHub Pages without installing Python. All sample people and incidents are fictional.

The complete application's source remains at [AlbadawiDev/casepilot](https://github.com/AlbadawiDev/casepilot). This browser preview does not run that application's Python backend, SQLite database, HTTP endpoints, sessions, authentication or server-side permissions. The page and its role selector label this boundary explicitly.

## Try the workflow

1. Open `casepilot-demo.html` and create a fictional request.
2. Open the request using its ticket button.
3. Progress it from open to in progress, then resolved. A resolved ticket can reopen. An open ticket cannot jump directly to resolved.
4. Switch the simulated role to inspect the assignment control. The agent can change status and priority; administrative assignment is available to the simulated administrator. This is an interface demonstration, not a security boundary.
5. Search by reference, title, requester or assignee; combine status and priority filters.
6. Export the filtered queue to CSV.
7. Select “Restablecer demo” and confirm to restore the nine sample tickets.

The deadline is set from priority when a ticket is created and remains unchanged when the priority changes, matching the original application's behavior.

## Local data and limits

The preview uses the isolated localStorage key `daniel.casepilot.browser-demo.v1`. Reloading keeps demo tickets on this browser and origin. Reset changes this key only; it does not clear unrelated storage.

If storage is blocked or a write fails, edits remain in memory and the page shows that they will not survive closing or reloading. Invalid stored data is replaced by the fictional sample queue with a visible notification. The preview supports up to 100 tickets and retains up to 250 local activity events.

There are no dependencies, external scripts, analytics, network API calls or credentials. User strings render as text nodes. CSV fields quote commas, double quotes and line breaks; potentially active spreadsheet values beginning with formula characters are prefixed with an apostrophe. This preview is not a shared help desk or a production service. Do not enter customer or personal data.

## Verification

Run with Node.js 24, using built-in modules:

```sh
node --test tests/casepilot-demo.test.cjs
```

Eight automated checks passed on Linux on 9 October 2026 (UTC):

- ticket creation, workflow transitions, persistence, reopening and isolated reset;
- simulated assignment restrictions without partial updates;
- combined filtering and matching export rows;
- CSV round-trip, multiline/quoted values and spreadsheet formula protection;
- literal user strings and text-node rendering checks;
- invalid saved data recovery and storage failure fallback;
- input validation and the bounded queue;
- validation of saved IDs, dates, enums and duplicate records.

These are separate checks for this static preview. The full CasePilot application's previously recorded 22 HTTP and 10 frontend tests remain separate. This document records automated checks; it does not claim a browser walkthrough or accessibility certification.

## Accessibility provisions

Ticket actions are native buttons. Forms have explicit labels and native validation. Dialogs use the native modal dialog element, accessible headings and close buttons. Closing a ticket returns focus to its button, or to search when filters remove it. Search result counts, saves and notices use live status regions. The ticket table scrolls inside its container on narrow screens.
