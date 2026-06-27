<?php declare(strict_types=1);
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Converts a full name to its Czech vocative (oslovení) form.
 *
 * Inflects both male and female first names and last names using
 * suffix-based rules loaded from serialized .dat files.
 *
 * @author Petro Joachim <petr@joachim.cz>, Jaroslav Týc <mail@jaroslavtyc.com>, Robin Bláha <robin.blaha@inteway.net>
 */
class CzechVocative
{
    /**
     * Format a full name in the vocative case.
     *
     * Example: "Petr Novák" → "Petře Nováku"
     *
     * @param string $full_name
     * @return string The name inflected in the vocative.
     */
    public function format(string $full_name): string
    {
        $name_parts = explode(" ", $full_name);

        foreach ($name_parts as &$name) {
            $name = $this->singleVocative($name);
        }

        return implode(" ", $name_parts);
    }

    /**
     * Inflect a single name part into the vocative.
     *
     * @param string   $name
     * @param bool|null $isWoman
     * @param bool|null $isLastName
     * @return string
     */
    private function singleVocative(string $name, ?bool $isWoman = null, ?bool $isLastName = null): string
    {
        $name = trim($name);
        if (preg_match("~[^[:alpha:]]$~u", $name)) {
            return $name;
        }
        $name = ucfirst(strtolower($name));
        $key = strtolower($name);

        if ($isWoman === null) {
            $isWoman = !$this->isMale($key);
        }

        if ($isWoman) {
            if ($isLastName === null) {
                [, $type] = $this->getMatchingSuffix(
                    $key,
                    $this->getWomanFirstVsLastNameSuffixes()
                );

                $isLastName = $type === "l";
            }

            if ($isLastName) {
                return $this->vocativeWomanLastName($name);
            }

            return $this->vocativeWomanFirstName($name);
        }

        return $this->vocativeMan($name, $key);
    }

    /**
     * Determine whether a name is male based on suffix rules.
     *
     * @param string $name Lowercased name.
     * @return bool
     */
    private function isMale(string $name): bool
    {
        $name = strtolower($name);

        [, $sex] = $this->getMatchingSuffix(
            $name,
            $this->getManVsWomanSuffixes()
        );

        return $sex !== "w";
    }

    /**
     * Inflect a male name to the vocative.
     *
     * @param string $name
     * @param string $key Lowercased name for suffix matching.
     * @return string
     */
    private function vocativeMan(string $name, string $key): string
    {
        [$match, $suffix] = $this->getMatchingSuffix(
            $key,
            $this->getManSuffixes()
        );

        if ($match) {
            $name = substr($name, 0, -1 * strlen($match));
        }
        $name .= $suffix;

        return ucfirst(strtolower($name));
    }

    /**
     * Inflect a female first name to the vocative.
     *
     * @param string $name
     * @return string
     */
    private function vocativeWomanFirstName(string $name): string
    {
        if (str_ends_with($name, "a")) {
            return substr($name, 0, -1) . "o";
        }

        return $name;
    }

    /**
     * Female last names stay unchanged in the vocative.
     *
     * @param string $name
     * @return string
     */
    private function vocativeWomanLastName(string $name): string
    {
        return $name;
    }

    /**
     * Find the longest matching suffix from the rules table and return
     * [matched_suffix, replacement_value].
     *
     * @param string $name
     * @param array  $suffixes Associative array of suffix => value.
     * @return array{string, mixed}
     */
    private function getMatchingSuffix(string $name, array $suffixes): array
    {
        foreach (range(strlen($name), 1) as $length) {
            $suffix = substr($name, -1 * $length);
            if (array_key_exists($suffix, $suffixes)) {
                return [$suffix, $suffixes[$suffix]];
            }
        }

        return ["", $suffixes[""]];
    }

    /** Cached male suffix rules. */
    private ?array $manSuffixes = null;

    /** Cached male-vs-female suffix rules. */
    private ?array $manVsWomanSuffixes = null;

    /** Cached female-first-name-vs-last-name rules. */
    private ?array $womanFirstVsLastSuffixes = null;

    /**
     * Get (and cache) male vocative suffix rules.
     *
     * @return array
     */
    private function getManSuffixes(): array
    {
        if ($this->manSuffixes === null) {
            $this->manSuffixes = $this->readSuffixes("man_suffixes");
        }

        return $this->manSuffixes;
    }

    /**
     * Read a serialized suffix array from a .dat file.
     *
     * @param string $file Filename stem (without extension).
     * @return array
     */
    private function readSuffixes(string $file): array
    {
        $filename = __DIR__ . "/data/" . $file . ".dat";

        return unserialize(file_get_contents($filename), ["allowed_classes" => false]);
    }

    /**
     * Get (and cache) male-vs-female suffix rules.
     *
     * @return array
     */
    private function getManVsWomanSuffixes(): array
    {
        if ($this->manVsWomanSuffixes === null) {
            $this->manVsWomanSuffixes = $this->readSuffixes("man_vs_woman_suffixes");
        }

        return $this->manVsWomanSuffixes;
    }

    /**
     * Get (and cache) female first-name-vs-last-name suffix rules.
     *
     * @return array
     */
    private function getWomanFirstVsLastNameSuffixes(): array
    {
        if ($this->womanFirstVsLastSuffixes === null) {
            $this->womanFirstVsLastSuffixes = $this->readSuffixes("woman_first_vs_last_name_suffixes");
        }

        return $this->womanFirstVsLastSuffixes;
    }
}