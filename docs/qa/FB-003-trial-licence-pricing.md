# FB-003 — Pricing, 7-day trial, desktop wait

| Field | Value |
|---|---|
| **ID** | FB-003 |
| **Date** | 2026-09-13 (message); schedule from **Fri 11 Sep 2026** |
| **Stage** | Post UAT / scope extension |
| **From** | Jan (Upwork) |
| **Type** | Scope change / product direction |
| **Status** | In progress — Days 1–3 done; Day 4 Fri 18 (smoke + handover) |
| **Requirements** | New: trial + annual licence (PropertiesManager-aligned). Desktop deferred. |

## Work notes

- 2026-09-16: Implemented `ErcLicence` (7-day trial + 1-year activate), API 402 gates, trial banner, `licence.php`, legacy fresh trial. `scripts/test_fb003_licence.php` PASSED. Deployed live.
- 2026-09-16 (Day 3 ahead): Account licence status page, licence chip in report UI, key normalize/Enter-to-activate, Pricing + Download copy for trial + 1-year licence.

## Client message

> Hi Mamun, thanks for the update. Header is working now. I will take a further look at the website and will let you know asap. Pricing will be the same as propertiesmanager for 1 year license/use. I want to wait with the desktop application. For propertiesmanager the trial version (7 days) has to be implemented. Regards, Jan

## Decisions (locked)

| Topic | Decision |
|---|---|
| Header (FB-002) | **Accepted** — working |
| Pricing | **Same model as PropertiesManager** — **1 year** licence / use |
| Trial | **7-day trial** (as on PropertiesManager) — **in scope** |
| Desktop / no-upload app | **Wait** — **out of current scope** (deferred) |
| Web app on STRATO | Continues as the product to licence |

## Interpretation

1. Original V1 “delivered hours only” packaging is superseded for go-to-market: EasyReportCreator will sell like PropertiesManager (annual licence + short trial).
2. Engineering work now includes a **licence / trial system** on the hosted web app (not a desktop installer).
3. Desktop remains a later option when Jan asks for it.

## Scope added (WP11+)

- 7-day trial per company/account (start, countdown, expiry messaging)
- Gate report features when trial expired / no active licence
- 1-year licence activation / status (key or admin grant — confirm with Jan)
- Public Pricing / Download copy aligned with PropertiesManager-style annual licence
- Docs + UAT for trial → paid path

## Out of this extension

- Desktop installer / code signing
- Payment gateway (unless Jan confirms in a follow-up — default: licence key / manual activation first)

## Acceptance criteria (draft)

- [x] New company can start a **7-day trial** and use the report app
- [x] After 7 days without licence: clear block + renew/buy message
- [x] Active **1-year** licence unlocks full use
- [x] Pricing page describes annual licence (PropertiesManager-aligned), not “100 h delivery only”
- [x] Desktop not advertised as available now

## Schedule

**Compressed:** complete trial + licensing in **four days** — **Tue 15, Wed 16, Thu 17, Fri 18 Sep 2026**.  
Handover lock: **Fri 18 Sep**. See `docs/Delivery-Schedule.md`.
