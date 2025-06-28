<?php

define('LICENSE_DIR', dirname(__DIR__, 1) . '/third_party_licenses');
define('OUTPUT_NOTICE', dirname(__DIR__, 1) . '/third_party_licenses/NOTICE2.md');

function collectLicenses(): array
{
    $licenses = [];

    foreach (glob(LICENSE_DIR . '/*.{MIT,BSD,Apache,GPL}', GLOB_BRACE) as $filePath) {
        $content = trim(file_get_contents($filePath));
        $hash = md5($content);

        if (!isset($licenses[$hash])) {
            $licenses[$hash] = [
                'files' => [basename($filePath)],
                'content' => $content
            ];
        } else {
            $licenses[$hash]['files'][] = basename($filePath);
        }
    }

    return $licenses;
}

function generateNoticeMarkdown(array $licenses): string
{
    $out = "# NOTICE\n\nThis file lists third-party licenses included in this application.\n\n";

    foreach ($licenses as $group) {
        $out .= "## Used By:\n";
        foreach ($group['files'] as $file) {
            $out .= "- `$file`\n";
        }

        $out .= "\n```\n" . $group['content'] . "\n```\n\n---\n";
    }

    return $out;
}

// Run and export
$licenses = collectLicenses();
file_put_contents(OUTPUT_NOTICE, generateNoticeMarkdown($licenses));

echo "Generated NOTICE.md successfully.\n";