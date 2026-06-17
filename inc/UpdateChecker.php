<?php

declare(strict_types=1);

namespace RhSeo;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * GitHub-basierter Auto-Update-Checker für rh-seo. Zieht das Release-ZIP aus den
 * GitHub Releases (Tags `v*`), inkl. gebundeltem Core und plugin-update-checker.
 */
final class UpdateChecker
{
    public const GITHUB_REPO = 'https://github.com/herbeckrobin/rh-seo/';
    public const PLUGIN_SLUG = 'rh-seo';

    public function boot(): void
    {
        if (! function_exists('add_filter') || ! class_exists(PucFactory::class)) {
            return;
        }

        $updateChecker = PucFactory::buildUpdateChecker(
            self::GITHUB_REPO,
            RHSEO_PLUGIN_FILE,
            self::PLUGIN_SLUG
        );

        $vcsApi = $updateChecker->getVcsApi();
        if ($vcsApi !== null && method_exists($vcsApi, 'enableReleaseAssets')) {
            $vcsApi->enableReleaseAssets();
        }
    }
}
