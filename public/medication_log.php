<?php
require_once '../config/config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    function medication_log()
    {
        if (isset($_GET['app']) && $_GET['app'] == 'medication_log') {
            $jsonFile = APP_BASE['data'] . 'medication_log.json';

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

            // Ensure data is an array
            if (!is_array($data)) {
                $data = [];
            }

            $date = $_POST["date"];
            $parsedTime = date_parse($_POST["time_slot"]);
            $formattedTime = sprintf('%02d:%02d', $parsedTime["hour"], $parsedTime["minute"]);
            $timeSlot = DateTime::createFromFormat('H:i', $formattedTime)->format('h:i A');

            $status = $_POST["status"] ?? null;
            if ($status === null)
                return;

            $note = $_POST["note"] ?? "";

            // Determine if it's AM or PM
            $isAM = (int) date('H', strtotime($timeSlot)) < 12;
            $isPM = !$isAM;

            // Find existing entry for the date
            $found = false;
            foreach ($data as &$entry) {
                if ($entry["date"] === $date) {
                    // Ensure "doses" is an array
                    if (!isset($entry["doses"]) || !is_array($entry["doses"])) {
                        $entry["doses"] = [];
                    }

                    // Track existing AM/PM doses
                    $existingAM = null;
                    $existingPM = null;

                    foreach ($entry["doses"] as &$dose) {
                        $doseHour = (int) date('H', strtotime($dose["time"]));

                        if ($doseHour < 12) {
                            $existingAM = &$dose;
                        } else {
                            $existingPM = &$dose;
                        }
                    }

                    // Append the new dose (allow multiple doses)
                    $entry["doses"][] = [
                        "time" => $timeSlot,
                        "status" => $status,
                        "note" => $note
                    ];

                    // Sort doses: AM first, PM second
                    usort($entry["doses"], function ($a, $b) {
                        return strtotime($a["time"]) - strtotime($b["time"]);
                    });

                    $found = true;
                    break;
                }
            }

            // If no entry exists for the date, create a new one with the first dose
            if (!$found) {
                $data[] = [
                    "date" => $date,
                    "doses" => [
                        [
                            "time" => $timeSlot,
                            "status" => $status,
                            "note" => $note
                        ]
                    ]
                ];
            }

            // Save to file
            file_put_contents($jsonFile, json_encode(array_reverse(array_slice(array_reverse($data), 0, 50)), JSON_PRETTY_PRINT));

            // Redirect back
            die(header('Location: http://' . APP_DOMAIN . '/?' . http_build_query(['path' => '', 'app' => 'medication_log'])));
        }
    }

    medication_log();

    /*
    if (isset($_POST['ace_path']) && realpath($path = APP_PATH . (APP_ROOT ?? (APP_BASE['clients'] . ($_GET['client'] . '/' . ($_GET['domain'] ?? '') ?? $_GET['domain'] ?? ''))) . ($_GET['path'] . '/' . $_GET['file'] ?? $_POST['ace_path']))) {
        //dd($path, false);   
        if (isset($_POST['ace_contents']))
            //dd($_POST['ace_contents']);
            file_put_contents($path, $_POST['ace_contents']);

        //dd($_POST, true);
        //http://localhost/Array?app=errors&path=&file=test.txt    obj.prop.second = value    obj->prop->second = value
        //dd( APP_URL . '1234?' . http_build_query(['path' => dirname( $_POST['ace_path']), 'app' => 'errors', 'file' => basename($path)]), true);


        //dd(APP_URL_BASE);

        die(header('Location: ' . APP_URL_BASE['scheme'] . '://' . APP_URL_BASE['host'] . '/?' . http_build_query(APP_QUERY + ['path' => dirname($_POST['ace_path']), 'file' => basename($path)])));
    } else
        dd("Path: $path was not found.", true);
    //dd($_POST);
*/
    //  if (isset($_GET['file'])) {
