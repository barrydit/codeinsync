<?php

namespace CodeInSync\Infrastructure\Git;

class GitPaths
{
    // Path to the Git executable (default)
    public const BIN = 'git';

    // Git internal folder and contents
    public const FOLDER    = '.git';
    public const CONFIG    = self::FOLDER . '/config';
    public const HEAD      = self::FOLDER . '/HEAD';
    public const INDEX     = self::FOLDER . '/index';
    public const OBJECTS   = self::FOLDER . '/objects';
    public const REFS      = self::FOLDER . '/refs';

    // Git project-level files
    public const IGNORE     = '.gitignore';
    public const ATTRIBUTES = '.gitattributes';
    public const MODULES    = '.gitmodules';

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
        return is_dir($path . DIRECTORY_SEPARATOR . self::FOLDER);
    }
}

/**
 * Central Git manager: command handling, environment/paths,
 * remote SHA update, latest version lookup, etc.
 */
class GitManager
{
    private string $appPath;
    private string $appRoot;
    private string $gitExec;
    /** @var array<string,mixed> */
    private array &$globalErrors;

    /**
     * @param array<string,mixed> $globalErrors
     */
    public function __construct(string $appPath, string $appRoot, array &$globalErrors)
    {
        $this->appPath      = rtrim($appPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->appRoot      = rtrim($appRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->globalErrors = &$globalErrors;

        $this->gitExec = $this->resolveGitExec();
        $this->ensureSshDirectory();
        $this->initGitVersionConstant();
    }

    /**
     * Convenience factory for your current globals.
     */
    public static function fromGlobals(): self
    {
        // These must exist in your bootstrap
        $appPath = defined('APP_PATH') ? APP_PATH : dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
        $appRoot = defined('APP_ROOT') ? APP_ROOT : '';

        if (!isset($GLOBALS['errors']) || !is_array($GLOBALS['errors'])) {
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

        if (!preg_match('/^git\s+(.*)/i', $cmd, $match)) {
            return [
                'status'  => 'error',
                'message' => 'Invalid or missing git command',
            ];
        }

        $gitCmd = trim($match[1]);

        // Build base command and prompt
        [$fullCommand, $shellPrompt, $workTree] = $this->buildBaseCommand($gitCmd);

        // -- Special: help ----------------------------------------
        if (preg_match('/^help\b/i', $gitCmd)) {
            $output[] = $this->helpText();
        }

        // -- Special: update --------------------------------------
        elseif (preg_match('/^update\b/i', $gitCmd)) {
            $output[] = function_exists('git_origin_sha_update')
                ? git_origin_sha_update()    // will hit our wrapper, see below
                : 'git_origin_sha_update() not available.';
        }

        // -- Special: clone ---------------------------------------
        elseif (preg_match('/^clone\b/i', $gitCmd)) {
            $cloneResult = $this->handleCloneCommand($cmd, $workTree);
            $output = array_merge($output, $cloneResult['output']);
            $errors = array_merge($errors, $cloneResult['errors']);
        }

        // -- Default git command ----------------------------------
        else {
            $res = $this->runProcess($fullCommand, $workTree);

            if (($res['exitCode'] ?? 0) !== 0 && ($res['stdout'] ?? '') === '') {
                if (!empty($res['stderr'])) {
                    $errors["GIT-$cmd"] = $res['stderr'];
                    error_log($res['stderr']);
                }
            } else {
                if (!empty($res['stdout'])) {
                    $output[] = $res['stdout'];
                }
                if (!empty($res['stderr'])) {
                    $output[] = 'stderr: ' . $res['stderr'];
                }
            }

            // Debug: include required files info (unchanged)
            $output[] = var_export(get_required_files(), true);
        }

        return [
            'status'  => empty($errors) ? 'success' : 'error',
            'command' => $cmd,
            'prompt'  => $shellPrompt,
            'output'  => $output,
            'errors'  => $errors,
        ];
    }

    /**
     * Port of git_origin_sha_update(), returning the chosen SHA.
     *
     * @return string|bool
     */
    public function updateOriginSha()
    {
        $latestLocalCommitSha = exec(
            $this->gitExec
            . ' --git-dir="' . $this->appPath . $this->appRoot . '.git"'
            . ' --work-tree="' . $this->appPath . $this->appRoot . '" rev-parse main'
        );

        $this->globalErrors['GIT_UPDATE'] =
            "Local main branch is not up-to-date with origin/main\n";

        if (isset($_ENV['GITHUB']) && !empty($_ENV['GITHUB']['USERNAME'])) {
            $latestRemoteCommitUrl = $this->buildLatestRemoteCommitUrl();

            if (isset($_ENV['GITHUB']['OAUTH_TOKEN']) || defined('COMPOSER_AUTH')) {
                $context = stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'header' => 'Authorization: token '
                            . ($_ENV['GITHUB']['OAUTH_TOKEN'] ?? COMPOSER_AUTH['token'])
                            . "\r\nUser-Agent: My-App\r\n",
                    ],
                ]);
            } else {
                $context = null;
            }

            if (
                defined('APP_IS_ONLINE') && APP_IS_ONLINE
                && $latestRemoteCommitUrl !== null
                && !check_http_status($latestRemoteCommitUrl, 404)
                && !check_http_status($latestRemoteCommitUrl, 401)
            ) {
                if ($context === false) {
                    error_log("Failed to create stream context.");
                    var_dump(error_get_last());
                } else {
                    $response = file_get_contents($latestRemoteCommitUrl, false, $context) ?? '{}';
                    if ($response === false) {
                        error_log("Failed to get contents from url.");
                        var_dump(error_get_last());
                    } else {
                        $decodedResult = json_decode($response, true);
                        if ($decodedResult === null && json_last_error() !== JSON_ERROR_NONE) {
                            error_log("Failed to decode json.");
                            var_dump(json_last_error_msg());
                        }
                    }
                }
            }
        }

        if (isset($http_response_header) && strpos($http_response_header[0], '401') !== false) {
            $this->globalErrors['git-unauthorized'] =
                "[git] You are not authorized. The token may have expired.\n";
        }

        // Preserve your original logic
        if (isset($response) && $response !== null) {
            $this->globalErrors['GIT_UPDATE'] .= "Failed to retrieve commit information.\n";
            $data = json_decode($response, true);
        } else {
            $data = null;
        }

        if ($data && isset($data['object']['sha'])) {
            $latestRemoteCommitSha = $data['object']['sha'];

            // Your old compare logic was commented out; leaving it as-is.
            // $_ENV['GITHUB']['REMOTE_SHA'] = $latestRemoteCommitSha;
        } else {
            $this->globalErrors['GIT_UPDATE'] .= "Failed to retrieve commit information.\n";
        }

        $_ENV['DEFAULT_UPDATE_NOTICE'] = false;

        return $_ENV['GITHUB']['REMOTE_SHA'] = $latestLocalCommitSha;
    }

