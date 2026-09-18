# FB-005 — Drawing lists from other DCF files (Piping.dcf fails)

| Field | Value |
|---|---|
| **ID** | FB-005 |
| **Date** | 2026-09-17 (message); investigated **Fri 18 Sep 2026** |
| **Stage** | Post UAT |
| **From** | Jan (Upwork) |
| **Type** | Bug / multi-database support |
| **Status** | Root cause found — fix applied for project load on Piping.dcf |

## Client message

> Hi Mamun, yesterday i got a question regarding drawinglists of the other dcf files. All work except piping.dcf. Do you know what needs to be done to get this file read / imported?

Attached: `ProcessPower.dcf`, `Piping.dcf`, `Ortho.dcf`, `Iso.dcf` (H2h Zuivering Maasoever).

## Probe results (2026-09-18)

| File | Size | Validate | PnPDrawings | drawings() | loadProject |
|---|---:|---|---:|---:|---|
| ProcessPower.dcf | ~1.8 MB | OK | 1 | 1 | OK (P&ID-style; no EngineeringItems in this sample) |
| **Piping.dcf** | ~6.6 MB | OK | **28** | **28** | **FAIL** — `no such column: ei.ClassName` |
| Ortho.dcf | ~0.3 MB | OK | 11 | 11 | OK |
| Iso.dcf | ~0.2 MB | OK | 64 | 64 | OK |

## Root cause

`Piping.dcf` is the **Plant 3D piping (3D) database**, not the P&ID `ProcessPower` schema.

- It **does** contain `PnPDrawings` (28 rows); the drawing-list query itself works.
- It also contains `EngineeringItems`, but with **3D part columns** (`PartFamilyId`, `Spec`, `NominalDiameter`, …) and **no `ClassName`**.
- On upload / project open, `ErcDcf::loadProject()` calls `componentCount()`, which always filters `ei.ClassName …` → SQL error → import appears broken, so drawing list never reaches the UI.

Ortho/Iso/ProcessPower in this set either lack `EngineeringItems` or do not hit that path the same way, so they “work”.

## Fix

- Make `componentCount()` (and any load-time count) **schema-safe**: only use `ClassName` when the column exists.
- Drawing list for Piping.dcf should then open like the other files.
- P&ID equipment/valve/line lists remain tied to ProcessPower-style tables; Piping.dcf will not populate those (different product database).

## Sample files (local)

`C:\Users\Administrator\Downloads\{ProcessPower,Piping,Ortho,Iso}.dcf`  
Probe script: `scripts/probe_jan_dcfs.php`

## Acceptance

- [x] Upload `Piping.dcf` without error
- [x] Drawing list shows ~28 rows from Piping.dcf
- [x] Ortho / Iso / ProcessPower still load
- [ ] Reply to Jan explaining 3D vs P&ID DCF roles

## Fix notes (2026-09-18)

- `ErcDcf::componentCount()` and components query only filter `ClassName` when the column exists.
- Re-probe: Piping loadProject OK, drawings()=28.