//    file_put_contents($projectRoot.(!$_POST['path'] ? '' : DIRECTORY_SEPARATOR.$_POST['path']).DIRECTORY_SEPARATOR.$_POST['file'], $_POST['editor']);
//  }

    /*
        if (isset($_POST['cmd'])) {
          if ($_POST['cmd'] && $_POST['cmd'] != '') 
            if (preg_match('/^install/i', $_POST['cmd']))
              include('templates/' . preg_split("/^install (\s*+)/i", $_POST['cmd'])[1] . '.php');
            else if (preg_match('/^php(:?(.*))/i', $_POST['cmd'], $match))
              exec($_POST['cmd'], $output);
            else if (preg_match('/^composer(:?(.*))/i', $_POST['cmd'], $match)) {
            $output[] = 'env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; sudo ' . COMPOSER_EXEC . ' ' . $match[1];
    $proc=proc_open('env COMPOSER_ALLOW_SUPERUSER=' . COMPOSER_ALLOW_SUPERUSER . '; sudo ' . COMPOSER_EXEC . ' ' . $match[1],
      array(
        array("pipe","r"),
        array("pipe","w"),
        array("pipe","w")
      ),
      $pipes);
    [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
    $output[] = 'Composer: ' . (!isset($stdout) ? NULL : $stdout . (!isset($stderr) ? NULL : ' Error: ' . $stderr) . (!isset($exitCode) ? NULL : ' Exit Code: ' . $exitCode));
    $output[] = $_POST['cmd'];

            } else if (preg_match('/^git(:?(.*))/i', $_POST['cmd'], $match)) {
            $output[] = APP_SUDO . GIT_EXEC . ' ' . $match[1];
    $proc=proc_open(APP_SUDO . GIT_EXEC . ' ' . $match[1],
      array(
        array("pipe","r"),
        array("pipe","w"),
        array("pipe","w")
      ),
      $pipes);
    [$stdout, $stderr, $exitCode] = [stream_get_contents($pipes[1]), stream_get_contents($pipes[2]), proc_close($proc)];
    $output[] = 'Composer: ' . (!isset($stdout) ? NULL : $stdout . (!isset($stderr) ? NULL : ' Error: ' . $stderr) . (!isset($exitCode) ? NULL : ' Exit Code: ' . $exitCode));
    $output[] = $_POST['cmd'];

            }

              //exec($_POST['cmd'], $output);
            else echo $_POST['cmd'] . "\n";
          //else var_dump(NULL); // eval('echo $repo->status();')
          if (!empty($output)) echo 'PHP >>> ' . join("\n... <<< ", $output) . "\n"; // var_dump($output);
          //else var_dump(get_class_methods($repo));
          exit();
        }
    */
}

$notesData = file_exists($jsonFile = '../' . APP_BASE['data'] . 'notes.json') ? json_decode(file_get_contents($jsonFile), true) : ['{
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

$jsonFile = APP_BASE['data'] . 'medication_log.json';
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

$logs = array_reverse($data); ?>
<!DOCTYPE html>
<html>

<head>
</head>

<body>
    <?php
    // Check if the data is an array, if not, initialize it as an empty array
    $logMap = [];

    // Normalize log by date for easy lookup
    foreach ($data as $entry) {
        if (!is_array($entry))
            $entry = json_decode($entry, true);
        if (isset($entry['date'])) {
            $logMap[$entry['date']] = $entry['doses'];
        }
    }

    // Calculate this week's Sunday–Saturday range
    $today = new DateTime();
    $startOfWeek = clone $today;
    $startOfWeek->modify('last Monday'); // Change to 'last Monday' if you prefer
    $endOfWeek = clone $startOfWeek;
    $endOfWeek->modify('+6 days');

    echo '<div style="text-align: center; width: 50%; margin: 0 auto;">
    <div style="width: 400px; text-align: left; margin-left: auto; margin-right: auto; margin-bottom: 20px;">';

    echo "<h2>Dashboard's Stack</h2>";
    echo "<ul>";

    // WEEK HEADER
    echo "<li style='background-color: #333; color: #fff; padding: 5px; font-weight: bold;'>
        Monday - Sunday" /*. $startOfWeek->format("M j") . " - " . $endOfWeek->format("M j") . " (W" . $startOfWeek->format('W') . ")*/ . "
      </li>";

    // Build week view
    for ($i = 7; $i >= 1; $i--) {
        $date = clone $startOfWeek;
        $date->modify("+$i day");
        $dateStr = $date->format('Y-m-d');
        $dayName = $date->format('l');
        $isWeekend = in_array($date->format('w'), ['0', '6']);
        $bgColor = $isWeekend ? 'background-color: #ffe6e6;' : ($i % 2 === 0 ? 'background-color: lightblue;' : '');

        echo "<li style=\"$bgColor\">
        <strong>$dateStr ($dayName)</strong>
        <ul>";

        if (isset($logMap[$dateStr])) {
            // Reverse dose order (PM first, then AM)
            $doses = array_reverse($logMap[$dateStr]);
            foreach ($doses as $dose) {
                $color = $dose["status"] === "missed" ? "red" : "green";
                echo "<li style='color: $color;'>{$dose['time']} - {$dose['status']} ({$dose['note']})</li>";
            }
        } else {
            // No entry? Assume both doses missed
            //echo "<li style='color: red;'>10:00 PM - not yet?</li>";
            //echo "<li style='color: red;'>10:00 AM - not yet?</li>";
        }

        echo "</ul></li>";
    }

    echo "</ul></div></div>"; ?>
</body>

</html>