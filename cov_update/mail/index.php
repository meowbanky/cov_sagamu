<?php
// cov_update/mail/index.php — DISABLED 2026-08-16
//
// This was a one-off credential-blast script left over from the OOUTH
// deployment (it referenced oouth_coop.apk / mail.oouth.com and the legacy
// tblusers_online table). It mass-emailed members' PLAINTEXT passwords and had
// no callers. It is disabled as part of removing plaintext password handling.
//
// The original is preserved outside the web root if it is ever needed again.
http_response_code(410);
echo 'This endpoint has been retired.';
