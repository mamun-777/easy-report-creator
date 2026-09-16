# EasyReportCreator — Weekly and daily schedule

| Field | Value |
|---|---|
| Start | **Wednesday 19 August 2026** |
| Finish (extended) | **Friday 18 September 2026** |
| Original V1 finish | Thursday 10 September 2026 (core lists + STRATO web app) |
| Deliverable budget | **100 h** core + **WP11–WP12 32 h** (trial / 1-year licence) = **132 h** |
| Upwork calendar | **23 working days × 8 h = 184 h** (was 17 × 8 = 136 h; Week 4 compressed) |
| Week shape | **5 working days per week**, Mon–Fri; weekends off |
| Daily pace | **8 h every working day** (4 h AM + 4 h PM on Upwork) |
| **Deployment** | **Web app on STRATO** — upload `ProcessPower.dcf` |
| **FB-003 (Sep 2026)** | **7-day trial** + **1-year licence** (PropertiesManager-aligned). **Desktop = wait** (deferred). |
| **Licence sprint** | **Tue 15 – Fri 18 Sep** (4 × 8 h) — complete trial + licensing setup |

Core V1 (lists, Excel, multi-project properties, header) landed by **Thu 10 / Fri 11 Sep**.  
**Trial/licence must be finished in four working days starting Tue 15 Sep** (handover **Fri 18 Sep**).

Upwork copy-paste lines: **`Upwork-Time-Log.md`** and **section 4** below (≤ 120 characters each).

---

## 1. Hour budget

| WP | Deliverable | Hours |
|---|---|---:|
| WP1 | Baseline, branding, light design tokens, repo layout | **5** |
| WP2 | Header from Project Details (standard + user-defined) | **12** |
| WP3 | Property / column selection from class catalogue | **20** |
| WP4 | Templates / company standards | **8** |
| WP5 | One-way English Excel (logo, title block, revision) | **10** |
| WP6 | Core list types on `MN-P-RHN-PID-0001` | **11** |
| WP7 | English light web UI (propertiesmanager.nl format) | **12** |
| WP8 | **STRATO hosted web app** (DCF upload) + easyreportcreator.com + HTTPS | **10** |
| WP9 | Short user guide | **3** |
| WP10 | Acceptance on sample + feedback (FB-001 / FB-002) | **7** |
| WP11 | **7-day trial + 1-year licence** (PropertiesManager-aligned) | **24** |
| WP12 | Pricing/site copy + licence UAT / handover | **8** |
| — | Coordination / check-ins | **2** |
| | **Total (extended)** | **132** |

---

## 2. Weekly view

| Week | Dates (working days) | Upwork h | Deliverable focus |
|---|---|---:|---|
| **1** | Wed 19 – Tue 25 Aug | **40** | Architecture, PHP site, Project Details header |
| **2** | Wed 26 Aug – Tue 1 Sep | **40** | Column picker, templates, Excel, first lists |
| **3** | Wed 2 – **Thu 10 Sep** | **56** | Lists, UI, STRATO deploy, UAT / first handover |
| **4** | **Fri 11 – Fri 18 Sep** | **48** | FB-003: trial + annual licence (4-day build Tue–Fri) |
| | | **184** | Extended finish **Fri 18 Sep** |

---

## 3. Daily view

### Week 1 — Wed 19 – Tue 25 Aug (40 h)

| Date | h | WP | Work |
|---|---:|---|---|
| **Wed 19 Aug** | 8 | WP1 | Kick-off. PHP+Python split, light theme, domain, STRATO. Architecture doc, schedule, repo layout. |
| **Thu 20 Aug** | 8 | WP1, WP8 | Shared light tokens. PHP config, header, footer. Home page + local preview script. |
| **Fri 21 Aug** | 8 | WP8 | Product, Pricing, Download, Contact, Terms. DNS checklist for easyreportcreator.com. |
| **Mon 24 Aug** | 8 | WP2 | Read PnPProject / Project Details + all custom categories (S88). Header catalogue API. |
| **Tue 25 Aug** | 8 | WP2 | Header field picker, logo upload, revision table in preview. Week 1 wrap-up. |

### Week 2 — Wed 26 Aug – Tue 1 Sep (40 h)

