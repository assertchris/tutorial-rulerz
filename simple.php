<?php

require "vendor/autoload.php";

use RulerZ\Compiler;
use RulerZ\Parser;
use RulerZ\RulerZ;

$compiler = new Compiler\EvalCompiler(
    new Parser\HoaParser()
);

$rulerz = new RulerZ(
    $compiler, [
        new Compiler\Target\ArrayVisitor(),
    ]
);

$tracks = [
    [
        "title"  => "Animus Vox",
        "artist" => "The Glitch Mob",
        "plays"  => 36,
        "year"   => 2010
    ],
    [
        "title"  => "Bad Wings",
        "artist" => "The Glitch Mob",
        "plays"  => 12,
        "year"   => 2010
    ],
    [
        "title"  => "We Swarm",
        "artist" => "The Glitch Mob",
        "plays"  => 28,
        "year"   => 2010
    ]
];

$filtered = $rulerz->filter(
    $tracks,
    "artist = :artist and year < :year and plays < :plays",
    [
        "artist" => "The Glitch Mob",
        "year"   => 2015,
        "plays"  => 20
    ]
);

print_r($filtered);
