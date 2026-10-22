# CLAUDE.md

Guidance for Claude Code when working in this folder.

## What this is

The **public MamaBe registration form** for the MamaBe website. It is deployed to the **education server, which has no odb** — so this
folder must stay self-contained (no paths into `../odb`). It is **frontend only**: nothing is stored here. `api/submit.php` is a thin proxy
that forwards each registration to **odb's API**, where everything is validated again and saved.

```
Visitor ─▶ form.php ─▶ api/submit.php ──HTTPS (api_user mamabe_api, from api/.env)──▶ odb: /api/mamabeRegister.php
                                                                                          └▶ odb/mamabe/registration/api/register.php
          form.php ─▶ api/outlets.php ──(same credentials)──▶ odb: /api/mamabeOutlets.php ─▶ …/registration/api/outlets.php
```

Plain PHP 8 + vanilla JS, no build step, no framework. See `README.md` for files and deployment.

## The odb side (not in this folder)

- `C:\laragon\www\api\mamabeRegister.php` / `mamabeOutlets.php` — odb's plain-PHP API folder (prod: `https://octopusdb.info/api`). Two
  lines each: include `../odb/mamabe/registration/api/register.php` / `outlets.php`.
- `odb/mamabe/registration/` (a subfolder of the MamaBe module) — the odb module: `api/register.php` (auth against odb `api_user`: username + SHA-256 password, and the
  username must be in `MAMABE_API_USERS`; rate limit on the visitor IP this proxy forwards as `client_ip`; full validation; saves), the AI
  name-check worker, NAS copy, and the staff admin list. Its `README.md` has the details.

## Business rules (decided by the user — don't change without asking)

- **Open sign-up**, two ways (step 1 picks one): **Pink Book** or **Baby Health Book**. Both ask: how you know us (outlet / insurance /
  social media), mama name (as per IC), IC (MyKad or passport), phone, address, preferred language (1 English, 2 BM, 3 Chinese; Tamil (4) removed from the form by the user, Oct 2026), state (negeri) → preferred outlet to redeem
  in that state (list from odb via `api/outlets.php`; odb works the state out of each outlet's address, `MamabeRecords::stateOf`). Pink Book adds EDD + pink book photo; Baby Health Book adds baby name, baby DOB, baby IC (MyKid)
  + baby health book photo. All required. No baby gender (dropped by the user, Sept 2026; IC + address were dropped, then put back).
- odb matches the mama in `mamabe_mama` **by IC, else by mobile number** (dashes ignored, 01x and 601x treated alike): existing → the
  pregnancy/baby is linked to her and her details are **never overwritten** (only an empty IC / address / outlet is filled); new → a
  `mamabe_mama` row is created (`customer_id = 0`, `outlet_id` = her preferred outlet). **Never
  write to odb's `customer` table** (paid membership, registered at outlets). The pregnancy/baby goes into the mama's existing `mamabe_baby`
  row when one fits (`MamabeRecords::findBabyRow`): Baby Health Book → same MyKid, else her open pregnancy whose EDD is 4 weeks before…20
  weeks after the DOB, else — same DOB as another of her babies (other MyKid) → added as its **twin** (`twin_id`, like the
  MamaBe page's "add twin"), else a new row with EDD = DOB; Pink Book → same EDD, else an open pregnancy within ±30 days. Only empty fields are
  ever filled — nothing is overwritten. The preferred outlet becomes `mamabe_mama.outlet_id` —
  outlet staff only see mamas of their own outlets. Source is kept on `mamabe_submission` only.
- The visitor only ever sees **"Submitted successfully"** — never whether the mama already existed or what the AI decided.
- Language / source codes in `config.php` must match odb (`mamabe_mama.language`, `MamabeRecords::sources()`).
- Earlier designs (members-only check against `customer`, IC + name + phone verify step, the 3-step form with optional baby) were **dropped** by the user.

## Security

- `api/.env` holds the odb API password (`ODB_API_HOST`, `ODB_API_USERNAME`, `ODB_API_PASSWORD`). Never print or commit it. `api/.htaccess`
  blocks it from HTTP — keep that file, and check `…/api/.env` returns 403 after any deploy.
- **Never test against production.** `api/.env` points at `https://octopusdb.info/api`. For local tests create `api/.env.local` with
  `ODB_API_HOST=http://localhost/api` (it overrides `.env`), and delete it afterwards; never deploy it. When testing end-to-end locally,
  also stop odb's worker copying test photos to the real company NAS (`MAMABE_NAS_CONFIG` in `odb/mamabe/registration/common/config.php`), and
  delete test rows from `mamabe_mama` / `mamabe_baby` / `mamabe_submission` afterwards.

## Style

- `css/layout.css`, `page.css`, `alpro-*.css` are copies of odb's framework (`odb/common/css/`); `css/style.css` is this form's own layer
  (MamaBe teal, cards, numbered steps). Keep it phone-first — it is checked at 390px and 320px widths.
- The user writes in Malay/English mix; reply the same way.
