<?php
// README https://github.com/JulioPotier/wp-genders/blob/main/README.md

define( 'GENDER_LOCALE_FILENAME', 'lang_LANG' ); // Change the value with your locale like "en_US" or the global one "en"

$ar = 
[ 'i18n' => // do not rename this key
    // Order is not important here.
    [ 'fr_FR-aux' => 
        [ 
            "NE" => "à un membre du groupe d’administration",
            "NSP" => "aux membres du groupe d’administration",
            "M" => "à l’administrateur",
            "F" => "à l’administratrice",
            "MS" => "aux administrateurs",
            "FS" => "aux administratrices"
        ],
    'fr_FR-les' => 
        [ 
            "NE" => "un membre du groupe d’administration",
            "NSP" => "ayx membres du groupe d’administration",
            "M" => "l’administrateur",
            "F" => "l’administratrice",
            "MS" => "les administrateurs",
            "FS" => "les administratrices"
        ]
    ],
    /** Order is important here.
     *  You can use "[]" and "?" for regex, nothing else.
     * [] should contains possible chars at this place like "L[’']administrateur": maybe it's ' or ’
     * ? can be used to tag a possible existing char here, or not like "L’ admisnistrateur,? ou l’administratrice": maybe the comma is not present.
     *  Final regex is "/(.*)…(.*)/u"
     *  Replacement is $1…$2
     */
    'strings' => // do not rename this key
        [
            "à l[’']administrateur ou l[’']administratrice"     => 'fr_FR-aux',
            "[’']administrateur,? ou l[’']administratrice"      => 'fr_FR-les',
            "es administrateurs,? et les administratrices"      => 'fr_FR-les',
            "es administrateurs, les administratrices"          => 'fr_FR-les',
        ]
];
header('Content-Type: application/json');
file_put_contents( GENDER_LOCALE_FILENAME . '.json', json_encode( $ar, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT ) );