| Date | h | WP | Work |
|---|---:|---|---|
| **Wed 26 Aug** | 8 | WP3 | Class property catalogue from sample project. **Week 1 check-in.** |
| **Thu 27 Aug** | 8 | WP3 | Column picker UI per list/class; visible/hidden; English headers. |
| **Fri 28 Aug** | 8 | WP3, WP4 | Apply selection to queries. Template save/reload started. |
| **Mon 31 Aug** | 8 | WP4 | Company-standard templates: create, overwrite, load. Eight list defaults. |
| **Tue 1 Sep** | 8 | WP5 | Excel export: logo, title block, revision table, English issue layout. **Client locks web app + DCF upload on STRATO.** |

### Week 3 — Wed 2 – Thu 10 Sep (56 h)

| Date | h | WP | Work |
|---|---:|---|---|
| **Wed 2 Sep** | 8 | WP6 | Valve, equipment, line lists on sample project. **Week 2 check-in.** |
| **Thu 3 Sep** | 8 | WP6 | Control valve, instrument, drawing, line summary lists. |
| **Fri 4 Sep** | 8 | WP6, WP7 | Componentenlijst. Start light app UI (top nav, shared tokens). |
| **Mon 7 Sep** | 8 | WP7, WP8 | Finish light UI. Flow polish: upload → list → columns → preview → export. |
| **Tue 8 Sep** | 8 | WP8 | Deploy hosted web app on STRATO. DCF upload, session storage, HTTPS smoke-test. |
| **Wed 9 Sep** | 8 | WP8–WP10 | Deploy notes, user guide (upload workflow). Acceptance via web upload. |
| **Thu 10 Sep** | 8 | WP10 / FB-001 | UAT-2: multi-project Engineering Items properties (Jan feedback). |

### Week 4 — Fri 11 – Fri 18 Sep (48 h) · FB-003 licence / trial

| Date | h | WP | Work |
|---|---:|---|---|
| **Fri 11 Sep** | 8 | WP10 / FB-002 | Header from live Project Details. Confirm with Jan. Log FB-003 scope. |
| **Mon 14 Sep** | 8 | WP11 | Prep: licence model notes + company schema sketch (PropertiesManager-aligned). |
| **Tue 15 Sep** | 8 | WP11 | **Day 1/4:** Trial + licence data model; 7-day trial on register; UI days-remaining banner. |
| **Wed 16 Sep** | 8 | WP11 | **Day 2/4:** Enforce trial/licence on report API + UI; expired-trial screen. |
| **Thu 17 Sep** | 8 | WP11 | **Day 3/4:** 1-year licence activation (key/admin); account status; start Pricing copy. |
| **Fri 18 Sep** | 8 | WP11, WP12 | **Day 4/4:** Pricing/Download pages; full smoke-test; docs. **Handover lock.** |

**Sprint rule:** Trial + licensing system **complete by end of Fri 18 Sep** (four days from Tue 15).  
**Deferred (Jan):** desktop / no-upload installer — not in this sprint.

---

## 4. Upwork time log (8 h/day — 4 h AM + 4 h PM)

