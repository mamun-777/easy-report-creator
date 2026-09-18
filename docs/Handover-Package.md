# EasyReportCreator — handover package for Jan (Wed 9 Sep 2026)

## Live URLs

| URL | Purpose |
|---|---|
| https://easyreportcreator.com/ | Product site |
| https://easyreportcreator.com/report/login.php | Report app — sign in |
| https://easyreportcreator.com/report/register.php | Create company account |

## What to try (acceptance)

1. Register a company account (or sign in if you already have one).  
2. Upload `ProcessPower.dcf` from project **MN-P-RHN-PID-0001**.  
3. Open each of the eight lists and confirm row counts (see table below).  
4. Optionally change columns / header / logo.  
5. Export Excel for at least Valve List and Component List.  
6. Log out — the uploaded file is cleared from the session.

## Expected counts (MN-P-RHN-PID-0001)

| List | Count key | Expected rows |
|---|---|---:|
| Drawing List | drawings | 30 |
| Equipment List | equipment | 80 |
| Valve List | hand_valves | 344 |
| Control Valve List | control_valves | 43 |
| Instrument List | instruments | 126 |
| Line List | pipe_lines | 611 |
| Line Summary | line_groups | 386 |
| Component List | components | 897 |

> Note (FB-001): Component count excludes Engineering Items that also exist as pipe lines / line groups (was 898 with class-name-only filter).

## Live acceptance (Wed 9 Sep 2026)

Automated web acceptance against https://easyreportcreator.com/report passed:

- Upload `MN-P-RHN-PID-0001` / `ProcessPower.dcf`
- All **8 lists** matched expected counts (table above)
- Valve List Excel export OK (~24 KB)
- Logout clears session project

Script: `scripts/acceptance_web_upload.py`

## Open QA / scope

| ID | Issue | Status |
|---|---|---|
| [FB-001](qa/FB-001-engineering-items-properties.md) | Multi-project Engineering Items properties | **Verify** — live 2026-09-10 |
| [FB-002](qa/FB-002-header-project-details.md) | Header from uploaded project | **Closed** — Jan confirmed |
| [FB-003](qa/FB-003-trial-licence-pricing.md) | 7-day trial + 1-year licence (PropertiesManager); desktop wait | **Done** — admin + Moneybird links |
| [FB-004](qa/FB-004-website-copy-dcf-strato.md) | Plant 3D not required (only .dcf); no STRATO on site; local later | **Done** |
| [FB-005](qa/FB-005-piping-dcf-drawing-lists.md) | Drawing lists from Ortho/Iso/ProcessPower OK; Piping.dcf import failed | **In progress** — schema-safe load fix |

Vitens / MN-P-RHN-PID-0001 remains the **regression** sample for list counts only.

## Docs in this repo

| File | Contents |
|---|---|
| `docs/User-Guide.md` | Short upload → list → export guide |
| `docs/STRATO-Deploy.md` | Server / IIS / PHP / privacy notes |
| `docs/Delivery-Schedule.md` | Calendar through **Fri 18 Sep** (licence/trial 4-day sprint) |

## Out of current scope (deferred)

- Excel import → write back to DCF  
- Dutch UI toggle  
- **Desktop / no-upload installer** (Jan: wait)  

## Feedback

Please send comments on Upwork (bugs, trial/licence wording, ICT questions). Licence/trial handover lock is scheduled for **Fri 18 Sep**.
