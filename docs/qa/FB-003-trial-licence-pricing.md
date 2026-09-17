# FB-003 — Pricing, 7-day trial, desktop wait

| Field | Value |
|---|---|
| **ID** | FB-003 |
| **Date** | 2026-09-13 (message); schedule from **Fri 11 Sep 2026** |
| **Stage** | Post UAT / scope extension |
| **From** | Jan (Upwork) |
| **Type** | Scope change / product direction |
| **Status** | Done for sprint — trial, keys, light admin, Moneybird buy links (Thu 17) |
| **Requirements** | Trial + annual licence (PropertiesManager-aligned). Desktop deferred. |

## Work notes

- 2026-09-16: `ErcLicence` trial + activate, API 402, licence UI. Deployed.
- 2026-09-16: Jan will create payment links; then sent NL 21% + non-NL 0% Moneybird URLs.
- 2026-09-17: Key registry (issued keys only), light-mode `/admin/`, buy page + purchase intents, Pricing €59 + links. `test_fb003_licence.php` PASSED.

## Payment links (Jan, 2026-09-16)

| Region | URL |
|---|---|
| Dutch 21% VAT | https://mnbrd.com/p/djAw5AJqaPV4 |
| Non-Dutch 0% VAT | https://mnbrd.com/p/6Y6pDxlL08AE |

Configured in `website/inc/config.php` → `ERC_LICENCE.payment_links`.

## Decisions (locked)

| Topic | Decision |
|---|---|
| Header (FB-002) | **Accepted** |
| Pricing | PropertiesManager **1 year** licence / use |
| Trial | **7-day trial** |
| Desktop | **Wait** — mention “will follow later” |
| Payment | Moneybird links (above); admin issues key after payment |

## Acceptance criteria

- [x] New company can start a **7-day trial** and use the report app
- [x] After 7 days without licence: clear block + renew/buy message
- [x] Active **1-year** licence unlocks full use (issued key only)
- [x] Pricing page describes annual licence + trial; buy via Moneybird
- [x] Desktop not advertised as available now (will follow later)
- [x] Light-mode licence admin to issue / revoke keys

## Schedule

**Tue 15 – Fri 18 Sep 2026.** Handover lock Fri 18. See `docs/Delivery-Schedule.md`.
