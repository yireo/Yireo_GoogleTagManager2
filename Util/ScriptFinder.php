<?php declare(strict_types=1);

namespace Yireo\GoogleTagManager2\Util;

class ScriptFinder
{
    /**
     * @param string $html
     * @return string[]
     */
    public function find(string $html): array
    {
        $html = trim($html);
        if (empty($html)) {
            return [];
        }

        if (false === preg_match_all('#<script([^>]*)>(.*)</script>#msi', $html, $matches)) {
            return [];
        }

        if (empty($matches[0])) {
            return [];
        }

        $scripts = [];
        foreach ($matches[0] as $matchIndex => $match) {
            $fullScript = $matches[0][$matchIndex];
            $scriptAttributes = $matches[1][$matchIndex];
            $inlineJs = $matches[2][$matchIndex];
            if (false === $this->needsCsp($scriptAttributes)) {
                continue;
            }

            $scripts[$fullScript] = $inlineJs;
        }

        return $scripts;
    }

    private function needsCsp(string $scriptAttributes): bool
    {
        if (empty(trim($scriptAttributes))) {
            return true;
        }

        if (strstr($scriptAttributes, 'nonce=')) {
            return false;
        }

        if (preg_match('/type=([\"\']?)([^\"\']*)/msi', $scriptAttributes, $match)) {
            if (false === in_array($match[2], ['application/javascript', 'text/javascript'])) {
                return false;
            }
        }

        return true;
    }
}
