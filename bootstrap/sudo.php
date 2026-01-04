<?php
// bootstrap/sudo.php
declare(strict_types=1);

if (PHP_OS_FAMILY === 'Windows' || !function_exists('posix_geteuid')) {
    return '';
}

if (posix_geteuid() === 0) {
    return '';
}

$sudoBin = trim((string) shell_exec('command -v sudo 2>/dev/null'));
if ($sudoBin === '') {
    return '';
}

// Who am I?
$currentPw = posix_getpwuid(posix_geteuid());
$currentUser = $currentPw['name'] ?? 'unknown';

$apacheUser = getenv('APACHE_RUN_USER') ?: 'www-data';

// Only validate sudoers when running under Apache user (this is where failures matter)
if ($currentUser === $apacheUser) {

    // Target should be the repo owner user used for Git mutations.
    // For now (per your setup), this is debianuser.
    $targetUser = defined('GIT_OWNER') && GIT_OWNER !== ''
        ? GIT_OWNER
        : 'debianuser';

    if (empty($targetUser)) {
        trigger_error(
            '[CodeInSync][Git] GIT_OWNER is not defined. '
            . 'Git execution via sudo cannot be validated.',
            E_USER_WARNING
        );
    }

    $checkCmd = $sudoBin . ' -n -u ' . escapeshellarg($targetUser) . ' /usr/bin/git --version 2>&1';

    $out = [];
    $code = 0;
    @exec($checkCmd, $out, $code);

    if ($code !== 0) {
        trigger_error(
            '[CodeInSync][Git] Sudo is available but not configured for Git execution. '
            . 'Git mutations may fail under Apache/WSL. '
            . 'Fix: sudo editor /etc/sudoers.d/codeinsync-git and add: '
            . $apacheUser . ' ALL=(' . $targetUser . ') NOPASSWD: /usr/bin/git. '
            . 'See Architecture/Git/README.md.',
            E_USER_WARNING
        );
    }
}

return $sudoBin;