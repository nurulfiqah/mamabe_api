# MamaBe public registration form — education-server package

The **frontend only**: the form mamas fill in on the MamaBe website. Nothing is stored on this server — `api/submit.php` forwards each
registration (fields + photos) to **odb's API**, which validates it again and saves it into odb (`mamabe_mama`, `mamabe_baby`,
`mamabe_submission`), then runs the AI name check and copies the photos to the NAS. Staff review everything in odb
(`odb/mamabe/registration/index.php`, or MamaBe → Admin Settings → Website Registrations).

```
Visitor ──▶ form.php ──▶ api/submit.php ──HTTPS (api_user: mamabe_api)──▶ odb: /api/mamabeRegister.php
            (this server)  (proxy, reads api/.env)                           └▶ odb/mamabe/registration/api/register.php
```

## Files
| File | Purpose |
|---|---|
| `form.php` + `form_upload.php` | The form: 1) register using **Pink Book** or **Baby Health Book** · 2) mama — how you know us, name (as per IC), IC, phone, address, language, state (negeri) → preferred outlet to redeem in that state · 3) Pink Book: EDD + pink book photo, or Baby Health Book: baby name, DOB, baby IC (MyKid) + baby health book photo. Only the chosen section is sent. `form_upload.php` is the photo box, included twice. |
| `api/submit.php` | Proxy. Cheap checks (honeypot, register type, the chosen book's photo type/size), then forwards everything to `ODB_API_HOST/mamabeRegister.php` with the api_user credentials and the visitor's IP (odb rate-limits on it). Shows odb's message to the visitor; hides credential/connection problems behind a generic "try again later". |
| `api/outlets.php` | Proxy for the outlet dropdown: asks `ODB_API_HOST/mamabeOutlets.php` (same credentials) for the active Alpro Pharmacy / Alpro Baby outlets, each with its state; the browser caches it 10 minutes. |
| `api/sources.php` | Proxy for the "How do you know us?" chips: asks `ODB_API_HOST/mamabeSources.php` (same credentials) for the admin-editable list (odb's `registration/settings.php` manages it - add/rename/reorder/hide, no code change needed here); the browser caches it 10 minutes. |
| `api/.env` | **Secrets** — `ODB_API_HOST`, `ODB_API_USERNAME`, `ODB_API_PASSWORD`. Never commit it. |
| `api/.env.local` | Optional, overrides `.env` (e.g. `ODB_API_HOST=http://localhost/api` to test against a local odb). Don't deploy it. |
| `api/.htaccess` | Blocks `.env`, `.env.local` and other dotfiles from HTTP. |
| `config.php` | Upload limit, API timeout, language list (codes must match odb's `mamabe_mama.language`), `.env` reader, `mamabe_odb_call()` (the POST to odb every proxy uses). "How you know us" is no longer listed here - it's fetched live from `api/sources.php`. |
| `css/` | `layout.css`, `page.css`, `alpro-core.css`, `alpro-components.css`, `alpro-utils.css` — copies of `odb/common/css/*` (odb's framework), plus `style.css` (this form's own layer). Re-copy if odb's framework CSS changes. |
| `images/mamabe-logo.png` | Logo. |

## Deploying
1. Copy this folder to the education server. **Include both `.htaccess` files** (root + `api/`; hidden files are easy to miss); do **not** copy `api/.env.local`. `CLAUDE.md` / `README.md` are blocked by the root `.htaccess` (or just leave them out).
2. Put the real `api/.env` in place. Check that `https://<site>/…/api/.env` returns **403** — if the server isn't Apache (or ignores
   `.htaccess`), block it in the server config instead, or the odb API password is public.
3. Server needs PHP 8 with **curl**, **gd** (for `getimagesize`) and **mbstring**; `upload_max_filesize` ≥ 6M, `post_max_size` ≥ 12M.
4. The server must reach `ODB_API_HOST` over HTTPS. odb's side needs `/api/mamabeRegister.php` + `/api/mamabeOutlets.php` deployed and
   the `mamabe_api` user in `api_user` (see `odb/mamabe/registration/README.md`).
5. Smoke test: the outlet dropdown fills; submit one Pink Book and one Baby Health Book registration → "Submitted successfully", and both
   appear in odb's MamaBe *Website Registrations* list.

## Notes
- **Rate limit** is enforced by odb on the visitor IP this proxy forwards (`REMOTE_ADDR`). If this server sits behind a reverse proxy /
  CDN, every visitor has the proxy's IP — change `client_ip` in `api/submit.php` to read the real client IP header your proxy sets.
- The visitor only ever sees *"Submitted successfully"* — never whether the mama was already registered or what the AI decided.
