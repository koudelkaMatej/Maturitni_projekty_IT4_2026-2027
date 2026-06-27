<?php declare(strict_types=1);
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * @param string $full_name
 * @author Petro Joachim <petr@joachim.cz>, Jaroslav Týc <mail@jaroslavtyc.com>, Robin Bláha <robin.blaha@inteway.net>
 */
class CzechVocative
{
    /**
     * @param string $full_name
     * @return string the name inflicted in the vocative
     */
    public function format(string $full_name): string
    {
        $name_parts = explode(" ", $full_name);

        foreach ($name_parts as &$name) {
            $name = $this->singleVocative($name);
        }

        return implode(" ", $name_parts);
    }

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

    private function isMale(string $name): bool
    {
        $name = strtolower($name);

        [, $sex] = $this->getMatchingSuffix(
            $name,
            $this->getManVsWomanSuffixes()
        );

        return $sex !== "w";
    }

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

    private function vocativeWomanFirstName(string $name): string
    {
        if (str_ends_with($name, "a")) {
            return substr($name, 0, -1) . "o";
        }

        return $name;
    }

    private function vocativeWomanLastName(string $name): string
    {
        return $name;
    }

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

    private ?array $manSuffixes = null;
    private ?array $manVsWomanSuffixes = null;
    private ?array $womanFirstVsLastSuffixes = null;

    private function getManSuffixes(): array
    {
        if ($this->manSuffixes === null) {
            $this->manSuffixes = $this->readSuffixes("man_suffixes");
        }

        return $this->manSuffixes;
    }

    private function readSuffixes(string $file): array
    {
        $filename = __DIR__ . "/data/" . $file . ".dat";

        return unserialize(file_get_contents($filename), ["allowed_classes" => false]);
    }

    private function getManVsWomanSuffixes(): array
    {
        if ($this->manVsWomanSuffixes === null) {
            $this->manVsWomanSuffixes = $this->readSuffixes("man_vs_woman_suffixes");
        }

        return $this->manVsWomanSuffixes;
    }

    private function getWomanFirstVsLastNameSuffixes(): array
    {
        if ($this->womanFirstVsLastSuffixes === null) {
            $this->womanFirstVsLastSuffixes = $this->readSuffixes("woman_first_vs_last_name_suffixes");
        }

        return $this->womanFirstVsLastSuffixes;
    }
}