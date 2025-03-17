<?php

$notesData = file_exists($jsonFile = '../database/notes.json') ? json_decode(file_get_contents($jsonFile), true) : ['{
     "language":"PHP",
     "category":"String Manipulation",
     "snippets":
     [{
        "title":"Bug with notes",
        "description":"",
        "stackoverflow":
        {
            "url":"","title":""
        },
        "code":""
    }]
}'
];

$jsonFile = '../database/medication_log.json';
// Load existing data
$data = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : ['{
        "date": "' . date('Y-m-d') . '",
        "doses": [
            {
                "time": "10:00 AM",
                "status": "taken",
                "note": ""
            },
            {
                "time": "10:00 PM",
                "status": "taken",
                "note": ""
            }
        ]
    }'
];

$logs = array_reverse($data);
echo '<div style="text-align: center; width: 50%; margin: 0 auto;">
        <div style="width: 400px; text-align: left; margin-left: auto; margin-right: auto; margin-bottom: 20px;">';
echo "<h2>Medication History</h2>";
echo "<ul>";

foreach ($logs as $key => $entry) {
    echo "<li style=\"" . ($key % 2 === 0 ? 'background-color: lightblue;' : '') . "\">
            <strong>{$entry['date']}</strong>
            <ul>";
    $entry["doses"] = array_reverse($entry["doses"]);
    foreach ($entry["doses"] as $dose) { // Now looping through indexed array
        $color = $dose["status"] === "missed" ? "red" : "green";
        echo "<li style='color: $color;'>{$dose['time']} - {$dose['status']} ({$dose['note']})</li>";
    }

    echo "</ul></li>";
}

echo "</ul>";
echo '</div></div>';