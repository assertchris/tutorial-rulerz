<?php

require "vendor/autoload.php";

// $document = new DomDocument();
// $document->loadHTMLFile("library.xml");
//
// $tracks = $document
//     ->getElementsByTagName("dict")[0] // root node
//     ->getElementsByTagName("dict")[0] // track container
//     ->getElementsByTagName("dict");   // track nodes
//
// $clean = [];
//
// foreach ($tracks as $track) {
//     $key = null;
//     $all = [];
//
//     foreach ($track->childNodes as $node) {
//         if ($node->tagName == "key") {
//             $key = str_replace(" ", "", $node->nodeValue);
//         } else {
//             $all[$key] = $node->nodeValue;
//             $key = null;
//         }
//     }
//
//     $clean[] = $all;
// }
//
// file_put_contents(
//     "tracks.json", json_encode($clean)
// );
//
// exit();

$filtered = [];
$fields = false;
$filterCount = 0;

if (isset($_POST["field"])) {
    $fields = $_POST["field"];
    $operators = $_POST["operator"];
    $values = $_POST["query"];

    $query = "";

    foreach ($fields as $i => $field) {
        $operator = $operators[$i];
        $value = $values[$i];

        if (!empty($field) && !empty($operator) && !empty($value)) {
            if (!empty($query)) {
                $query .= " and ";
            }

            $query .= "my_{$operator}({$field}, '{$value}')";
        }
    }

    $filterCount = count($fields);

    $compiler = new RulerZ\Compiler\EvalCompiler(
        new RulerZ\Parser\HoaParser()
    );

    $rulerz = new RulerZ\RulerZ(
        $compiler, [
            $visitor = new RulerZ\Compiler\Target\ArrayVisitor(),
        ]
    );

    $visitor->setOperator("my_is", function($field, $value) {
        return $field == $value;
    });

    $visitor->setOperator("my_not", function($field, $value) {
        return $field != $value;
    });

    $visitor->setOperator("my_contains", function($field, $value) {
        return stristr($field, $value);
    });

    $visitor->setOperator("my_begins", function($field, $value) {
        return preg_match("/^{$value}.*/i", $field) == 1;
    });

    $visitor->setOperator("my_ends", function($field, $value) {
        return preg_match("/.*{$value}$/i", $field) == 1;
    });

    $visitor->setOperator("my_gt", function($field, $value) {
        return $field > $value;
    });

    $visitor->setOperator("my_lt", function($field, $value) {
        return $field < $value;
    });

    $tracks = json_decode(
        file_get_contents("tracks.json"), true
    );

    $filtered = $rulerz->filter($tracks, $query);
}

function option($value, $label, $selected = null) {
    $parameters = "value={$value}";

    if ($value == $selected) {
        $parameters .= " selected='selected'";
    }

    return "<option {$parameters}>{$label}</option>";
}

?>
<!doctype html>
<html lang="en">
    <body>
<form method="post">
<?php if ($fields): ?>
<?php for ($i = 0; $i < $filterCount; $i++): ?>
    <div>
        <select name="field[<?= $i ?>]">
            <?= option("Name", "Name", $fields[$i]) ?>
            <?= option("Artist", "Artist", $fields[$i]) ?>
            <?= option("Album", "Album", $fields[$i]) ?>
            <?= option("Year", "Year", $fields[$i]) ?>
        </select>
        <select name="operator[<?= $i; ?>]">
            <?= option("contains", "contains", $operators[$i]) ?>
            <?= option("begins", "begins with", $operators[$i]) ?>
            <?= option("ends", "ends with", $operators[$i]) ?>
            <?= option("is", "is", $operators[$i]) ?>
            <?= option("not", "is not", $operators[$i]) ?>
            <?= option("gt", "greater than", $operators[$i]) ?>
            <?= option("lt", "less than", $operators[$i]) ?>
        </select>
        <input
            type="text"
            name="query[<?= $i ?>]"
            value="<?= $values[$i] ?>" />
    </div>
<?php endfor; ?>
<?php endif; ?>
    <div>
        <select name="field[<?= $filterCount ?>]">
            <?= option("Name", "Name") ?>
            <?= option("Artist", "Artist") ?>
            <?= option("Album", "Album") ?>
            <?= option("Year", "Year") ?>
        </select>
        <select name="operator[<?= $filterCount ?>]">
            <?= option("contains", "contains") ?>
            <?= option("begins", "begins with") ?>
            <?= option("ends", "ends with") ?>
            <?= option("is", "is") ?>
            <?= option("not", "is not") ?>
            <?= option("gt", "greater than") ?>
            <?= option("lt", "less than") ?>
        </select>
        <input type="text" name="query[<?= $filterCount ?>]" />
    </div>
    <input type="submit" value="filter" />
</form>
<ol>
    <?php foreach ($filtered as $track): ?>
        <li>
            <?= $track["Artist"] ?>,
            <?= $track["Album"] ?>,
            <?= $track["Name"] ?>
        </li>
    <?php endforeach; ?>
</ol>
    </body>
</html>
