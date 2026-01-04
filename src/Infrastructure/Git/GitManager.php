<?php
declare(strict_types=1);

namespace CodeInSync\Infrastructure\Git;

use DOMDocument;
use CodeInSync\Infrastructure\Dom\DomHelpers;
use CodeInSync\Infrastructure\Runtime\ProcessRunner;

if (!class_exists(\CodeInSync\Infrastructure\Runtime\ProcessRunner::class)) {
    require APP_PATH . 'src/Infrastructure/Runtime/ProcessRunner.php';
    @class_alias(\CodeInSync\Infrastructure\Runtime\ProcessRunner::class, 'ProcessRunner');
}

if (!class_exists(DomHelpers::class)) {
    require APP_PATH . 'src/Infrastructure/Dom/DomHelpers.php';
    @class_alias(DomHelpers::class, 'DomHelpers');
}

/*
use function CodeInSync\Infrastructure\Dom\getElementsByClass;
if (!function_exists('CodeInSync\\Infrastructure\\Dom\\getElementsByClass')) {
    require_once APP_PATH . 'src/Infrastructure/Dom/DomHelpers.php';
}

use function {
    defined, dirname, exec, error_log,
    file_get_contents, file_put_contents,
    is_dir, is_file, is_resource,
    mkdir, parse_url, preg_match,
    rtrim, realpath, stripos, strtotime,
    trim, var_export, fwrite, feof, fgets, fclose,
    curl_init, curl_setopt, curl_exec, curl_close,
    json_decode, filemtime, ceil, date, is_array,
    stream_context_create, in_array
};*/

use Throwable;

use const DIRECTORY_SEPARATOR;
use const JSON_ERROR_NONE;
use const PHP_OS;
//use const STDIN;
//use const STDOUT;

/**
 * Git-related paths and constants.
 */

class GitPaths
{
    // Path to the Git executable (default)
    public const BIN = 'git';

    // Git internal folder and contents
    public const FOLDER = '.git';
    public const CONFIG = self::FOLDER . '/config';
    public const HEAD = self::FOLDER . '/HEAD';
    public const INDEX = self::FOLDER . '/index';
    public const OBJECTS = self::FOLDER . '/objects';
    public const REFS = self::FOLDER . '/refs';

    // Git project-level files
    public const IGNORE = '.gitignore';
    public const ATTRIBUTES = '.gitattributes';
    public const MODULES = '.gitmodules';

    // Example default config template (unchanged from your heredoc)
    public const DEFAULT_CONFIG = <<<END
[safe]
    directory = /mnt/c/www

[user]
    name = <Your Name>
    email = <your.email@example.com>

[core]
    editor = vim
    autocrlf = input

[alias]
    co = checkout
    br = branch
    ci = commit
    st = status

[color]
    ui = auto
END;

    /**
     * Optional helper: check if .git exists in given directory.
     */
    public static function exists(string $path = '.'): bool
    {
        return \is_dir($path . DIRECTORY_SEPARATOR . self::FOLDER);
    }
}

/**
 * Central Git manager: command handling, environment/paths,
 * remote SHA update, latest version lookup, etc.
 */
final class GitManager
{
    private string $appPath;
    private string $appRoot;
    private string $gitExec;
    /** @var array<string,mixed> */
    private array $globalErrors;