    /**
     * Mirror of your ".env same day -> maybe update" logic,
     * but as an explicit method rather than side-effect at include.
     */
    public function maybeRefreshRemoteShaIfEnvTouchedToday(): void
    {
        $file = $this->appPath . $this->appRoot . '.env';

        if (is_file($file) && date('Y-m-d', filemtime($file)) === date('Y-m-d')) {
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

        if (!is_dir($path) && !mkdir($path, 0755, true)) {
            $this->globalErrors['GIT_LATEST'] = "$path could not be created.";
            return;
        }

        $cacheFile = $path . 'git-scm.com.html';

        if (is_file($cacheFile)) {
            // <= 5 days old? If older, refresh
            $nextCheck = strtotime('+5 days', filemtime($cacheFile));
            $daysDiff  = ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', $nextCheck))) / 86400));

            if ($daysDiff <= 0) {
                $this->downloadGitScmHtml($cacheFile);
            }
        } else {
            $this->downloadGitScmHtml($cacheFile);
        }

        $this->parseLatestVersionFromHtml($cacheFile);
    }

    // ------------------------------------------------------------------
    // Internals
    // ------------------------------------------------------------------

    private function buildBaseCommand(string $gitCmd): array
    {
        $sudo = (defined('APP_SUDO') && trim(APP_SUDO) !== '') ? APP_SUDO . ' -u www-data ' : '';
        $gitDir   = realpath($this->appPath . $this->appRoot . GitPaths::FOLDER);
        $workTree = realpath($this->appPath . $this->appRoot) . DIRECTORY_SEPARATOR;

        $fullCommand = sprintf(
            '%s%s --git-dir="%s" --work-tree="%s" %s',
            $sudo,
            $this->gitExec,
            $gitDir,
            $workTree,
            $gitCmd
        );

        $shellPrompt = '$ ' . $fullCommand;

        return [$fullCommand, $shellPrompt, $workTree];
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
     * Handle the special "git clone ..." logic, including socket branch.
     *
     * @return array{output:array<int,string>,errors:array<string,string>}
     */
    private function handleCloneCommand(string $cmd, string $workTree): array
    {
        $output = [];
        $errors = [];

        if (
            preg_match(
                '/^git\s+clone\s+(http(?:s)?:\/\/([^@\s]+)@github\.com\/[\w.-]+\/[\w.-]+\.git)(?:\s*([\w.-]+))?/i',
                $cmd,
                $githubRepo
            )
            || preg_match(
                '/^git\s+clone\s+(http(?:s)?:\/\/github\.com\/[\w.-]+\/[\w.-]+\.git)(?:\s*([\w.-]+))?/i',
                $cmd,
                $githubRepo
            )
        ) {
            $parsedUrl = parse_url($_ENV['GIT']['ORIGIN_URL']);
            $token     = $_ENV['GITHUB']['OAUTH_TOKEN'] ?? '';

            $remoteUrl = "https://{$token}@{$parsedUrl['host']}{$parsedUrl['path']}.git";

            // Preserve your original logic: append remoteUrl to $cmd
            $fullCommand = $cmd . ' ' . $remoteUrl;

            // No socket -> run via cis_run_process()
            if (
                !isset($GLOBALS['runtime']['socket'])
                || !is_resource($GLOBALS['runtime']['socket'])
            ) {
                $res = $this->runProcess($fullCommand, $workTree);

                if (!empty($res['stdout'])) {
                    $output[] = $res['stdout'];
                }
                if (!empty($res['stderr'])) {
                    $errors['stderr'] = $res['stderr'];
                }
            } else {
                // socket handling unchanged
                $message = "cmd: $fullCommand\n";
                fwrite($GLOBALS['runtime']['socket'], $message);
                $response = '';
                while (!feof($GLOBALS['runtime']['socket'])) {
                    $response .= fgets($GLOBALS['runtime']['socket'], 1024);
                }
                fclose($GLOBALS['runtime']['socket']);
                $output[] = trim($response);
            }
        }

        return compact('output', 'errors');
    }

    /**
     * Thin wrapper around cis_run_process() to make mocking easier.
     *
     * @param string $command
     * @param string $workTree
     * @return array{exitCode:int,stdout:string,stderr:string}
     */
    private function runProcess(string $command, string $workTree): array
    {
        // Assumes cis_run_process($cmd, $cwd) is defined elsewhere.
        return cis_run_process($command, $workTree);
    }

    private function ensureSshDirectory(): void
    {
        $base = defined('APP_PATH') ? APP_PATH : dirname(__DIR__) . DIRECTORY_SEPARATOR;
        $dirname = $base . '.ssh';

        if (!is_dir($dirname)) {
            if (!@mkdir($dirname, 0755, true)) {
                $this->globalErrors['APP_BASE'][basename($dirname)]
                    = "$dirname could not be created.";
            }
        }
    }

    /**
     * Determine the git executable and define GIT_EXEC if needed.
     */
    private function resolveGitExec(): string
    {
        if (defined('GIT_EXEC')) {
            return GIT_EXEC;
        }

        $exec = stripos(PHP_OS, 'WIN') === 0
            ? 'git.exe'
            : ($_ENV['GIT']['EXEC'] ?? '/usr/bin/git');

        defined('GIT_EXEC') or define('GIT_EXEC', $exec);

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

        $expr = $_ENV['GITHUB']['EXPR_VERSION'];
        $gitVersion = exec(GIT_EXEC . ' --version');

        if (preg_match($expr, $gitVersion, $match)) {
            defined('GIT_VERSION') or define('GIT_VERSION', rtrim($match[1], '.'));
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
            if (is_dir($this->appPath . $path)) {
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
        $url    = 'https://git-scm.com/downloads';
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

        $html = curl_exec($handle);
        if (!empty($html)) {
            if (file_put_contents($cacheFile, $html) === false) {
                $this->globalErrors['GIT_LATEST'] = "$url returned empty.";
            }
        } else {
            $this->globalErrors['GIT_LATEST'] = "$url returned empty.";
        }

        curl_close($handle);
    }

    private function parseLatestVersionFromHtml(string $cacheFile): void
    {
        libxml_use_internal_errors(true);

        $doc = new DOMDocument('1.0', 'utf-8');
        $html = @file_get_contents($cacheFile);
        if ($html === false) {
            $this->globalErrors['GIT_LATEST'] = "Could not read $cacheFile";
            return;
        }

        $doc->loadHTML($html);
        $contentNode = $doc->getElementById("main");

        if (!$contentNode) {
            $this->globalErrors['GIT_LATEST'] = "Could not find #main in git-scm HTML.";
            return;
        }

        $nodes = getElementsByClass($contentNode, 'span', 'version');
        if (empty($nodes) || !isset($nodes[0])) {
            $this->globalErrors['GIT_LATEST'] = "No .version span found.";
            return;
        }

        $nodeValue = $nodes[0]->nodeValue;
        $pattern   = '/(\d+\.\d+\.\d+)/';

        if (preg_match($pattern, $nodeValue, $matches)) {
            $version = $matches[1];
            defined('GIT_LATEST') or define('GIT_LATEST', $version);
        } else {
            $this->globalErrors['GIT_LATEST'] = $nodeValue . ' did not match $version';
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
     * Summary of git_origin_sha_update
     * @return bool|string
     */
    function git_origin_sha_update()
    {
        $manager = GitManager::fromGlobals();
        return $manager->updateOriginSha();
    }
}
