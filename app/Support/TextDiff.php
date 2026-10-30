<?php

namespace App\Support;

/**
 * Line-based diff (longest common subsequence) for revision history.
 * Documentation pages are a few hundred lines, so O(n·m) is fine.
 */
class TextDiff
{
    /**
     * @return array<int, array{0: '='|'+'|'-', 1: string}>
     */
    public static function lines(string $old, string $new): array
    {
        $a = preg_split('/\r\n|\r|\n/', $old) ?: [];
        $b = preg_split('/\r\n|\r|\n/', $new) ?: [];

        // Trim the common head and tail first: most edits are small.
        $head = [];
        while ($a !== [] && $b !== [] && $a[0] === $b[0]) {
            $head[] = ['=', array_shift($a)];
            array_shift($b);
        }

        $tail = [];
        while ($a !== [] && $b !== [] && end($a) === end($b)) {
            array_unshift($tail, ['=', array_pop($a)]);
            array_pop($b);
        }

        $n = count($a);
        $m = count($b);
        $lcs = array_fill(0, $n + 1, array_fill(0, $m + 1, 0));

        for ($i = $n - 1; $i >= 0; $i--) {
            for ($j = $m - 1; $j >= 0; $j--) {
                $lcs[$i][$j] = $a[$i] === $b[$j] ? $lcs[$i + 1][$j + 1] + 1 : max($lcs[$i + 1][$j], $lcs[$i][$j + 1]);
            }
        }

        $body = [];
        $i = $j = 0;

        while ($i < $n && $j < $m) {
            if ($a[$i] === $b[$j]) {
                $body[] = ['=', $a[$i]];
                $i++;
                $j++;
            } elseif ($lcs[$i + 1][$j] >= $lcs[$i][$j + 1]) {
                $body[] = ['-', $a[$i++]];
            } else {
                $body[] = ['+', $b[$j++]];
            }
        }

        while ($i < $n) {
            $body[] = ['-', $a[$i++]];
        }

        while ($j < $m) {
            $body[] = ['+', $b[$j++]];
        }

        return [...$head, ...$body, ...$tail];
    }

    /**
     * Only changed lines with a little context; '…' marks skipped lines.
     *
     * @return array<int, array{0: '='|'+'|'-'|'…', 1: string}>
     */
    public static function compact(string $old, string $new, int $context = 2): array
    {
        $lines = self::lines($old, $new);
        $keep = [];

        foreach ($lines as $index => [$type]) {
            if ($type !== '=') {
                for ($k = max(0, $index - $context); $k <= min(count($lines) - 1, $index + $context); $k++) {
                    $keep[$k] = true;
                }
            }
        }

        $result = [];
        $previous = -1;

        foreach (array_keys($keep) as $index) {
            if ($index > $previous + 1) {
                $result[] = ['…', ''];
            }

            $result[] = $lines[$index];
            $previous = $index;
        }

        return $result;
    }
}