    /**
     * @param array<string,mixed> $globalErrors
     */
    public function __construct(string $appPath, string $appRoot, array &$globalErrors)
    {
        $this->appPath = \rtrim($appPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->appRoot = \rtrim($appRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->globalErrors = &$globalErrors;

        $this->gitExec = self::resolveGitExec();
        $this->ensureSshDirectory();
        $this->initGitVersionConstant();
    }

    /**
     * Convenience factory for your current globals.
     */
    public static function fromGlobals(): self
    {
        // These must exist in your bootstrap
        $appPath = \defined('APP_PATH') ? APP_PATH : \dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
        $appRoot = \defined('APP_ROOT') ? APP_ROOT : '';

        if (!isset($GLOBALS['errors']) || !\is_array($GLOBALS['errors'])) {
            $GLOBALS['errors'] = [];
        }

        return new self($appPath, $appRoot, $GLOBALS['errors']);
    }

    /**
     * Main entry: replaces handle_git_command(), but is also wrapped
     * by the global function for backwards-compatibility.
     */
    public function handleCommand(string $cmd): array
    {
        $output = [];
        $errors = [];

        if (!\preg_match('/^git\s+(.*)/i', $cmd, $match)) {
            return [
                'ok' => false,
                'api' => 'git',
                'command' => $cmd,
                'prompt' => '$',
                'result' => 'Invalid or missing git command',
                'error' => 'INVALID_GIT_COMMAND',
                'output' => [],
                'errors' => ['INVALID_GIT_COMMAND' => 'Invalid or missing git command'],
            ];
        }

        $gitCmd = \trim($match[1]);

        // Build base argv + prompt
        [$argv, $shellPrompt, $workTree] = $this->buildBaseCommand($gitCmd);

        // Repo not resolvable / not a git worktree
        if (empty($argv) || $workTree === '') {
            return [
                'ok' => false,
                'api' => 'git',
                'command' => $cmd,
                'prompt' => $shellPrompt ?: '$',
                'result' => 'Invalid repository path (missing .git or work-tree)',
                'error' => 'GIT_PATHS_INVALID',
                'output' => [],
                'errors' => ['GIT_PATHS_INVALID' => 'Missing .git directory or invalid work-tree'],
            ];
        }

        // Commit-like commands require identity
        if ($this->isCommitLikeCommand($gitCmd)) {
            $inj = $this->maybeInjectIdentityForCommit($argv, $shellPrompt, $workTree);

            if (empty($inj['ok'])) {
                $missing = $inj['missing'] ?? ['user.name', 'user.email'];

                return [
                    'ok' => false,
                    'api' => 'git',
                    'command' => $cmd,
                    'prompt' => $shellPrompt,
                    'result' => 'Git identity required: missing ' . \implode(', ', $missing),
                    'error' => 'GIT_IDENTITY_REQUIRED',
                    'missing' => $missing,
                    'output' => [],
                    'errors' => ['GIT_IDENTITY_REQUIRED' => 'Missing user.name and/or user.email'],
                ];
            }

            // ✅ IMPORTANT: apply injected argv/prompt
            if (!empty($inj['argv']) && \is_array($inj['argv'])) {
                $argv = $inj['argv'];
            }
            if (!empty($inj['prompt']) && \is_string($inj['prompt'])) {
                $shellPrompt = $inj['prompt'];
            }
        }

        // -- Special: help ----------------------------------------
        if (\preg_match('/^help\b/i', $gitCmd)) {
            $output[] = $this->helpText();

            // -- Special: update --------------------------------------
        } elseif (\preg_match('/^update\b/i', $gitCmd)) {
            $output[] = \function_exists('git_origin_sha_update')
                ? git_origin_sha_update()
                : 'git_origin_sha_update() not available.';

            // -- Special: push ----------------------------------------
        } elseif (\preg_match('/^push\b/i', $gitCmd)) {
            $pushResult = $this->handlePushCommand($gitCmd, $workTree); // <-- pass subcommand only
            $output = [...$output, ...($pushResult['output'] ?? [])];
            $errors = [...$errors, ...($pushResult['errors'] ?? [])];

            // -- Special: clone ---------------------------------------
        } elseif (\preg_match('/^clone\b/i', $gitCmd)) {
            // For now keep legacy clone handler signature
            $cloneResult = $this->handleCloneCommand($cmd, $workTree);
            $output = [...$output, ...($cloneResult['output'] ?? [])];
            $errors = [...$errors, ...($cloneResult['errors'] ?? [])];

            // -- Default git command ----------------------------------
        } else {
            $res = $this->runProcess($argv, $workTree);

            if (($res['exitCode'] ?? 0) !== 0 && ($res['stdout'] ?? '') === '') {
                if (!empty($res['stderr'])) {
                    $errors["GIT-$gitCmd"] = $res['stderr'];
                    \error_log((string) $res['stderr']);
                } else {
                    $errors["GIT-$gitCmd"] = 'Git command failed.';
                }
            } else {
                if (!empty($res['stdout'])) {
                    $output[] = $res['stdout'];
                }
                if (!empty($res['stderr'])) {
                    $output[] = 'stderr: ' . $res['stderr'];
                }
            }
        }

        // Build a printable result string
        $resultText = '';
        if (!empty($output)) {
            $resultText .= \implode("\n", \array_map('strval', $output));
        }
        if (!empty($errors)) {
            $errText = \implode("\n", \array_map('strval', \is_array($errors) ? $errors : [$errors]));
            $resultText .= ($resultText !== '' ? "\n\n" : '') . $errText;
        }

        return [
            'ok' => empty($errors),
            'api' => 'git',
            'command' => $cmd,
            'prompt' => $shellPrompt,
            'result' => $resultText === '' ? '' : $resultText,
            'output' => $output,
            'errors' => $errors,
        ];
    }

    /**
     * Read a git config key from local or global scope.
     *
     * @param 'local'|'global' $scope
     */
    private function gitConfigGet(string $key, string $workTree, string $scope = 'local'): string
    {
        $workTree = \rtrim($workTree, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        // Resolve gitDir from workTree rather than appPath/appRoot, so this works
        // even if caller passes a resolved worktree path.
        $gitDir = \realpath($workTree . GitPaths::FOLDER);
        if ($gitDir === false) {
            return '';
        }

        // Base argv (sudo policy + git + git-dir/work-tree)
        $baseArgv = [
            ...$this->computeSudoArgvForRepoOwner($workTree),
            $this->gitExec,
            '--git-dir=' . $gitDir,
            '--work-tree=' . $workTree,
        ];

        $argv = [...$baseArgv, 'config'];

        if ($scope === 'global') {
            $argv[] = '--global';
        } else {
            $argv[] = '--local';
        }

        $argv[] = '--get';
        $argv[] = $key;

        $res = $this->runProcess($argv, $workTree);

        // git config --get returns exit code 1 if missing; treat as empty
        $val = \trim((string) ($res['stdout'] ?? ''));

        return $val;
    }

    /**
     * Write a git config key to local or global scope.
     *
     * @param 'local'|'global' $scope
     */
    private function gitConfigSet(string $key, string $value, string $workTree, string $scope = 'local'): array
    {
        $scope = ($scope === 'global') ? 'global' : 'local';

        $workTree = \rtrim($workTree, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $gitDir = \realpath($workTree . GitPaths::FOLDER);

        if ($gitDir === false) {
            return [
                'exitCode' => 1,
                'stdout' => '',
                'stderr' => 'GIT_DIR_NOT_FOUND',
                'cmd' => '',
            ];
        }

        $argv = [
            ...$this->computeSudoArgvForRepoOwner($workTree),
            $this->gitExec,
            '--git-dir=' . $gitDir,
            '--work-tree=' . $workTree,
            'config',
            ($scope === 'global') ? '--global' : '--local',
            $key,
            $value,
        ];

        return $this->runProcess($argv, $workTree);
    }

    /**
     * Seed repo-local git identity from .env once, only if missing.
     *
     * - Checks git config --local user.name/user.email
     * - If either missing, tries .env (GITHUB.USERNAME / GITHUB.EMAIL)
     * - Writes only missing keys to --local
     * - Returns details for UI/logging
     */
    public function maybeSeedLocalIdentityFromEnv(): array
    {
        // Resolve paths
        $gitDir = \realpath($this->appPath . $this->appRoot . GitPaths::FOLDER);
        $workTreeReal = \realpath("{$this->appPath}{$this->appRoot}");

        if ($gitDir === false || $workTreeReal === false) {
            return ['ok' => false, 'error' => 'GIT_PATHS_INVALID'];
        }

        $workTree = \rtrim($workTreeReal, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        $localName = $this->gitConfigGet('user.name', $workTree, 'local');
        $localEmail = $this->gitConfigGet('user.email', $workTree, 'local');

        // Already configured -> do nothing
        if ($localName !== '' && $localEmail !== '') {
            return [
                'ok' => true,
                'seeded' => false,
                'reason' => 'already_configured',
                'name' => $localName,
                'email' => $localEmail,
            ];
        }

        // Env fallback (your env parser stores sections)
        $envName = \trim((string) ($_ENV['GITHUB']['USERNAME'] ?? ''));
        $envEmail = \trim((string) ($_ENV['GITHUB']['EMAIL'] ?? ''));

        $missing = [];
        if ($localName === '') {
            $missing[] = 'user.name';
        }
        if ($localEmail === '') {
            $missing[] = 'user.email';
        }

        // Can't seed if env missing required values
        if (($localName === '' && $envName === '') || ($localEmail === '' && $envEmail === '')) {
            return [
                'ok' => false,
                'error' => 'GIT_IDENTITY_REQUIRED',
                'seeded' => false,
                'missing' => $missing,
                'env_present' => [
                    'USERNAME' => $envName !== '',
                    'EMAIL' => $envEmail !== '',
                ],
            ];
        }

        $writes = [];

        if ($localName === '' && $envName !== '') {
            $r = $this->gitConfigSet('user.name', $envName, $workTree, 'local');
            if (($r['exitCode'] ?? 1) !== 0) {
                return [
                    'ok' => false,
                    'error' => 'GIT_CONFIG_SET_FAILED',
                    'key' => 'user.name',
                    'details' => $r,
                ];
            }
            $writes[] = 'user.name';
            $localName = $envName;
        }

        if ($localEmail === '' && $envEmail !== '') {
            $r = $this->gitConfigSet('user.email', $envEmail, $workTree, 'local');
            if (($r['exitCode'] ?? 1) !== 0) {
                return [
                    'ok' => false,
                    'error' => 'GIT_CONFIG_SET_FAILED',
                    'key' => 'user.email',
                    'details' => $r,
                ];
            }
            $writes[] = 'user.email';
            $localEmail = $envEmail;
        }

        return [
            'ok' => true,
            'seeded' => true,
            'written' => $writes,
            'name' => $localName,
            'email' => $localEmail,
        ];
    }



    /**
     * Decide if this command can create commits and therefore requires identity.
     */
    private function isCommitLikeCommand(string $gitCmd): bool
    {
        if (\preg_match('/^commit\b/i', $gitCmd))
            return true;
        if (\preg_match('/^(merge|cherry-pick)\b/i', $gitCmd))
            return true;
        if (\preg_match('/^rebase\b.*\s--continue\b/i', $gitCmd))
            return true;
        return false;
    }

    /**
     * Resolve identity using:
     *  1) git config --local
     *  2) git config --global
     *  3) $_ENV['GITHUB'] fallback
     *
     * Returns:
     *  ['name'=>string,'email'=>string,'source'=>string,'ok'=>bool,'missing'=>array]
     */
    private function resolveIdentity(string $workTree): array
    {
        $name = $this->gitConfigGet('user.name', $workTree, 'local');
        $email = $this->gitConfigGet('user.email', $workTree, 'local');

        $source = ($name !== '' || $email !== '') ? 'git-local' : 'none';

        if ($name === '' || $email === '') {
            $gName = $this->gitConfigGet('user.name', $workTree, 'global');
            $gEmail = $this->gitConfigGet('user.email', $workTree, 'global');

            // Fill only missing pieces
            if ($name === '' && $gName !== '') {
                $name = $gName;
            }
            if ($email === '' && $gEmail !== '') {
                $email = $gEmail;
            }

            if (($gName !== '' || $gEmail !== '') && $source === 'none') {
                $source = 'git-global';
            } elseif (($gName !== '' || $gEmail !== '') && ($name === $gName || $email === $gEmail)) {
                // optional: keep 'git-local' if local had something, but global filled remainder
                // if you'd rather signal mixed sources, you can use 'git-mixed'
                $source = ($source === 'git-local') ? 'git-local' : 'git-global';
            }
        }

        if ($name === '' || $email === '') {
            $envName = \trim((string) ($_ENV['GITHUB']['USERNAME'] ?? ''));
            $envEmail = \trim((string) ($_ENV['GITHUB']['EMAIL'] ?? ''));

            if ($name === '' && $envName !== '') {
                $name = $envName;
            }
            if ($email === '' && $envEmail !== '') {
                $email = $envEmail;
            }

            if ($envName !== '' || $envEmail !== '') {
                $source = 'env-github';
            }
        }

        $missing = [];
        if ($name === '') {
            $missing[] = 'name';
        }
        if ($email === '') {
            $missing[] = 'email';
        }

        return [
            'name' => $name,
            'email' => $email,
            'source' => $source,
            'ok' => empty($missing),
            'missing' => $missing,
        ];
    }

    /**
     * If identity is missing, returns ok=false with missing keys.
     * If identity exists from git config (local/global), returns argv unchanged.
     * If identity is from env fallback, injects per-command via: -c user.name=... -c user.email=...
     *
     * @param list<string> $argv
     */
    private function maybeInjectIdentityForCommit(array $argv, string $shellPrompt, string $workTree): array
    {
        $id = $this->resolveIdentity($workTree);

        if (empty($id['ok'])) {
            return [
                'ok' => false,
                'error' => 'GIT_IDENTITY_REQUIRED',
                'missing' => $id['missing'] ?? ['user.name', 'user.email'],
                'argv' => $argv,
                'prompt' => $shellPrompt,
                'source' => $id['source'] ?? 'unknown',
            ];
        }

        // If identity came from git-local or git-global, no injection needed.
        if (($id['source'] ?? '') !== 'env-github') {
            return [
                'ok' => true,
                'argv' => $argv,
                'prompt' => $shellPrompt,
                'source' => $id['source'] ?? 'unknown',
            ];
        }

        // Identity came from env fallback -> inject per command
        $name = (string) ($id['name'] ?? '');
        $email = (string) ($id['email'] ?? '');

        // Build injection tokens
        $injectTokens = [
            '-c',
            'user.name=' . $name,
            '-c',
            'user.email=' . $email,
        ];

        // Find where "git" lives inside argv (may be preceded by sudo tokens)
        $gitIdx = $this->findGitExecIndex($argv);

        // If we can't reliably locate git, fail safe (do not mutate)
        if ($gitIdx === -1) {
            return [
                'ok' => true,
                'argv' => $argv,
                'prompt' => $shellPrompt,
                'source' => $id['source'],
            ];
        }

        // Insert inject tokens right after the git executable
        \array_splice($argv, $gitIdx + 1, 0, $injectTokens);

        // Update prompt for UI (use argvToPrompt helper you already have)
        $shellPrompt = '$ ' . $this->argvToPrompt($argv);

        return [
            'ok' => true,
            'argv' => $argv,
            'prompt' => $shellPrompt,
            'source' => $id['source'],
        ];
    }

    /**
     * Locate the git executable inside argv.
     * Handles cases like: ["sudo","-u","bob","/usr/bin/git", ...] or ["git", ...]
     *
     * @param list<string> $argv
     */
    private function findGitExecIndex(array $argv): int
    {
        $gitExec = (string) $this->gitExec;
        $gitBase = \basename($gitExec);

        foreach ($argv as $i => $tok) {
            if (!\is_string($tok) || $tok === '') {
                continue;
            }

            // Exact match: "git" or "/usr/bin/git"
            if ($tok === $gitExec || $tok === $gitBase) {
                return (int) $i;
            }

            // Sometimes token might be a full path but gitExec is "git"
            if ($gitBase === 'git' && \basename($tok) === 'git') {
                return (int) $i;
            }
        }

        return -1;
    }

    /**
     * Runner-based HTTP GET (curl) that returns status + body.
     *
     * @return array{ok:bool,status:int,body:string,stderr:string,cmd:string}
     */
    private function httpGetViaCurl(string $url, array $headers = [], int $timeout = 15): array
    {
        // NOTE: requires curl binary available (usually: /usr/bin/curl)
        $argv = [
            'curl',
            '-sS',                 // silent but show errors
            '-L',                  // follow redirects
            '--connect-timeout',
            '10',
            '--max-time',
            (string) $timeout,
            '-o',
            '-',             // output body to stdout
            '-w',
            "\n__HTTP_STATUS__:%{http_code}\n", // append status marker
        ];

        foreach ($headers as $h) {
            $argv[] = '-H';
            $argv[] = $h;
        }

        $argv[] = $url;

        $res = ProcessRunner::run($argv, [
            'cwd' => \rtrim($this->appPath, DIRECTORY_SEPARATOR),
            'timeout' => $timeout + 5,
        ]);

        $stdout = (string) ($res['out'] ?? '');
        $stderr = (string) ($res['err'] ?? '');
        $cmd = (string) ($res['cmd'] ?? '');

        $status = 0;
        $body = $stdout;

        // Parse the marker appended by curl -w
        $marker = "\n__HTTP_STATUS__:";
        $pos = \strrpos($stdout, $marker);
        if ($pos !== false) {
            $body = \substr($stdout, 0, $pos);
            $tail = \substr($stdout, $pos + \strlen($marker));
            $tail = \trim($tail);
            $status = (int) $tail;
        }

        $ok = ($status >= 200 && $status < 300);

        return [
            'ok' => $ok,
            'status' => $status,
            'body' => \trim($body),
            'stderr' => $stderr,
            'cmd' => $cmd,
        ];
    }

    /**
     * Update cached LOCAL_SHA + REMOTE_SHA for main branch.
     *
     * - LOCAL_SHA: from "git rev-parse main" (runner-based)
     * - REMOTE_SHA: from GitHub API "refs/heads/main" (runner-based curl)
     *
     * Backwards-compat: returns LOCAL_SHA (string) or false on failure.
     *
     * @return string|bool
     */
    public function updateOriginSha()
    {
        // ----------------------------
        // 1) LOCAL SHA (runner-based git)
        // ----------------------------
        [$argv, $prompt, $workTree] = $this->buildBaseCommand('rev-parse main');

        if (empty($argv) || $workTree === '') {
            $this->globalErrors['GIT_UPDATE'] = "Invalid repository path (missing .git or work-tree)\n";
            return false;
        }

        $res = $this->runProcess($argv, $workTree);
        $localSha = \trim((string) ($res['stdout'] ?? ''));

        if ($localSha === '' || !\preg_match('/^[0-9a-f]{7,40}$/i', $localSha)) {
            $this->globalErrors['GIT_UPDATE'] = "Failed to resolve local main SHA.\n"
                . "cmd: " . ((string) ($res['cmd'] ?? '')) . "\n"
                . "stderr: " . ((string) ($res['stderr'] ?? '')) . "\n";
            return false;
        }

        // Store local SHA (new, correct)
        $_ENV['GITHUB']['LOCAL_SHA'] = $localSha;

        // Optional: keep your legacy "REMOTE_SHA means local" behavior if anything depends on it.
        // Comment this out once you're sure nothing relies on it:
        // $_ENV['GITHUB']['REMOTE_SHA'] = $localSha;

        // Reset/update notice text
        $this->globalErrors['GIT_UPDATE'] =
            "Local main branch is not up-to-date with origin/main\n";

        // ----------------------------
        // 2) REMOTE SHA (runner-based GitHub API via curl)
        // ----------------------------
        $remoteSha = '';
        $remoteStatus = 0;

        if (isset($_ENV['GITHUB']) && !empty($_ENV['GITHUB']['USERNAME'])) {
            $url = $this->buildLatestRemoteCommitUrl();

            if (\defined('APP_IS_ONLINE') && APP_IS_ONLINE && $url !== null) {
                $token = (string) (
                    $_ENV['GITHUB']['OAUTH_TOKEN']
                    ?? (defined('COMPOSER_AUTH') ? (COMPOSER_AUTH['token'] ?? '') : '')
                );

                $headers = [
                    'User-Agent: My-App',
                    'Accept: application/vnd.github+json',
                ];

                if ($token !== '') {
                    $headers[] = 'Authorization: token ' . $token;
                }

                $http = $this->httpGetViaCurl($url, $headers, 20);
                $remoteStatus = (int) ($http['status'] ?? 0);

                // Store status/debug for UI
                $_ENV['GITHUB']['REMOTE_STATUS'] = $remoteStatus;

                if ($remoteStatus === 401 || $remoteStatus === 403) {
                    $this->globalErrors['git-unauthorized'] =
                        "[git] You are not authorized. The token may have expired.\n";
                }

                if (!empty($http['ok']) && !empty($http['body'])) {
                    $decoded = \json_decode($http['body'], true);

                    if ($decoded === null && \json_last_error() !== JSON_ERROR_NONE) {
                        $this->globalErrors['GIT_UPDATE'] .= "Failed to decode json: " . \json_last_error_msg() . "\n";
                    } else {
                        // Expected payload: { "object": { "sha": "..." } }
                        $remoteSha = \trim((string) ($decoded['object']['sha'] ?? ''));
                    }
                } else {
                    // Request attempted but failed
                    $this->globalErrors['GIT_UPDATE'] .= "Failed to retrieve remote commit information.\n";
                    $this->globalErrors['GIT_UPDATE'] .= "status: {$remoteStatus}\n";
                    if (!empty($http['stderr'])) {
                        $this->globalErrors['GIT_UPDATE'] .= "stderr: {$http['stderr']}\n";
                    }
                }
            }
        }

        // If remote sha looks valid, store it (new, correct)
        if ($remoteSha !== '' && \preg_match('/^[0-9a-f]{7,40}$/i', $remoteSha)) {
            $_ENV['GITHUB']['REMOTE_SHA'] = $remoteSha;
        }

        // ----------------------------
        // 3) Compare + set a friendly flag/message
        // ----------------------------
        $hasRemote = isset($_ENV['GITHUB']['REMOTE_SHA']) && \is_string($_ENV['GITHUB']['REMOTE_SHA']) && $_ENV['GITHUB']['REMOTE_SHA'] !== '';
        $remoteShaFinal = $hasRemote ? (string) $_ENV['GITHUB']['REMOTE_SHA'] : '';

        // A clean boolean you can show in UI
        $_ENV['GITHUB']['IS_UP_TO_DATE'] = ($hasRemote && $remoteShaFinal === $localSha);

        // Improve your notice text (optional but helpful)
        if ($hasRemote) {
            if ($remoteShaFinal === $localSha) {
                $this->globalErrors['GIT_UPDATE'] = "Local main is up-to-date with origin/main\n";
            } else {
                $this->globalErrors['GIT_UPDATE'] =
                    "Local main is behind origin/main\n"
                    . "local:  {$localSha}\n"
                    . "remote: {$remoteShaFinal}\n";
            }
        } else {
            $this->globalErrors['GIT_UPDATE'] .= "Remote SHA unavailable (offline, unauthorized, or API failure).\n";
        }

        $_ENV['DEFAULT_UPDATE_NOTICE'] = false;

        // Backwards-compat return: LOCAL_SHA
        return $localSha;
    }


    /**
     * Mirror of your ".env same day -> maybe update" logic,
     * but as an explicit method rather than side-effect at include.
     */
    public function maybeRefreshRemoteShaIfEnvTouchedToday(): void
    {
        $file = "{$this->appPath}{$this->appRoot}.env";

        if (\is_file($file) && \date('Y-m-d', \filemtime($file)) === \date('Y-m-d')) {
            if (
                isset($_ENV['GITHUB']['REMOTE_SHA'])
                && $this->updateOriginSha() !== $_ENV['GITHUB']['REMOTE_SHA']
            ) {
                // no-op, but this is where you'd react to the change
            }
        }
    }

    /**
     * Refresh local cached git-scm.com HTML and parse the latest version.
     */
    public function refreshGitDownloadCacheAndVersion(): void
    {
        $path = app_base('var', null, 'abs');

        if (!\is_dir($path) && !\mkdir($path, 0755, true)) {
            $this->globalErrors['GIT_LATEST'] = "$path could not be created.";
            return;
        }

        $cacheFile = "{$path}git-scm.com.html";

        if (\is_file($cacheFile)) {
            // <= 5 days old? If older, refresh
            $nextCheck = \strtotime('+5 days', \filemtime($cacheFile));
            $daysDiff = \ceil(abs((\strtotime(\date('Y-m-d')) - \strtotime(\date('Y-m-d', $nextCheck))) / 86400));

            if ($daysDiff <= 0)
                $this->downloadGitScmHtml($cacheFile);
        } else
            $this->downloadGitScmHtml($cacheFile);


        $this->parseLatestVersionFromHtml($cacheFile);
    }

    // ------------------------------------------------------------------
    // Internals
    // ------------------------------------------------------------------

    private function buildBaseCommand(string $gitCmd): array
    {
        $gitDir = \realpath($this->appPath . $this->appRoot . GitPaths::FOLDER);
        $workTreeReal = \realpath("{$this->appPath}{$this->appRoot}");

        if ($gitDir === false || $workTreeReal === false) {
            // Keep return shape stable; handle upstream if you want.
            return [[], '$ (invalid repo)', ''];
        }

        $workTree = rtrim($workTreeReal, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        // --- compute sudo argv (NOT string prefix) ---
        $sudoArgv = $this->computeSudoArgvForRepoOwner($workTree);

        // --- tokenize git subcommand safely-ish (supports quotes) ---
        $gitArgs = $this->tokenizeCommand($gitCmd);

        // Build argv: [sudo..., git, --git-dir=..., --work-tree=..., ...gitArgs]
        $argv = [
            ...$sudoArgv,
            $this->gitExec,
            '--git-dir=' . $gitDir,
            '--work-tree=' . $workTree,
            ...$gitArgs,
        ];

        // Pretty prompt for UI/logs (shell-escaped for readability)
        $shellPrompt = '$ ' . $this->argvToPrompt($argv);

        return [$argv, $shellPrompt, $workTree];
    }

    /**
     * @return list<string>
     */
    private function computeSudoArgvForRepoOwner(string $workTree): array
    {
        $isWindows = \stripos(PHP_OS_FAMILY, 'Windows') === 0;
        $hasPosix = \function_exists('posix_geteuid') && \function_exists('posix_getpwuid');

        if ($isWindows || !$hasPosix) {
            return [];
        }

        $currentUid = \posix_geteuid();
        $currentPw = \posix_getpwuid($currentUid);
        $currentUser = $currentPw['name'] ?? null;

        $repoStat = @\stat($workTree);
        if ($repoStat === false) {
            return [];
        }

        $ownerPw = \posix_getpwuid($repoStat['uid']);
        $repoOwner = $ownerPw['name'] ?? null;

        if (
            !$repoOwner ||
            !$currentUser ||
            $currentUser === $repoOwner ||
            !\defined('APP_SUDO') ||
            \trim((string) APP_SUDO) === ''
        ) {
            return [];
        }

        // APP_SUDO may be "sudo" or "sudo -n" etc.
        $sudoBase = $this->tokenizeCommand((string) APP_SUDO);

        return [
            ...$sudoBase,
            '-u',
            $repoOwner,
        ];
    }

    /**
     * Tokenize a command string into argv, respecting simple single/double quotes.
     * @return list<string>
     */
    private function tokenizeCommand(string $cmd): array
    {
        $cmd = \trim($cmd);
        if ($cmd === '') {
            return [];
        }

        // Matches:
        // - "double quoted"
        // - 'single quoted'
        // - unquoted tokens
        \preg_match_all('/"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"|\'([^\']*)\'|(\\S+)/', $cmd, $m);

        $out = [];
        $count = \count($m[0]);
        for ($i = 0; $i < $count; $i++) {
            $tok = $m[1][$i] !== '' ? \stripcslashes($m[1][$i])
                : ($m[2][$i] !== '' ? $m[2][$i]
                    : $m[3][$i]);
            if ($tok !== '') {
                $out[] = $tok;
            }
        }

        return $out;
    }

    /**
     * Render argv as a shell-like string for display only.
     */
    private function argvToPrompt(array $argv): string
    {
        return \implode(' ', \array_map(static fn($s) => \escapeshellarg((string) $s), $argv));
    }

    private function helpText(): string
    {
        return <<<END
git reset filename   (unstage a specific file)

git branch
  -m   oldBranch newBranch   (Rename branch)
  -d   Safe delete
  -D   Force delete

git commit -am "Message"
git checkout -b newBranch
END;
    }

    /**
     * Build: https://<token>@github.com/<user>/<repo>.git
     * Returns null if env missing.
     */
    private function buildAuthedGithubPushUrl(): ?string
    {
        $token = (string) ($_ENV['GITHUB']['OAUTH_TOKEN'] ?? '');
        $user = (string) ($_ENV['GITHUB']['USERNAME'] ?? '');
        $repo = (string) ($_ENV['GITHUB']['REPOSITORY'] ?? '');

        if ($token === '' || $user === '' || $repo === '') {
            return null;
        }

        // URL-encode token for safety (in case it contains special chars)
        $tokenEnc = \rawurlencode($token);

        return "https://{$tokenEnc}@github.com/{$user}/{$repo}.git";
    }

    /**
     * Redact token from any output (stdout/stderr/prompt strings).
     */
    private function redactGithubToken(string $text): string
    {
        $token = (string) ($_ENV['GITHUB']['OAUTH_TOKEN'] ?? '');
        if ($token === '') {
            return $text;
        }

        // If we url-encoded it in the URL, redact both raw and encoded forms
        $encoded = \rawurlencode($token);

        $text = \str_replace($token, '[REDACTED]', $text);
        $text = \str_replace($encoded, '[REDACTED]', $text);

        return $text;
    }

    /**
     * Special handler: git push
     * - If user runs `git push` (or `git push origin`) with no explicit URL,
     *   auto-build: https://<token>@github.com/<user>/<repo>.git
     *
     * @return array{output:array<int,string>,errors:array<string,string>}
     */
    private function handlePushCommand(string $gitCmd, string $workTree): array
    {
        $output = [];
        $errors = [];

        // Tokenize the push subcommand (already without leading "git ")
        $args = $this->tokenizeCommand($gitCmd);

        // Expect: ["push", ...]
        if (empty($args) || \strtolower($args[0]) !== 'push') {
            $errors['GIT_PUSH'] = 'Invalid push command.';
            return compact('output', 'errors');
        }

        // Build base argv/prompt like normal:
        // baseArgv = [sudo..., gitExec, --git-dir=..., --work-tree=...]
        $gitDir = \realpath(\rtrim($workTree, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . GitPaths::FOLDER);
        if ($gitDir === false) {
            $errors['GIT_PUSH'] = 'GIT_DIR_NOT_FOUND';
            return compact('output', 'errors');
        }

        $baseArgv = [
            ...$this->computeSudoArgvForRepoOwner($workTree),
            $this->gitExec,
            '--git-dir=' . $gitDir,
            '--work-tree=' . \rtrim($workTree, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR,
        ];

        // Decide whether user already provided a URL (or something URL-like)
        // Examples that mean "already provided":
        //   git push https://...
        //   git push git@github.com:...
        //   git push http://...
        $hasExplicitUrl = false;
        foreach ($args as $tok) {
            if (!\is_string($tok))
                continue;
            if (\preg_match('#^(https?://|git@|ssh://)#i', $tok)) {
                $hasExplicitUrl = true;
                break;
            }
        }

        // If no explicit URL, inject URL as the "remote" argument
        // `git push <url> <refspec>` is valid.
        if (!$hasExplicitUrl) {
            $url = $this->buildAuthedGithubPushUrl();
            if ($url === null) {
                $errors['GIT_PUSH'] = 'Missing GITHUB.OAUTH_TOKEN and/or GITHUB.USERNAME/REPOSITORY in env.';
                return compact('output', 'errors');
            }

            // Where to insert URL?
            // args = ["push", ...]
            // If user said only "push": becomes ["push", "<url>"]
            // If user said "push origin": replace "origin" with url (so they can still add refs)
            if (isset($args[1]) && \is_string($args[1]) && $args[1] !== '' && $args[1][0] !== '-') {
                // Replace remote name with URL
                $args[1] = $url;
            } else {
                // Insert URL after "push"
                \array_splice($args, 1, 0, [$url]);
            }
        }

        // Final argv
        $argv = [...$baseArgv, ...$args];

        $res = $this->runProcess($argv, $workTree);

        // Redact token from anything that might be shown/logged
        $resStdout = (string) ($res['stdout'] ?? '');
        $resStderr = (string) ($res['stderr'] ?? '');
        $redactedStdout = $this->redactGithubToken($resStdout);
        $redactedStderr = $this->redactGithubToken($resStderr);

        if (($res['exitCode'] ?? 0) !== 0) {
            $errors['GIT_PUSH'] = $redactedStderr !== '' ? $redactedStderr : 'git push failed.';
            if ($redactedStdout !== '') {
                $output[] = $redactedStdout;
            }
            return compact('output', 'errors');
        }

        if ($redactedStdout !== '') {
            $output[] = $redactedStdout;
        }
        if ($redactedStderr !== '') {
            $output[] = 'stderr: ' . $redactedStderr;
        }

        return compact('output', 'errors');
    }


    /**
     * Handle the special "git clone ..." logic, including socket branch.
     *
     * @return array{output:array<int,string>,errors:array<string,string>}
     */
    private function handleCloneCommand(string $cmd, string $workTree): array
    {
        $output = [];
        $errors = [];

        if (
            \preg_match(
                '/^git\s+clone\s+(http(?:s)?:\/\/([^@\s]+)@github\.com\/[\w.-]+\/[\w.-]+\.git)(?:\s*([\w.-]+))?/i',
                $cmd,
                $githubRepo
            )
            || \preg_match(
                '/^git\s+clone\s+(http(?:s)?:\/\/github\.com\/[\w.-]+\/[\w.-]+\.git)(?:\s*([\w.-]+))?/i',
                $cmd,
                $githubRepo
            )
        ) {
            $parsedUrl = \parse_url($_ENV['GIT']['ORIGIN_URL']);
            $token = $_ENV['GITHUB']['OAUTH_TOKEN'] ?? '';

            $remoteUrl = "https://{$token}@{$parsedUrl['host']}{$parsedUrl['path']}.git";

            // Preserve your original logic: append remoteUrl to $cmd
            $fullCommand = "$cmd $remoteUrl";

            // No socket -> run via cis_run_process()
            if (
                !isset($GLOBALS['runtime']['socket'])
                || !\is_resource($GLOBALS['runtime']['socket'])
            ) {
                [$argv, $prompt, $wt] = $this->buildBaseCommand(\preg_replace('/^git\s+/i', '', $fullCommand));
                $res = $this->runProcess($argv, $wt);

                if (!empty($res['stdout'])) {
                    $output[] = $res['stdout'];
                }
                if (!empty($res['stderr'])) {
                    $errors['stderr'] = $res['stderr'];
                }
            } else {
                // socket handling unchanged
                $message = "cmd: $fullCommand\n";
                \fwrite($GLOBALS['runtime']['socket'], $message);
                $response = '';
                while (!\feof($GLOBALS['runtime']['socket'])) {
                    $response .= \fgets($GLOBALS['runtime']['socket'], 1024);
                }
                \fclose($GLOBALS['runtime']['socket']);
                $output[] = \trim($response);
            }
        }

        return compact('output', 'errors');
    }

    /**
     * Runs argv in a given cwd (workTree) via ProcessRunner.
     * Keeps old return shape: exitCode/stdout/stderr/cmd.
     *
     * @param list<string> $argv
     */
    private function runProcess(array $argv, string $workTree): array
    {
        $res = ProcessRunner::run($argv, [
            'cwd' => \rtrim($workTree, DIRECTORY_SEPARATOR),
            'timeout' => 120,
        ]);

        return [
            'exitCode' => (int) ($res['exit'] ?? 1),
            'stdout' => (string) ($res['out'] ?? ''),
            'stderr' => (string) ($res['err'] ?? ''),
            'cmd' => (string) ($res['cmd'] ?? ''),
        ];
    }

    private function ensureSshDirectory(): void
    {
        $base = \defined('APP_PATH') ? APP_PATH : \dirname(__DIR__) . DIRECTORY_SEPARATOR;
        $dirname = "$base.ssh";

        if (!\is_dir($dirname)) {
            if (!@\mkdir($dirname, 0755, true)) {
                $this->globalErrors['APP_BASE'][basename($dirname)]
                    = "$dirname could not be created.";
            }
        }
    }

    /**
     * Determine the git executable and define GIT_EXEC if needed.
     */
    public static function resolveGitExec(): string
    {
        if (\defined('GIT_EXEC')) {
            return GIT_EXEC;
        }

        $exec = \stripos(PHP_OS, 'WIN') === 0
            ? 'git.exe'
            : ($_ENV['GIT']['EXEC'] ?? '/usr/bin/git');

        \defined('GIT_EXEC') or define('GIT_EXEC', $exec);

        return $exec;
    }

    /**
     * Initialize GIT_VERSION constant if EXPR_VERSION is present.
     */
    private function initGitVersionConstant(): void
    {
        if (!isset($_ENV['GITHUB']['EXPR_VERSION'])) {
            return;
        }

        $expr = (string) $_ENV['GITHUB']['EXPR_VERSION'];

        // Run: git --version (no repo needed)
        $res = ProcessRunner::run([$this->gitExec, '--version'], [
            'cwd' => \rtrim($this->appPath, DIRECTORY_SEPARATOR),
            'timeout' => 15,
        ]);

        $gitVersion = \trim((string) ($res['out'] ?? ''));

        if ($gitVersion === '') {
            // Optional: log stderr for debugging
            // $this->globalErrors['GIT_VERSION'] = (string)($res['err'] ?? 'git --version failed');
            return;
        }

        if (\preg_match($expr, $gitVersion, $match)) {
            \defined('GIT_VERSION') or \define('GIT_VERSION', \rtrim((string) $match[1], '.'));
        }
    }

    /**
     * Build the GitHub API URL for the latest "main" ref,
     * based on client/domain/project/COMPOSER settings.
     */
    private function buildLatestRemoteCommitUrl(): ?string
    {
        $username = $_ENV['GITHUB']['USERNAME'];

        if (!empty($_GET['client']) || !empty($_GET['domain'])) {
            $repoName = $_GET['domain'] ?? $_ENV['DEFAULT_DOMAIN'];
            return "https://api.github.com/repos/{$username}/{$repoName}/git/refs/heads/main";
        }

        if (!empty($_GET['project'])) {
            $path = app_base('projects', null, 'rel') . $_GET['project'] . DIRECTORY_SEPARATOR;
            if (\is_dir("{$this->appPath}$path")) {
                $repoName = $_GET['project'];
                return "https://api.github.com/repos/{$username}/{$repoName}/git/refs/heads/main";
            }
        }

        if (isset($_ENV['COMPOSER']) && !empty($_ENV['COMPOSER'])) {
            $repoName = $_ENV['COMPOSER']['PACKAGE'];
            return "https://api.github.com/repos/{$username}/{$repoName}/git/refs/heads/main";
        }

        return null;
    }

    private function downloadGitScmHtml(string $cacheFile): void
    {
        $url = 'https://git-scm.com/downloads';
        $handle = \curl_init($url);
        \curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

        $html = \curl_exec($handle);
        if (!empty($html)) {
            if (\file_put_contents($cacheFile, $html) === false) {
                $this->globalErrors['GIT_LATEST'] = "$url returned empty.";
            }
        } else {
            $this->globalErrors['GIT_LATEST'] = "$url returned empty.";
        }

        \curl_close($handle);
    }

    private function parseLatestVersionFromHtml(string $cacheFile): void
    {
        libxml_use_internal_errors(true);

        $doc = new DOMDocument('1.0', 'utf-8');
        $html = @\file_get_contents($cacheFile);
        if ($html === false) {
            $this->globalErrors['GIT_LATEST'] = "Could not read $cacheFile";
            return;
        }

        $doc->loadHTML($html);
        $contentNode = $doc->getElementById("main");

        if (!$contentNode) {
            $this->globalErrors['GIT_LATEST'] = 'Could not find #main in git-scm HTML.';
            return;
        }

        $nodes = DomHelpers::getElementsByClass($contentNode, 'span', 'version');
        if (empty($nodes) || !isset($nodes[0])) {
            $this->globalErrors['GIT_LATEST'] = 'No .version span found.';
            return;
        }

        $nodeValue = $nodes[0]->nodeValue;
        $pattern = '/(\d+\.\d+\.\d+)/';

        if (\preg_match($pattern, $nodeValue, $matches)) {
            $version = $matches[1];
            \defined('GIT_LATEST') or define('GIT_LATEST', $version);
        } else {
            $this->globalErrors['GIT_LATEST'] = "$nodeValue did not match \$version";
        }
    }
}

// ------------------------------------------------------------------
// Backwards-compatible global functions
// ------------------------------------------------------------------

// Old API: handle_git_command(string $cmd): array
if (!function_exists('handle_git_command')) {
    function handle_git_command(string $cmd): array
    {
        $manager = GitManager::fromGlobals();
        return $manager->handleCommand($cmd);
    }
}

// Old API: git_origin_sha_update(): bool|string
if (!function_exists('git_origin_sha_update')) {
    /**
     * @return bool|string  LOCAL_SHA on success, false on failure
     */
    function git_origin_sha_update()
    {
        $manager = GitManager::fromGlobals();
        $sha = $manager->updateOriginSha();

        // Optional: legacy compat if some UI expects GITHUB.REMOTE_SHA always exists
        // (Do NOT overwrite a real remote sha)
        if ($sha !== false) {
            $_ENV['GITHUB']['LOCAL_SHA'] = (string) $sha;
            if (empty($_ENV['GITHUB']['REMOTE_SHA'])) {
                $_ENV['GITHUB']['REMOTE_SHA'] = (string) $sha; // fallback only
            }
        }

        return $sha;
    }
}