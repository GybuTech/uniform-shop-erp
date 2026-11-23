# Offline-First Multi-Branch Sync: Design

**Status:** Phases 0–2 built and tested (tills, outbox, shop → HQ upload, HQ sync status). Sync stays
switched off until HQ goes live; see §10 for the go-live steps. Phases 3–5 are not built yet.

**Decisions taken (24 Nov 2026):** HQ on Railway · Main Shop's catalog is the master · receipt numbers
start with the branch code · returns only at the shop that issued the receipt · today: three shops,
one PC each, each with only its own data · **all selling happens on the shop PCs; HQ is view and
manage only** (built: in cloud mode the till, shifts, returns, voids, repayments and stock changes are
refused and hidden from the menu).
**Goal:** Every shop keeps selling with no internet. Whenever a shop is online, head office (HQ) sees
and manages every branch: sales, cash-ups, stock, prices, staff and transfers between shops.

---

## 1. The shape of the system

```
                         ┌──────────────────────────────┐
                         │   HQ CLOUD  (Railway, MySQL) │
                         │   - all branches' data       │
                         │   - master catalog & prices  │
                         │   - staff accounts, roles    │
                         │   - owner dashboard/reports  │
                         └──────────────▲───────────────┘
                     internet (when up) │  HTTPS, one API token per shop
            ┌───────────────────────────┼───────────────────────────┐
            │                           │                           │
   ┌────────┴────────┐        ┌─────────┴───────┐         ┌─────────┴───────┐
   │ MAIN SHOP server│        │ STALL 35 server │         │ BRANCH 3 server │
   │ PC + local MySQL│        │ PC + local MySQL│   ...   │ PC + local MySQL│
   └──▲──────────▲───┘        └──▲──────────────┘         └─────────────────┘
      │ shop LAN │               │
   Till 1     Till 2          Till 1      (other tills = a browser pointed at
   (browser)  (browser)       (browser)    the shop server over the shop's LAN)
```

- **One "shop server" per shop.** It's the PC that runs the app and the shop's own database (today's
  XAMPP setup). It needs no internet to work.
- **Extra tills in the same shop are just browsers** pointing at the shop server over the shop's local
  network (router or switch). They share that shop's stock instantly, with no syncing inside a shop.
  Each till has its own cash drawer and float (the "tills" feature is already built).
- **HQ cloud** is the same application running in cloud mode with a central database.
- A **sync service** on each shop server talks to HQ whenever the internet is up.

## 2. Who owns which data

Conflicts are avoided by giving every kind of record exactly one owner. Only the owner may change it;
everyone else receives copies.

| Data | Owner | Direction | Notes |
|---|---|---|---|
| Sales, sale lines, voids | Shop that sold | Shop → HQ | Append-only. A void is a new event, not a delete. |
| Shifts, cash-ups, Z-reports, cash collections | Shop | Shop → HQ | Per till. |
| Returns / refunds | Shop that processed it | Shop → HQ | May refer to a sale made at another shop (see §5). |
| Customer repayments | Shop that took the money | Shop → HQ | |
| Expenses | Shop (or HQ) | Up (or down) | Whoever recorded it owns it. |
| Stock movements at a shop | That shop | Shop → HQ | HQ derives each branch's stock level from these. |
| Categories, products, variants, **prices**, price tiers | HQ | HQ → shops | Shops stop editing the catalog once Phase 3 is live. |
| Staff accounts, roles, permissions | HQ | HQ → shops | Password changes are made at HQ. |
| Branches, tills, business settings | HQ | HQ → shops | |
| Customers (credit accounts) | Shared | Both ways | Matched by a global ID. A shop can add a customer offline. |
| Stock transfers between shops | Sending shop / HQ | Both ways | See §5. |
| Owner stock adjustments for a branch | HQ | HQ → that shop | Delivered as an instruction the shop applies to its own stock. |

**Stock rule:** a branch's stock changes only from its *own* movements: sales, returns, intake,
transfers in and out, and adjustments applied at that shop. Two places can never both edit the same
stock figure, so there's nothing to merge.

## 3. How a record travels

