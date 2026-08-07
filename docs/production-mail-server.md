# Production Mail Server Readiness

Last checked: 2026-07-03

## Current Status

Application and VPS mail readiness are in place.

- Admin System includes Mail & SMTP Readiness checks.
- CLI diagnostics are available with `php artisan ops:mail-diagnostics`.
- Email Center can send test email through the configured Laravel mailer.
- Laravel production mailer uses the local SMTP relay on `carikerja.asia:25`.
- Postfix, Dovecot, OpenDKIM, nginx, and PHP-FPM are active on the VPS.
- Roundcube webmail is available at `https://carikerja.asia/webmail/`.
- Local delivery and Laravel mail delivery to `postmaster@carikerja.asia` have been verified.

Active mail ports on the VPS:

- `25/tcp` SMTP
- `465/tcp` SMTPS
- `587/tcp` submission
- `143/tcp` IMAP
- `993/tcp` IMAPS

Mailboxes created:

- `no-reply@carikerja.asia`
- `postmaster@carikerja.asia`

Mailbox passwords are stored only on the VPS in `/root/carikerja-mail-credentials.txt`; do not commit them to the repository.

## Remaining External DNS Tasks

The app diagnostics will continue to show warnings until the public DNS records are updated.

Add or update these records in Cloudflare/DNS:

```dns
mail.carikerja.asia.        A      103.178.153.81
carikerja.asia.             MX 10  mail.carikerja.asia.
carikerja.asia.             TXT    "v=spf1 mx ip4:103.178.153.81 -all"
_dmarc.carikerja.asia.      TXT    "v=DMARC1; p=quarantine; rua=mailto:postmaster@carikerja.asia; adkim=s; aspf=s"
default._domainkey.carikerja.asia. TXT "v=DKIM1; h=sha256; k=rsa; s=email; p=MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4IW31+GBC/lnohu2HcMAHysDGexcttxi0cdQHRJNKz3GkGNDLrueU7vgh9QxpWmqPawNzxNQn3JHQ56YC6WU+raD6POMhAf78l4r+j95mPW/lqn6CRFtds37rM6nxqlNXcTI2ZnKpNSBrT9yrzSlK8HtS89OZRzh8ip+23ta2IUJ+XqmVbQXDcP91TEBCoT+YKs5tnOp+FoLpHSQJ2GtjZWBxXRC6uSXHiuf/6Qk06FgBGi9I/QZLlAOfgAiDYFKmKnfMCLznPk7k9oaMs8RqL2sCnjPWPyDZi7pEkoPPUH2DDjIvt9HwHqLLlcNAczKWlhKKtwDOTMjaXHZ3JeArwIDAQAB"
```

If Cloudflare Email Routing must stay active during transition, use this SPF temporarily instead:

```dns
carikerja.asia.             TXT    "v=spf1 include:_spf.mx.cloudflare.net mx ip4:103.178.153.81 ~all"
```

The VPS provider should also set reverse DNS/PTR for `103.178.153.81` to `mail.carikerja.asia`.

## Recommended Certificate Follow-Up

The current mail services use the existing `carikerja.asia` Let's Encrypt certificate. After `mail.carikerja.asia` resolves to the VPS, issue/expand the certificate for `mail.carikerja.asia` and point Postfix/Dovecot to that certificate.

## Verification Commands

Run these after DNS propagation:

```bash
cd /var/www/carikerja.asia
php artisan optimize:clear
php artisan ops:mail-diagnostics
postqueue -p
systemctl status postfix dovecot opendkim --no-pager
```

Expected result after DNS is correct:

- Mail readiness is `ok`.
- MX points to `mail.carikerja.asia`.
- SPF authorizes `103.178.153.81`.
- DMARC exists.
- DKIM selector `default` exists.
- SMTP socket connects successfully.