| Date | h | AM (4 h) | PM (4 h) |
|---|---:|---|---|
| Wed 19 Aug | 8 | Kick-off. Lock PHP+Python split, light theme, domain. Draft architecture doc. | Delivery schedule + repo layout (app/, samples/, reference/). Project structure doc. |
| Thu 20 Aug | 8 | Shared light design tokens. PHP config, header, footer includes. | Home page in PHP. Local preview script. Start Product page. |
| Fri 21 Aug | 8 | Product + Pricing pages. Stylesheet polish on public site. | Download, Contact, Terms pages. DNS checklist for easyreportcreator.com. |
| Mon 24 Aug | 8 | Read PnPProject + Project Details from sample DCF. Standard header fields. | Parse custom categories (S88 etc). Header catalogue API endpoints. |
| Tue 25 Aug | 8 | Header field picker UI. Wire fields into preview title block. | Logo upload + preview. Revision table editor. Week 1 wrap-up testing. |
| Wed 26 Aug | 8 | Class property discovery from sample project. Engineering Items tree. | Property list per class via API. Week 1 check-in with client. |
| Thu 27 Aug | 8 | Column picker UI: show/hide toggles. English column header labels. | Connect picker to preview grid. Persist column selection in app state. |
| Fri 28 Aug | 8 | Apply selected columns to query output. Hide fields from preview/export. | Template save: columns + sort order. Start template load/reload JSON flow. |
| Mon 31 Aug | 8 | Template create, overwrite, load. Defaults for eight list types. | Save header + revision rows in template. Valve list round-trip test. |
| Tue 1 Sep | 8 | Excel title block from selected Project Details. Logo in workbook. | Revision table in Excel. English issued layout. Client confirms STRATO web + upload. |
| Wed 2 Sep | 8 | Harden valve list on MN-P-RHN-PID-0001. Validate row counts vs sample. | Equipment + line lists hardened. Week 2 check-in with client. |
| Thu 3 Sep | 8 | Control valve + instrument lists on sample project. | Drawing list + line summary export tests on sample DCF. |
| Fri 4 Sep | 8 | Componentenlijst query + Excel export. Regression on all eight lists. | Light UI: shared tokens, Space Grotesk title bar, sidebar counts. |
| Mon 7 Sep | 8 | Matched public site tokens/type. Project card + list-count dashboard. | Flow polish: upload zone, steps, empty states, drag-drop → export. |
| Tue 8 Sep | 8 | Remote VPS: IIS site, PHP 8.3 FastCGI, 80 MB uploads, session cleanup. | Deployed to STRATO; smoke-test OK. DNS/HTTPS when A-record ready. |
| Wed 9 Sep | 8 | Deploy notes + short user guide (upload workflow). | Acceptance on MN-P-RHN-PID-0001 via web upload. Package for Jan feedback. |
| Thu 10 Sep | 8 | FB-001: live property catalogue from DCF (any project). | Verify on 2nd sample + handover / notes for Jan. |
| Fri 11 Sep | 8 | FB-002: header values from uploaded Project Details. | Deploy header fix; confirm with Jan; note FB-003 scope. |
| Mon 14 Sep | 8 | WP11 prep: trial + 1-year licence model (PropertiesManager). | Company licence schema sketch + gate design. |
| Tue 15 Sep | 8 | Day 1/4: licence DB fields; 7-day trial on register. | Trial banner + days-remaining in report UI. |
| Wed 16 Sep | 8 | Day 2/4: enforce expiry on report API (upload/lists/export). | Expired-trial screen + renew messaging. |
| Thu 17 Sep | 8 | Day 3/4: 1-year licence activation (key or admin grant). | Account status + Pricing page draft. |
| Fri 18 Sep | 8 | Day 4/4: Pricing/Download pages; end-to-end smoke-test. | Docs + deploy. **Handover lock.** Desktop deferred. |

---

## 5. Domain and STRATO (inside WP8)

**Deployment model:** single **web app on STRATO** — users upload `ProcessPower.dcf` in the browser (typical file size a few MB; sample ~3.3 MB). No desktop installer or Windows code signing while Jan waits on desktop (FB-003).

1. **DNS** — point easyreportcreator.com (and www) at the STRATO webspace.
2. **SSL** — enable HTTPS in the STRATO panel.
3. **PHP** — PHP 8.x, `DirectoryIndex index.php`; tune `upload_max_filesize` / `post_max_size` for `.dcf` uploads.
4. **Deploy** — public pages + report engine on STRATO (upload → analyse → preview → Excel export).
5. **Privacy** — session-based storage; auto-delete uploaded `.dcf` after use (document in user guide).
6. **Licence (WP11)** — 7-day trial then 1-year licence/use, aligned with PropertiesManager.

---

## 6. Daily rhythm

1. Log **8 h on Upwork** every working day (4 h AM + 4 h PM).
2. Prove engine changes on sample DCFs the same day.
3. Check-ins: end of **Wed 26 Aug**, **Wed 2 Sep**, **Thu 10 Sep** (core), **Fri 18 Sep** (licence/trial handover).

---

## 7. Slip rule

Licence/trial **must ship by Fri 18 Sep**. If blocked, same-day overtime only — do **not** slip into the week of 21 Sep unless Jan agrees. Do **not** start desktop work until Jan asks. Do not cut the 8 h working-day shape.