1. **Global IDs.** Every synced row gets a `uuid` in addition to its local numeric id. Numeric ids
   clash between shops (each shop has its own sale #1); UUIDs never do. Human-facing numbers get the
   branch code (e.g. `MS-RCP-…`, `S35-RCP-…`) so receipts are unique across the company.
2. **Outbox.** When a shop-owned record is created or changed, the shop writes it to a local
   `sync_outbox` table *in the same database transaction*. A sale can't exist without its
   outbox entry, so nothing is ever forgotten.
3. **Push** (every minute, via Windows Task Scheduler running `php artisan sync:run`): send unsent outbox
   entries in batches to `POST /api/sync/push`. HQ **upserts by uuid**, so a batch sent twice (e.g. the
   connection dropped before the reply came back) lands once. Entries are marked sent only after HQ
   confirms.
4. **Pull:** `GET /api/sync/pull?since=<cursor>` returns everything HQ-owned that changed since the
   shop's last pull (catalog, prices, users, settings), plus instructions addressed to that branch
   (incoming transfers, adjustments). The shop applies them and stores the new cursor.
5. **Offline:** the outbox simply grows. When the connection returns, it drains oldest-first.

**Security:** each shop server has its own API token tied to its branch. HQ rejects any record
claiming to belong to a different branch. All traffic is HTTPS.

## 4. What the owner sees at HQ

- Dashboard and reports across all branches or one branch, as the app does today in cloud mode.
- Per branch: **"Last synced 3 min ago"** and how many records are still waiting. A shop that's been
  offline for hours is visible at a glance.
- Live open tills, cash-ups and variances, stock per branch, low stock everywhere.
- Figures are only as fresh as each shop's last sync. That's an inherent property of working offline.

## 5. Cross-shop flows

- **Transfer Main Shop → STALL 35:** created at Main Shop (or at HQ on Main Shop's behalf). Main Shop's
  stock goes down at dispatch (a `transfer_out` movement) and syncs up. HQ forwards the incoming transfer
  to STALL 35 on its next pull. STALL 35 confirms receipt, which adds the stock (`transfer_in`) and syncs up.
  If STALL 35 is offline, the transfer waits at HQ until it's back.
- **Return at a different shop than the sale:** not allowed (decided). The system refuses it and says
  which shop issued the receipt.
- **Customer credit across shops:** the balance is computed from synced sales and repayments. While a
  shop is offline it only knows the payments it has seen, so a credit limit can be exceeded if the same
  customer buys at two offline shops. Acceptable for schools on account, but flagged at HQ.

## 6. Moving today's data in (the one-off migration)

Each shop currently has its **own separate database** with its own catalog ids and users.

1. **Pick one master catalog.** Match products across shops by SKU (SKUs are generated the same way,
   so most will match). Anything that doesn't match goes on a reconciliation list for a person to decide.
2. **Staff accounts:** merge into one list at HQ; same username = same person, after confirmation.
3. **Initial upload:** each shop sends its full history once, tagged with its branch. After that, only
   changes are sent.
4. Do this per shop, one at a time, on a quiet day, with a full database backup first.

## 7. Phases

| Phase | Delivers | Visible to staff? |
|---|---|---|
| 0 — Prep (mostly done) | Tills/registers, blind cash-up, bug fixes. Next: make the shop server reachable by the shop's other tills over the LAN, securely (debug off, firewall). | Tills |
| 1 — Foundations | UUIDs on synced tables, outbox, branch-coded document numbers, HQ sync API with per-shop tokens. | No |
| 2 — Shops → HQ | Sales, shifts, returns, repayments, expenses, stock movements upload. HQ dashboard per branch with sync status. **This is the "see all my shops" goal.** | Owner |
| 3 — HQ → Shops | One master catalog, prices, tiers, staff and settings pushed down. One-off catalog/user reconciliation. Shop-side catalog editing locked. | Yes |
| 4 — Cross-shop | Transfers routed through HQ, owner adjustments as instructions, shared customer credit. | Yes |
| 5 — Hardening | Sync health alerts, conflict/reconciliation report, automatic backups, data retention. | Owner |

Each phase is shippable on its own; Phase 2 already gives head office full visibility.

## 8. Risks and how they're handled

- **Shop server PC fails:** the whole shop stops (tills are browsers on it). Mitigation: nightly local
  backup plus the HQ copy; a spare PC can be restored from HQ data.
- **Wrong clock on a shop PC:** records carry both the shop's time and HQ's receive time; HQ flags drift.
- **Long outage:** no data loss (outbox), but HQ figures are stale and price changes arrive late.
- **Someone edits the catalog at a shop after Phase 3:** blocked in the UI; HQ is the single source.

## 9. Decisions needed before building

1. **Hosting:** keep Railway for HQ? It needs a paid plan with a managed MySQL database and backups.
2. **Master catalog:** which shop's catalog is the starting point (probably Main Shop)?
3. **Branch codes** for receipt numbers (e.g. MS, S35, KY).
4. **Returns of sales from another shop** (§5): allow with reconciliation at HQ, or same-shop only?
5. **Shop LAN:** does each multi-till shop have a router/switch connecting its PCs? Which PC is the shop
   server?
6. **Selling at HQ:** will anyone ever sell directly on the cloud version, or is HQ view/manage only?

Answered: 1 Railway · 2 Main Shop · 3 yes · 4 same shop only · 5 one PC per shop today ·
6 no selling at HQ (view/manage only).

## 10. Go-live checklist (when HQ goes online)

What is already built: every shop PC records each sale, shift, return, repayment, expense and stock
change in its outbox from now on, and uploads automatically once configured. The first upload also
sends all of the shop's earlier history.

1. **Deploy HQ on Railway** with `APP_STORE_MODE=cloud`, a MySQL database, `APP_DEBUG=false` and HTTPS.
2. **Load the master catalog** at HQ from Main Shop (spreadsheet export/import), and create the three
   branches with their codes (e.g. MS, S35, KY).
3. For each branch at HQ: **Settings → Branches → Generate Sync Key**. Copy the key shown (once).
4. On that shop's PC, add to `.env`:
   ```
   SYNC_HQ_URL=https://<your-hq-address>
   SYNC_TOKEN=<the key from step 3>
   ```
   then restart `RUN_POS.bat`. It starts a minimized **"POS Cloud Sync"** window that uploads every
   minute; keep it open.
5. On the shop PC, **Settings → Branches → Cloud Sync** shows the last sync time and anything waiting.
   **Sync Now** uploads immediately. At HQ, the dashboard shows "last synced" for every shop.
6. Back up each shop's database before its first sync.

Notes:
- Staff are matched by **username**. If two shops use the same username for different people, HQ keeps
  them apart by adding the branch code (e.g. `jane_s35`). These are records of who sold, not logins;
  activate them at HQ only if that person should log in there.
- Products are matched by **SKU**. An item HQ has never seen is created at HQ automatically (category
  "Synced From Shops" if the category is unknown), so no sale is ever dropped.
- The walk-in customer and any customer with the same phone at several shops become **one** customer
  at HQ.
- Records that arrive before what they depend on (e.g. a return before its sale) simply wait and go
  through on the next run.
