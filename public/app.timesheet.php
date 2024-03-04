<?php
/*
include('config.php'); /// BAD ... causes errors due to requireing redunantly.

https://stackoverflow.com/questions/17694894/different-timezone-types-on-datetime-object

*/

if (__FILE__ == get_required_files()[0])
  if ($path = (basename(getcwd()) == 'public')
    ? (is_file('../config.php') ? '../config.php' : (is_file('../config/config.php') ? '../config/config.php' : null))
    : (is_file('config.php') ? 'config.php' : (is_file('config/config.php') ? 'config/config.php' : null))) require_once($path);
else die(var_dump($path . ' path was not found. file=config.php'));

$interval = new DateInterval('PT00H');
//echo $interval->format('%H:%I:%S');

$interval = new DateTime('2023-09-15 00:00:00'); // Example DateTime

list($hrs, $min, $sec) = explode(':', '00:00:06');

$dateInterval = new DateInterval("PT{$hrs}H{$min}M{$sec}S");

// Convert DateInterval to seconds
$totalSeconds = $dateInterval->s + $dateInterval->i * 60 + $dateInterval->h * 3600 + $dateInterval->d * 86400;

// Increment DateInterval by one second
//$totalSeconds++;

// Convert the updated total seconds back to a DateInterval
$dateInterval = DateInterval::createFromDateString("$totalSeconds seconds");

$interval->add($dateInterval);

//dd($interval->format("H:i:s"));

// Define the time ranges for each period

$timeRanges = [
    ['Night', 0, 6],
    ['Morning', 6, 12],
    ['Afternoon', 12, 18],
    ['Evening', 18, 22],
    ['Night', 22, 24],
];

$today = new DateTime();
$today->modify('last monday');

$weeklyHours = [
  $today->format('Y-m-d') => [
    ['Morning', new DateInterval('PT00H00M00S'), new DateInterval('PT00H00M00S')], // working | idleing
    ['Afternoon', new DateInterval('PT00H00M00S'), new DateInterval('PT00H00M00S')],
    ['Evening', new DateInterval('PT00H00M00S'), new DateInterval('PT00H00M00S')],
    ['Night', new DateInterval('PT00H00M00S'), new DateInterval('PT00H00M00S')]
  ]
];

$totalHours = [
    ['Morning', new DateInterval('PT00H00M00S'), new DateInterval('PT00H00M00S')],
    ['Afternoon', new DateInterval('PT00H00M00S'), new DateInterval('PT00H00M00S')],
    ['Evening', new DateInterval('PT00H00M00S'), new DateInterval('PT00H00M00S')],
    ['Night', new DateInterval('PT00H00M00S'), new DateInterval('PT00H00M00S')]
];

// json does not like excess commas ..,
// does not like comments  /* ... */
$json_data = <<<END
{
    "2023-09-18T12:00:00-14:00": {
        "12:35:59": "00:00:12",
        "12:46:44": "00:00:15"
    },
    "2023-09-19T18:00:00-22:00": {
        "18:16:50": "00:00:21",
        "18:24:21": "00:00:17"
    }
}
END;

!is_file(APP_PATH . APP_BASE['database'] . 'weekly-timesheet-' . date('Y-m') . '.json')
  and @touch(APP_PATH . APP_BASE['database'] . 'weekly-timesheet-' . date('Y-m') . '.json');

$json_data = json_decode(file_get_contents(APP_PATH . APP_BASE['database'] . 'weekly-timesheet-' . date('Y-m') . '.json'), true);

if (empty($json_data))
  $json_data = json_decode('{' . '"' . date('Y-m-d') . 'T' . date('H').':00:00-24:00' . '": {' . '} }', true);

$currentTime = new DateTime(); // Get current time
$currentTime->setTime('12', '00');

function int_pad($n, $len) {
    $intPart = (int)$n;

    if (!$intPart) return str_repeat('0', $len - 1) . $n;

    return str_repeat('0', (int)max(0, $len - 1 - floor(log($intPart, 10)))) . $n;
}

/*
Address possible bug ... [{"2023-09-26T23:00:00-24:00":[]}]
*/

$matches = (array) [];
if (!empty($json_data))
  foreach ($json_data as $key => $idleTimes) {

    if ($key)
      if ($dateTime = new DateTime($key))
        preg_match('/^(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2}:\d{2})[-+](\d{2}:\d{2})$/', $key, $matches);

  $date = new DateTime();

  if (!empty($matches)) {

    $sevenDaysAfter = clone $today;
    $sevenDaysAfter->modify('+7 days');

    if (new DateTime($matches[1]) >= $today && new DateTime($matches[1]) <= $sevenDaysAfter) {
      (isset($weeklyHours[$matches[1]]) ?:
        $weeklyHours[$matches[1]] = [
          ['Morning', new DateInterval('PT00H00M00S'), new DateInterval('PT00H00M00S')],
          ['Afternoon', new DateInterval('PT00H00M00S'), new DateInterval('PT00H00M00S')],
          ['Evening', new DateInterval('PT00H00M00S'), new DateInterval('PT00H00M00S')],
          ['Night', new DateInterval('PT00H00M00S'), new DateInterval('PT00H00M00S')]
        ]);
    } elseif (!isset($weeklyHours[$matches[1]])) // $today->format('Y-m-d')
      continue;


    $date = $matches[1];
    $periodStart = $matches[2];
    $periodEnd = $matches[3];

    $firstPeriodStart = new DateTime($date . ' ' . $periodStart);
    $firstPeriodEnd = new DateTime($date . ' ' . $periodEnd);

      foreach ($timeRanges as $index => $range) {
        $working_time = new DateInterval('PT0S'); // Initialize working time interval
        $idle_time = new DateInterval('PT0S'); // Initialize idle time interval
              
        $rotationIndex = null;

        if ($rotationIndex === null) //{
          $rotationIndex = ($index - 1 < 0 ? 0 : $index - 1);
        else {
          $rotationIndex = ($rotationIndex < 0 ? 0 : $rotationIndex);
        }

        // echo 'start / end: ' . int_pad($range[1], 2) . ' -> ' . int_pad($range[2], 2) . "<br />\n";

        $firstRangeStart = new DateTime($date . ' ' . int_pad($range[1], 2) . ':00:00'); // !DateInterval('PT' . int_pad($range[1], 2) . 'H00M00S' );
        $firstRangeEnd = new DateTime($date . ' ' . int_pad($range[2], 2) . ':00:00'); // !DateInterval('PT' . int_pad($range[2], 2) . 'H00M00S' );

        if ($firstRangeStart >= $firstPeriodStart) {

          if ($firstPeriodEnd >= $firstRangeEnd) {
            // echo 'this fits here ... ' . $firstRangeStart->format('H:i:s') . '>=' . $firstPeriodStart->format('H:i:s')  . ' START &amp;&amp; ' . $firstPeriodEnd->format('H:i:s') . '>=' . $firstRangeEnd->format('H:i:s')  . " END<br />\n";
            
            $working_time = clone $weeklyHours[$date][$rotationIndex][1];

// Subtract $firstRangeStart from $firstRangeEnd 

list($hrs, $min, $sec) = explode(':', $firstRangeStart->format('H:i:s'));

$endTime = clone $firstRangeEnd;

$endTime->sub(new DateInterval("PT{$hrs}H{$min}M{$sec}S"));

list($hrs, $min, $sec) = explode(':', $endTime->format('H:i:s'));

$dateInterval = new DateInterval("PT{$hrs}H{$min}M{$sec}S");

// Convert DateInterval to seconds
$totalSeconds = $dateInterval->s + $dateInterval->i * 60 + $dateInterval->h * 3600 + $dateInterval->d * 86400;

$workingSeconds = $working_time->s + $working_time->i * 60 + $working_time->h * 3600 + $working_time->d * 86400;

$totalSeconds = $totalSeconds + $workingSeconds;

/*
Convert seconds back into DateInterval HH:MM:SS
*/
$addHrs = floor($totalSeconds / 3600);
$addMin = floor(($totalSeconds % 3600) / 60);
$addSec = $totalSeconds % 60;

// Create a new DateInterval with the updated values
// Create a new DateInterval with the updated values
$endTime = new DateTime($date . ' ' . "$hrs:$min:$sec");
$weeklyHours[$date][$rotationIndex][1] = new DateInterval("PT{$addHrs}H{$addMin}M{$addSec}S"); // ::createFromDateString("$totalSeconds seconds")

// ---

// Get the original values from the DateInterval
$originalHours = $totalHours[$rotationIndex][1]->h;
$originalMinutes = $totalHours[$rotationIndex][1]->i;
$originalSeconds = $totalHours[$rotationIndex][1]->s;

// Add the new values
$newHours = $originalHours + $hrs;
$newMinutes = $originalMinutes + $min;
$newSeconds = $originalSeconds + $sec;

// Calculate any carryovers
$carryMinutes = floor($newSeconds / 60);
$newSeconds = $newSeconds % 60;

$carryHours = floor(($newMinutes + $carryMinutes) / 60);
$newMinutes = ($newMinutes + $carryMinutes) % 60;

$newHours += $carryHours;

$errors['TIME'] = 'Total hours time has been appended ' . $newHours . '+=' . $hrs . '  index: (' . $rotationIndex . ')';

// Create a new DateInterval
$totalHours[$rotationIndex][1] = new DateInterval("PT{$newHours}H{$newMinutes}M{$newSeconds}S");

// Increment DateInterval by one second
//$totalSeconds++;

            //$endTime = clone $firstRangeEnd;

            //$endTime->sub(new DateInterval("PT{$hrs}H{$min}M{$sec}S"));
            //echo 'endTime: ' . $endTime->format('H:i:s') . '  working_time: ' . $working_time->format('%H:%I:%S') . "<br />\n";
            continue;
          }
          if ($firstPeriodEnd >= $firstRangeStart && $firstPeriodEnd <= $firstRangeEnd) {
            // echo 'and this fits here ... ' . $firstPeriodEnd->format('H:i:s') . '>=' . $firstRangeStart->format('H:i:s') . " START  &amp;&amp; ";
            // echo $firstPeriodEnd->format('H:i:s')  . '<=' . $firstRangeEnd->format('H:i:s') . " END<br />\n";

// $firstPeriodEnd - $firstRangeStart == 3hrs


            $working_time = clone $weeklyHours[$date][$rotationIndex][1];

list($hrs, $min, $sec) = explode(':', $firstRangeStart->format('H:i:s'));

$endTime = clone $firstPeriodEnd;
$endTime->sub(new DateInterval("PT{$hrs}H{$min}M{$sec}S"));

   // $endTime->format('H:i:s') == 03:00:00

//append 3hrs to the working time

list($hrs, $min, $sec) = explode(':', $endTime->format('H:i:s'));

$dateInterval = new DateInterval("PT{$hrs}H{$min}M{$sec}S");

$totalSeconds = $dateInterval->s + $dateInterval->i * 60 + $dateInterval->h * 3600 + $dateInterval->d * 86400;

$workingSeconds = $working_time->s + $working_time->i * 60 + $working_time->h * 3600 + $working_time->d * 86400;

$totalSeconds = $totalSeconds + $workingSeconds;
$addHrs = floor($totalSeconds / 3600);
$addMin = floor(($totalSeconds % 3600) / 60);
$addSec = $totalSeconds % 60;

// Create a new DateInterval with the updated values
$weeklyHours[$date][$rotationIndex][1] = new DateInterval("PT{$addHrs}H{$addMin}M{$addSec}S"); // ::createFromDateString("$totalSeconds seconds")

// ---

// Get the original values from the DateInterval
$originalHours = $totalHours[$rotationIndex][1]->h;
$originalMinutes = $totalHours[$rotationIndex][1]->i;
$originalSeconds = $totalHours[$rotationIndex][1]->s;

// Add the new values
$newHours = $originalHours + $hrs;
$newMinutes = $originalMinutes + $min;
$newSeconds = $originalSeconds + $sec;

// Calculate any carryovers
$carryMinutes = floor($newSeconds / 60);
$newSeconds = $newSeconds % 60;

$carryHours = floor(($newMinutes + $carryMinutes) / 60);
$newMinutes = ($newMinutes + $carryMinutes) % 60;

$newHours += $carryHours;

// Create a new DateInterval
$totalHours[$rotationIndex][1] = new DateInterval("PT{$newHours}H{$newMinutes}M{$newSeconds}S");

// $working_time->format('%H:%I:%S') == 21:00:00
            continue;
          }

/*
        ["12:32:44": "00:00:06"],
        ["12:32:22": "00:00:06"],
        ["12:40:21": "00:00:59"],
*/
          // echo 'endTime: ' . $endTime->format('H:i:s') . '  working_time: ' . $working_time->format('%H:%I:%S') . "<br />\n";
        //$endTime->setTime('00', '00');
//var_dump('Idle Count: ' . count($idleTimes));


/*
          $firstRangeStart = new DateTime($date . ' ' . int_pad($range[1], 2) . ':00:00'); // !DateInterval('PT' . int_pad($range[1], 2) . 'H00M00S' );
          $firstRangeEnd = new DateTime($date . ' ' . int_pad($range[2], 2) . ':00:00'); // !DateInterval('PT' . int_pad($range[2], 2) . 'H00M00S' );


          if ($timestamp >= $firstRangeStart && $timestamp <= $firstRangeEnd) {

            //$it = DateTime::createFromFormat('Y-m-d H:i:s', $idleTime);

            if ($idleTime = new DateTime($date . ' ' . $idleTime)) { // DateInterval("PT{$hrs}H{$min}M{$sec}S")
            
              //list($hrs, $min, $sec) = explode(':', $idleTime);

              //var_dump($idleTime);

              //list($hrs, $min, $sec) = explode(':', $timestamp->format('H:i:s'));

              //$timestamp->add(new DateInterval("PT{$hrs}H{$min}M{$sec}S"));

              list($hrs, $min, $sec) = explode(':', $timestamp->format('H:i:s'));

              //if ($timestamp = new DateInterval("PT{$hrs}H{$min}M{$sec}S")) {

              $idleTime_init = $idleTime;

              $idleTime->add(new DateInterval("PT{$hrs}H{$min}M{$sec}S"));

              if ($idleTime <= $firstRangeEnd)
                //var_dump('idleTime: ' . $idleTime->format('H:i:s'));
              else {
              
              }

                //list($hrs, $min, $sec) = explode(':', $firstRangeEnd->format('H:i:s'));
                
                //$idleTime->sub("PT{$hrs}H{$min}M{$sec}S");
                
                

              //}


            }
          }
*/
        }
        
      
        }

        
      }

/*
  2023-09-18T06:00:00-21:00

  if the timestamp is the last one as apposed to one in a sequence
  
 Night  start : 00 end:06
 Morning  start : 06 end:12
  this fits here ... 21:00:00>=12:00:00 END
 Afternoon start : 12 end:18
  this fits here ... 21:00:00>=18:00:00 END   OR   and this fits here ... 21:00:00>=18:00:00 START
 Evening start : 18 end:22
  and this fits here ... 21:00:00>=18:00:00 START
*/

/*
        if ( $firstRangeStart >= $firstPeriodStart && $firstPeriodEnd >= $firstRangeEnd  ) {
          echo 'and here' . $firstRangeStart->format('H:i:s') . '>=' . $firstPeriodStart->format('H:i:s') . "<br />\n";
          continue;
        }
        && $firstRangeEnd <= $firstPeriodEnd) {
          echo 'and ';
        }
*/
        
        //}

      //dd();

      if (!empty($idleTimes))
        foreach ($idleTimes as $timestamp => $idleTime) {
          //var_dump('Idle Time!' . $timestamp);
          
          //dd($date);
          $timestamp = new DateTime($timestamp); // DateInterval("PT{$hrs}H{$min}M{$sec}S")

          $rotationIndex = null;

          // Get the current hour
          $currentHour = (int) $timestamp->format('G');

          if ($rotationIndex === null) //{
            $rotationIndex = $index - 1;
          else {
            $rotationIndex = ($rotationIndex < 0 ? 0 : ($rotationIndex > 3 ? 0 : $rotationIndex)) - 1;
          }

          foreach ($timeRanges as $index => $range) {
            if ($currentHour >= $range[1] && $currentHour < $range[2]) {
              $rotationIndex = ($index < 0 ? 0 : ($index > 3 ? 0 : $index)); // $index - 1
              break;
            }
          }
          
          if (preg_match('/^\d*\:\d*:\d*$/', $idleTime)) {
            list($hrs, $min, $sec) = explode(':', $idleTime);
          } else {
            continue;
          }
          
          $timestamp_add = clone $timestamp;
          $timestamp_add->add(new DateInterval("PT{$hrs}H{$min}M{$sec}S"));
          
          if ((int) $timestamp_add->format('G') < $range[2] && $timestamp_add > $timestamp) {
            //dd('Date: ' . $date . ' Idle: ' . "{$hrs}:{$min}:{$sec}" . '  Idle Time: ' . $timestamp_add->format('H:i:s') . '  Time: ' . $timestamp->format('H:i:s')  . '   Rotation: ' . $rotationIndex);
//var_dump('wtf index: ' . $rotationIndex . '  Time: ' . $timestamp->format('H:i:s'));

//$weeklyHours[$date][$rotationIndex][2];
// Get the original values from the DateInterval
$originalHours = $weeklyHours[$date][$rotationIndex][2]->h;
$originalMinutes = $weeklyHours[$date][$rotationIndex][2]->i;
$originalSeconds = $weeklyHours[$date][$rotationIndex][2]->s;

// Add the new values
$newHours = $originalHours + $hrs;
$newMinutes = $originalMinutes + $min;
$newSeconds = $originalSeconds + $sec;

// Calculate any carryovers
$carryMinutes = floor($newSeconds / 60);
$newSeconds = $newSeconds % 60;

$carryHours = floor(($newMinutes + $carryMinutes) / 60);
$newMinutes = ($newMinutes + $carryMinutes) % 60;

$newHours += $carryHours;

// Create a new DateInterval
$weeklyHours[$date][$rotationIndex][2] = new DateInterval("PT{$newHours}H{$newMinutes}M{$newSeconds}S");

//$totalHours[$rotationIndex][2];

// Get the original values from the DateInterval
$originalHours = $totalHours[$rotationIndex][2]->h;
$originalMinutes = $totalHours[$rotationIndex][2]->i;
$originalSeconds = $totalHours[$rotationIndex][2]->s;

// Add the new values
$newHours = $originalHours + $hrs;
$newMinutes = $originalMinutes + $min;
$newSeconds = $originalSeconds + $sec;

// Calculate any carryovers
$carryMinutes = floor($newSeconds / 60);
$newSeconds = $newSeconds % 60;

$carryHours = floor(($newMinutes + $carryMinutes) / 60);
$newMinutes = ($newMinutes + $carryMinutes) % 60;

$newHours += $carryHours;

// Create a new DateInterval
$totalHours[$rotationIndex][2] = new DateInterval("PT{$newHours}H{$newMinutes}M{$newSeconds}S");


//dd($rotationIndex);
/*            
            $weeklyHours[$date][$rotationIndex][2]
            
            
            How to append time to a DateInterval?
$weeklyHours = [
  $today->format('Y-m-d') => [
    ['Morning', new DateInterval('PT00H00M00S'), new DateInterval('PT00H00M00S')], // working | idleing
    ['Afternoon', new DateInterval('PT00H00M00S'), new DateInterval('PT00H00M00S')],
    ['Evening', new DateInterval('PT00H00M00S'), new DateInterval('PT00H00M00S')],
    ['Night', new DateInterval('PT00H00M00S'), new DateInterval('PT00H00M00S')]
  ]
];
*/

          //$idleTimes = null; //break;
        }
      }
  }
  //dd($totalHours);

/*
    $today->modify('+1 day');

    if($today->format('l') === 'Monday') {
        break; // Exit the loop when it's Monday again
    }

*/
/**/


        //  echo 'firstPeriodStart: ' . $firstPeriodStart->format('H:i:s') . 'firstPeriodEnd: ' . $firstPeriodEnd->format('H:i:s') . '   firstRangeEnd: ' . $firstRangeEnd->format('H:i:s') . "<br />\n";        
        
/*
        // Calculate working time for this range (based on your logic)
        $working_time_for_range = ... // Calculate working time for this range

        // Calculate idle time for this range (based on your logic)
        $idle_time_for_range = ... // Calculate idle time for this range

        // Add working time for this range to total working time
        $working_time->add($working_time_for_range);

        // Add idle time for this range to total idle time
        $idle_time->add($idle_time_for_range);
***
        foreach ($idleTimes as $timestamp => $idleTime) {

          list($hrs, $min, $sec) = explode(':', $timestamp);

          if ($timestamp = new DateTime($date . ' ' . $timestamp)) // DateInterval("PT{$hrs}H{$min}M{$sec}S")

          if ($timestamp >= $firstRangeStart && $timestamp <= $firstRangeEnd) {

            //$it = DateTime::createFromFormat('Y-m-d H:i:s', $idleTime);

            if ($idleTime = new DateTime($date . ' ' . $idleTime)) { // DateInterval("PT{$hrs}H{$min}M{$sec}S")
            
              //list($hrs, $min, $sec) = explode(':', $idleTime);

              //var_dump($idleTime);

              //list($hrs, $min, $sec) = explode(':', $timestamp->format('H:i:s'));

              //$timestamp->add(new DateInterval("PT{$hrs}H{$min}M{$sec}S"));

              list($hrs, $min, $sec) = explode(':', $timestamp->format('H:i:s'));

              //if ($timestamp = new DateInterval("PT{$hrs}H{$min}M{$sec}S")) {

              $idleTime_init = $idleTime;

              $idleTime->add(new DateInterval("PT{$hrs}H{$min}M{$sec}S"));

              if ($idleTime <= $firstRangeEnd)
                dd('idleTime: ' . $idleTime->format('H:i:s'));
              else {
              
              }

                //list($hrs, $min, $sec) = explode(':', $firstRangeEnd->format('H:i:s'));
                
                //$idleTime->sub("PT{$hrs}H{$min}M{$sec}S");
                
                

              //}


            }


            // How to "convert" idleTime, first add the $timestamp and
            // minus it from the firstRangeEnd  if its a possitive number,
            // than it gets appended to the idle time. Negative, cut it into 
            // piece where by if its possitive end, and append it to idle time.

              //dd('timestamp: ' . $timestamp->format('Y-m-d h:i:s') . '   idletime: ' . $idleTime->format('%H:%I:%S') ); // $timestamp = new DateTime($date . ' ' . $timestamp));

                    // Process each idle time within the date

                    // Compare $timestamp with the current time range and perform actions accordingly
                //if ($timestamp is within $range) { }
                        // Perform actions for this combination of date, time range, and idle time
                        // Adjust idle time to stay within range
                        // $idle_time = ... // Adjust idle time

//            var_dump($idleTimes);
            break; // Break out of the loop once a match is found
          }
        }
      }
  }
}
*/
//dd();


$Now = new DateTime(date('Y-m-d') . 'T' . date('H').':00:00', new DateTimeZone('-' . $timeRanges[4][2] . ':00')); // date('H') + 6 now

//dd($Now->format('Y-m-d H:i:s'));

$json = (!is_file(APP_PATH . APP_BASE['database'] . 'weekly-timesheet-' . date('Y-m') . '.json') ? 
  (!@touch(APP_PATH . APP_BASE['database'] . 'weekly-timesheet-' . date('Y-m') . '.json') ? 
    (!file_get_contents(APP_PATH . APP_BASE['database'] . 'weekly-timesheet-' . date('Y-m') . '.json', true) ? json_encode([$Now->format(DATE_RFC3339) => []]) : file_get_contents(APP_PATH . APP_BASE['database'] . 'weekly-timesheet-' . date('Y-m') . '.json', true)) :
    (!@file_put_contents(APP_PATH . APP_BASE['database'] . 'weekly-timesheet-' . date('Y-m') . '.json', $json = json_encode([$Now->format(DATE_RFC3339) => []]), LOCK_EX) ?: $json)
  ) :
  (!file_get_contents(APP_PATH . APP_BASE['database'] . 'weekly-timesheet-' . date('Y-m') . '.json', true) ? json_encode([$Now->format(DATE_RFC3339) => []]) : file_get_contents(APP_PATH . APP_BASE['database'] . 'weekly-timesheet-' . date('Y-m') . '.json', true))
);

//file_get_contents('database/weekly-timesheet-' . date('Y-m') . '.json', true) :  : (!@touch('timesheet.json') ? '' . json_encode([$Now->format(DATE_RFC3339) => []]), 'timesheet.json', LOCK_EX) : file_get_contents('timesheet.json', true)));

//die(var_dump($json));

$json_decode = json_decode($json, true);

switch ($_SERVER['REQUEST_METHOD']) {
  case 'POST':
    if (isset($_POST['idletime'])) { 
      $_POST['idletime']['time'] = trim($_POST['idletime']['time']);
      $_POST['idletime']['idle'] = (is_null($_POST['idletime']['idle']) ? NULL : trim($_POST['idletime']['idle']));

      if (!empty($json_decode))
        foreach($json_decode as $weekday_key => $weekday) {
          if (preg_match('/(' . /*\d+\-\d+\-\d+*/ date('Y-m-d') . ')T(\d+:\d+:\d+)-(\d+:\d+)/',$weekday_key,$matches)) {
            if ($matches[1] == $Now->format('Y-m-d') && !empty($weekday)) {
              foreach($weekday as $idletime_key => $idletime) {
                if (strtotime($_POST['idletime']['time']) >= strtotime($idletime_key) && strtotime($idletime) == null) { // $Now->format('H:i:s')
                  $json_decode[$weekday_key][$idletime_key] = $_POST['idletime']['idle']; // $Now->format('H:i:s')
                  continue 2;
                }
              }
              if ($json_decode[$weekday_key][$idletime_key] !== $_POST['idletime']['time']) // $Now->format('H:i:s')
                if (!isset($json_decode[$weekday_key][$_POST['idletime']['time']])) // $Now->format('H:i:s')
                  $json_decode[$weekday_key][$_POST['idletime']['time']] = (isset($_POST['idletime']['idle']) ? $_POST['idletime']['idle'] : NULL);  // $Now->format('H:i:s')
            } else {
              //$json_decode[$Now->format(DATE_RFC3339)] = [$_POST['idletime']['time'] => (isset($_POST['idletime']['idle']) && !is_null($_POST['idletime']['idle']) ? $_POST['idletime']['idle'] : NULL)];
              $json_decode[$weekday_key][$_POST['idletime']['time']] = (isset($_POST['idletime']['idle']) && !is_null($_POST['idletime']['idle']) ? $_POST['idletime']['idle'] : NULL); // $Now->format('H:i:s')
            }
          } else {
            $json_decode[$Now->format('Y-m-d\TH:i:sP')] = [date('H:i:s') => '']; // DATE_RFC3339
          }
        }
      else 
        $json_decode[] = [$Now->format(DATE_RFC3339) => []];

      $_POST['idletime'] = json_encode($_POST['idletime']); 
      
      file_put_contents(APP_PATH . APP_BASE['database'] . 'weekly-timesheet-' . date("Y-m") . '.json', json_encode($json_decode), LOCK_EX);
      
      //Shutdown::setEnabled(false)->setShutdownMessage()->shutdown(); 
      Shutdown::setEnabled(false)->setShutdownMessage(function() use($json_decode) {
        return json_encode($json_decode);
      })->shutdown();
      
      //die(json_encode($json_decode));
    }

    //Shutdown::setEnabled(false)->shutdown(json_encode($json_decode));// $_POST['idletime']
    break;
  case 'GET':
    if (!is_file(APP_PATH . APP_BASE['database'] . 'weekly-timesheet-' . date("Y-m") . '.json'))
      file_put_contents(APP_PATH . APP_BASE['database'] . 'weekly-timesheet-' . date("Y-m") . '.json', json_encode([$Now->format('Y-m-d\TH:i:sP') => []]), LOCK_EX);
    //die(); // $Now->format('H:i:s') => null
      break;
}

ob_start(); ?>

/* Styles for the absolute div */
#app_timesheet-container {
    position: absolute;
    top: 10%;
    //bottom: 60px;
    left: 50%;
    transform: translateX(-50%);
    width: auto;
    height: 500px;
    background-color: rgb(255, 255, 255);
    color: black;
    /* text-align: center; */
    padding: 10px;
    z-index: 1;
}

input {
   border: 1px solid #000;
}

td, tr {
  border: none;
}


<?php $appTimesheet['style'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

  <div id="app_timesheet-container" class="<?= (APP_SELF == __FILE__ || isset($_GET['app']) && $_GET['app'] == 'timesheet' ? 'selected' : '') ?>" style="display: none; <?= /* */ null; ?>border: 1px solid #000; width: 800px;">
    <div class="header ui-widget-header" style=" text-align: center;">
      <div style="display: inline-block;">Weekly Time Sheet</div>
      <div style="display: inline; float: right; text-align: center;">[<a style="cursor: pointer; font-size: 13px;" onclick="document.getElementById('app_timesheet-container').style.display='none';">X</a>]</div> 
    </div>
<div style=" overflow-x: scroll;">
    <form style="display: inline;" action="<?= APP_URL_BASE . basename(APP_SELF) . '?' . http_build_query(APP_QUERY + array( 'app' => 'php')) . (defined('APP_ENV') && APP_ENV == 'development' ? '#!' : '') /*  $c_or_p . '=' . (empty($_GET[$c_or_p]) ? '' : $$c_or_p->name) . '&amp;app=composer' */ ?>" method="GET">
      <div style="font-size: 12px;">
        <div style="float: left;">NAME OF EMPLOYEE<br />
        <input type="text" style="width: 300px;" />
        </div><div style="float: right; text-align: right;">FOR WEEK ENDING<br />
        <input type="text" style="width: 300px;" />
        </div>
        <div style="clear: both;"></div>
        <div style="float: left;">DEPARTMENT<br />
        <input type="text" style="width: 300px;" /></div><div style="float: right; text-align: right;">EXEMPTIONS<br />
        <input type="text" style="width: 300px;" /></div>
      </div>
      <div style="clear: both;"></div>
<?php
$today = new DateTime(date('Y-m-d H:i:s'));

// Store the current time
$current_time = $today->format('H:i:s');

// Check if today is Monday
if ($today->format('N') != 1)
    // Get the last Monday    
    $today->modify('last monday'); //$today->modify('+7 days'); // Subtract 7 days
else 
  $today->modify('+' . $today->format('N') - 1 . ' days');

// Set the time back to the current time
$today->setTime(...explode(':', $current_time));

$currentDate = new DateTime();

// $currentDate->setTime('06', '30');

echo $today->format('Y-m-d H:i:s');

while(true) { // $weekday <= 6

//echo $today->format('Y-m-d'); // Output will be the date of the last Monday
$dayOfWeek = strtoupper($today->format('l'));

// Get the current hour
$currentHour = $currentDate->format('G');

// Determine the rotation based on the current hour
$rotationIndex = null;

// If $rotationIndex is still null, use the last rotation (Night - Morning)
if ($rotationIndex === null) {
    $rotationIndex = count($timeRanges) - 1;
} else {
    $rotationIndex = ($rotationIndex < 0 ? 0 : ($rotationIndex >= 4 ? 0 : $rotationIndex)); //  || 
    //dd($rotationIndex); // $rotationIndex -= 1;
}


//dd($rotationIndex);

foreach ($timeRanges as $index => $range) {
    if ($currentHour >= $range[1] && $currentHour < $range[2]) {
        $rotationIndex = ($index < 0 ? 0 : ($index >= 4 ? 0 : $index));
        break;
    }
}

// Define the time periods for each rotation
$timePeriods = [
    ['NIGHT', 'MORNING'],
    ['MORNING', 'AFTERNOON'],
    ['AFTERNOON', 'EVENING'],
    ['EVENING', 'NIGHT'],
];

// Use the selected rotation index to determine the time periods
list($firstPeriod, $secondPeriod) = $timePeriods[$rotationIndex];

$currentTime = $currentDate;

$periodTimes = [
    'MORNING' => ['start' => '06:00:00', 'end' => '12:00:00'],
    'AFTERNOON' => ['start' => '12:00:00', 'end' => '18:00:00'],
    'EVENING' => ['start' => '18:00:00', 'end' => '22:00:00'],
    'NIGHT' => ['start' => '22:00:00', 'end' => '23:59:59'],
];

$firstPeriodStart = new DateTime($periodTimes[$firstPeriod]['start']);
$firstPeriodEnd = new DateTime($periodTimes[$firstPeriod]['end']);
$secondPeriodStart = new DateTime($periodTimes[$secondPeriod]['start']);
$secondPeriodEnd = new DateTime($periodTimes[$secondPeriod]['end']);

// Determine the background color for the current time period
$backgroundColorFirst = ($firstPeriod === $timePeriods[$rotationIndex][0]) ? '#00FF00' : 'white';
$backgroundColorSecond = ($secondPeriod === $timePeriods[$rotationIndex][1]) ? '#FF0000' : 'white';

// Determine if "OUT" should be highlighted in red
$highlightOut = false;
if ($firstPeriodStart <= $currentTime && $currentTime <= $firstPeriodEnd) {
    // $highlightOut = ($currentTime->format('i') >= 1 && $currentTime->format('i') <= 59);
    $remainingTime = $firstPeriodEnd->getTimestamp() - $currentTime->getTimestamp();
    $highlightOut = ($remainingTime <= 3599); // 3599 seconds = 59 minutes and 59 seconds
}

/* Debug statements
echo "First Period Start: " . $firstPeriodStart->format('Y-m-d H:i:s') . "<br />\n";
echo "First Period End: " . $firstPeriodEnd->format('Y-m-d H:i:s') . "<br />\n";
echo "Second Period Start: " . $secondPeriodStart->format('Y-m-d H:i:s') . "<br />\n";
echo "Second Period End: " . $secondPeriodEnd->format('Y-m-d H:i:s') . "<br />\n";
echo "Current Time: " . $currentTime->format('Y-m-d H:i:s') . "<br />\n";
echo "Highlight Out: " . ($highlightOut ? 'true' : 'false') . "<br />\n";
*/
//dd();
//if (($firstPeriodStart <= $currentTime && $currentTime <= $firstPeriodEnd) || ($secondPeriodStart <= $currentTime && $currentTime <= $secondPeriodEnd)) {
//    $highlightOut = true;
//}

// Set background color for the highlighted column
if (!$highlightOut) {
    $backgroundColorFirst = '#00FF00'; // Green
    $backgroundColorSecond = 'white';
} else {
    $backgroundColorFirst = 'white';
    $backgroundColorSecond = '#FF0000'; // Red
}
/**/
  if ($today->format('N') == 1)
echo <<<END
      <div style="display: flex; align-items: center; justify-content: center; font-size: 14px; width: 775px; border: 1px solid #000;">
        <div style="display: inline-block; text-align: left; width: 145px; padding: 10px;">DAYS OF WEEK</div>

        <div style="display: inline-block; text-align: center; width: 175px;">
          <div style="background-color: #E6EAF6; border: 1px solid #000; padding: 10px; font-weight: bold;">$firstPeriod</div>
          <div style="display: inline-block; background-color: $backgroundColorFirst; border: 1px solid #000; text-align: center; padding-top: 7px; margin: -2px; width: 50%; height: 40px; box-sizing: border-box;" title="{$firstPeriodStart->format('h:i:s')} - {$firstPeriodEnd->format('h:i:s')}">Time</div>
          <div style="display: inline-block; background-color: $backgroundColorSecond; border: 1px solid #000; text-align: center; padding-top: 7px; margin: -2px; width: 50%; height: 40px; box-sizing: border-box;" title="{$firstPeriodStart->format('h:i:s')} - {$firstPeriodEnd->format('h:i:s')}">Idle</div>
        </div>        
        <div style="display: inline-block; text-align: center; width: 175px;">
          <div style="background-color: #E6EAF6; border: 1px solid #000; padding: 10px; font-weight: bold;">$secondPeriod</div>
          <div style="display: inline-block; border: 1px solid #000; text-align: center; padding-top: 7px; margin: -2px; width: 50%; height: 40px; box-sizing: border-box;" title="{$secondPeriodStart->format('h:i:s')} - {$secondPeriodEnd->format('h:i:s')}">Time</div>
          <div style="display: inline-block; border: 1px solid #000; text-align: center; padding-top: 7px; margin: -2px; width: 50%; height: 40px; box-sizing: border-box;" title="{$secondPeriodStart->format('h:i:s')} - {$secondPeriodEnd->format('h:i:s')}">Idle</div>
        </div>


        <div style="display: inline-block; text-align: center; width: 175px;">
          <div style="background-color: #E6EAF6; border: 1px solid #000; padding: 10px; font-weight: bold;">OVERTIME</div>
          <div style="display: inline-block; border: 1px solid #000; text-align: center; padding-top: 7px; margin: -2px; width: 50%; height: 40px; box-sizing: border-box;">Time</div>
          <div style="display: inline-block; border: 1px solid #000; text-align: center; padding-top: 7px; margin: -2px; width: 50%; height: 40px; box-sizing: border-box;">Idle</div>
        </div>
        <div style="display: inline-block; text-align: center; width: 300px;">
          <div style="background-color: #E6EAF6; border: 1px solid #000; padding: 10px; font-weight: bold;">FOR OFFICE USE ONLY</div>
          <div style="display: inline-block; border: 1px solid #000; font-size: 12px; text-align: center; margin: -2px; width: 50%; height: 40px; box-sizing: border-box;">REGULAR<br />HOURS</div>
          <div style="display: inline-block; border: 1px solid #000; font-size: 12px; text-align: center; margin: -2px; width: 50%; height: 40px; box-sizing: border-box;">OVERTIME<br />HOURS</div>
        </div>

      </div>
      <div style="height: 265px; overflow: scroll;">

END;

$defaultBackgroundColor = ($today->format('Y-m-d') === $currentDate->format('Y-m-d') ? '#FFFF00' : '#FFFFFF' );

//dd($highlightOut);

if (!$highlightOut) {
    $backgroundColorThird = '#BFFF3F'; // green
    $backgroundColorFourth = $defaultBackgroundColor;
} else {
    $backgroundColorThird = $defaultBackgroundColor; // orange;
    $backgroundColorFourth = '#FFBF3F';
}

//$rotationIndex -= 1;

if (!isset($weeklyHours[$today->format('Y-m-d')])) {
  echo <<<END
      <div style="font-size: 14px; width: 775px; border: 1px solid #000; background-color: $defaultBackgroundColor;">
        <div style="display: inline-block; text-align: left; width: 120px;">
          <div style="border: 1px solid #000; padding: 10px;" title="{$today->format('Y-m-d')}">{$dayOfWeek}</div>
        </div>
        <div style="display: inline-block; text-align: center; width: 130px;">
          <div style="display: inline-block; background-color: $backgroundColorThird; border-right: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px; height: 100%; width: 50%; box-sizing: border-box;">0.0</div>
          <div style="display: inline-block; background-color: $backgroundColorFourth; border-left: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px; height: 100%; width: 50%; box-sizing: border-box;">0:0</div>
        </div>
        <div style="display: inline-block; text-align: center; width: 140px;">
          <div style="display: inline-block; border-left: 1px solid #000; border-right: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px; height: 100%; width: 50%; box-sizing: border-box;">0.0</div>
          <div style="display: inline-block; border-left: 1px solid #000; border-right: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px; height: 100%; width: 50%; box-sizing: border-box;">0:0</div>
        </div>
        <div style="display: inline-block; text-align: center; width: 130px;">
          <div style="display: inline-block; border-right: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px; height: 100%; width: 50%; box-sizing: border-box;">0</div>
          <div style="display: inline-block; border-left: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px; height: 100%; width: 50%; box-sizing: border-box;">0</div>
        </div>
        <div style="display: inline-block; text-align: center; width: 238px;">
          <div style="display: inline-block; border-left: 1px solid #000; border-right: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px; height: 100%; width: 50%; box-sizing: border-box;">0</div>
          <div style="display: inline-block; border-left: 1px solid #000; border-right: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px; height: 100%; width: 50%; box-sizing: border-box;">0</div>
        </div>
      </div>
END;
} else {
  $date = $today->format('Y-m-d');
  echo <<<END
      <div style="font-size: 14px; width: 775px; border: 1px solid #000; background-color: $defaultBackgroundColor;">
        <div style="display: inline-block; text-align: left; width: 120px;">
          <div style="border: 1px solid #000; padding: 10px;" title="{$today->format('Y-m-d')}">{$dayOfWeek}</div>
        </div>
        <div style="display: inline-block; text-align: center; width: 130px;">
          <div style="display: inline-block; background-color: $backgroundColorThird; border-right: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px; height: 100%; width: 50%; box-sizing: border-box;">
END;
echo $weeklyHours[$date][$rotationIndex][1]->h . '.' . $weeklyHours[$date][$rotationIndex][1]->i;
echo <<<END
</div>
          <div style="display: inline-block; background-color: $backgroundColorFourth; border-left: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px; height: 100%; width: 50%; box-sizing: border-box;">
END;
echo $weeklyHours[$date][$rotationIndex][2]->h . ':' . $weeklyHours[$date][$rotationIndex][2]->i . ':' . $weeklyHours[$date][$rotationIndex][2]->s;
echo <<<END
</div>
        </div>
        <div style="display: inline-block; text-align: center; width: 140px;">
          <div style="display: inline-block; border-left: 1px solid #000; border-right: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px; height: 100%; width: 50%; box-sizing: border-box;">
END;

$rotationNextIdx = ($rotationIndex + 1 < 0 ? 0 : ($rotationIndex + 1 > 3 ? 0 : $rotationIndex)) + 1;

echo $weeklyHours[$date][$rotationNextIdx][1]->h . '.' . $weeklyHours[$date][$rotationNextIdx][1]->i;
echo <<<END
</div>
          <div style="display: inline-block; border-left: 1px solid #000; border-right: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px; height: 100%; width: 50%; box-sizing: border-box;">
END;
echo $weeklyHours[$date][$rotationNextIdx][2]->h . ':' . $weeklyHours[$date][$rotationNextIdx][2]->i . ':' . $weeklyHours[$date][$rotationNextIdx][2]->s;
echo <<<END
</div>
        </div>
        <div style="display: inline-block; text-align: center; width: 130px;">
          <div style="display: inline-block; border-right: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px; height: 100%; width: 50%; box-sizing: border-box;">0</div>
          <div style="display: inline-block; border-left: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px; height: 100%; width: 50%; box-sizing: border-box;">0</div>
        </div>
        <div style="display: inline-block; text-align: center; width: 238px;">
          <div style="display: inline-block; border-left: 1px solid #000; border-right: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px; height: 100%; width: 50%; box-sizing: border-box;">
END;
echo $weeklyHours[$date][0][1]->h + $weeklyHours[$date][1][1]->h + $weeklyHours[$date][2][1]->h + $weeklyHours[$date][3][1]->h;
echo <<<END
</div>
          <div style="display: inline-block; border-left: 1px solid #000; border-right: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px; height: 100%; width: 50%; box-sizing: border-box;">0</div>
        </div>
      </div>

END;
}

//dd($totalHours[$rotationIndex][2]);


  if ($today->format('N') == 7) {//   $totalHours
  echo <<<END
      </div>
      <div style="font-size: 14px; width: 775px; border: 1px solid #000; background-color: #E6EAF6; font-weight: bold;">
        <div style="display: inline-block; text-align: left; width: 120px;">
          <div style="border: 1px solid #000; padding: 10px; ">TOTAL HOURS</div>
        </div>
        <div style="display: inline-block; text-align: center; width: 130px;">
          <div style="display: inline-block; border-right: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px; height: 100%; width: 50%; box-sizing: border-box;">
END;
echo $totalHours[$rotationIndex][1]->h;
echo <<<END
</div>
          <div style="display: inline-block; border-left: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px;  height: 100%; width: 50%; box-sizing: border-box;">
END;
echo $totalHours[$rotationIndex][2]->h . ':' . $totalHours[$rotationIndex][2]->i . ':' . $totalHours[$rotationIndex][2]->s;
echo <<<END
</div>
        </div>
        <div style="display: inline-block; text-align: center; width: 140px;">
          <div style="display: inline-block; border-left: 1px solid #000; border-right: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px; height: 100%; width: 50%; box-sizing: border-box;">

END;
$rotationNextIdx = ($rotationIndex + 1 < 0 ? 0 : ($rotationIndex + 1 > 3 ? 0 : $rotationIndex)) + 1;
echo $totalHours[$rotationNextIdx][1]->h;
echo <<<END
</div>
          <div style="display: inline-block; border-left: 1px solid #000; border-right: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px; height: 100%; width: 50%; box-sizing: border-box;">
END;
echo $totalHours[$rotationNextIdx][2]->h . ':' . $totalHours[$rotationNextIdx][2]->i . ':' . $totalHours[$rotationNextIdx][2]->s;
echo <<<END
</div>
        </div>
        <div style="display: inline-block; text-align: center; width: 130px;">
          <div style="display: inline-block; border-right: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px; height: 100%; width: 50%; box-sizing: border-box;">0</div>
          <div style="display: inline-block; border-left: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px; height: 100%; width: 50%; box-sizing: border-box;">0</div>
        </div>
        <div style="display: inline-block; text-align: center; width: 238px;">
          <div style="display: inline-block; border-left: 1px solid #000; border-right: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px; height: 100%; width: 50%; box-sizing: border-box;">
END;
echo $totalHours[0][1]->h + $totalHours[1][1]->h + $totalHours[2][1]->h + $totalHours[3][1]->h;
echo <<<END
</div>
          <div style="display: inline-block; border-left: 1px solid #000; border-right: 1px solid #000; text-align: center; padding: 10px; margin: 0 -2px; height: 100%; width: 50%; box-sizing: border-box;">0</div>
        </div>
      </div>
      <div style="clear: both;"></div>

    </form>
  </div>
END;
    }
    $today->modify('+1 day');

    if($today->format('l') === 'Monday') {
        break; // Exit the loop when it's Monday again
    }
}


?>

      <!-- <pre id="ace-editor" class="ace_editor"></pre> -->

  </div>

<?php $appTimesheet['body'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

    var idleTime = -1;
    var intervalID = [0, 1];
    var isIntervalActive = false;
    var occupiedTime = 0;
    var date = new Date();
    var clock = new Date().toLocaleTimeString('en-US', { hour: '2-digit', hour12: true, minute: '2-digit', second: '2-digit'});
    var time = new Date().toLocaleTimeString('en-US', { hour: '2-digit', hour12: true, minute: '2-digit', second: '2-digit'});
    var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sept", "Oct", "Nov", "Dec"];
    var month = months[date.getMonth()];
    
    snd.loop = true;

function play_beep() {
    snd.play();
}

function stop_beep() {
    snd.pause();
}

function clockIncrement() {
    var time = new Date().toLocaleTimeString('en-US', { hour: '2-digit', hour12: true, minute: '2-digit', second: '2-digit'});
    var clocktime = document.getElementById('clockTime');
    var weekday=new Array(7);
    weekday[0]="Sun,";
    weekday[1]="Mon,";
    weekday[2]="Tues,";
    weekday[3]="Wed,";
    weekday[4]="Thurs,";
    weekday[5]="Fri,";
    weekday[6]="Sat,";
    
    
    clocktime.innerHTML = '<i style="color: green;">' + weekday[date.getDay()] + '  ' + time + '  ' + month + ' ' + date.getDate() + ' ' + date.getFullYear()  + '</i>';
}

function startInterval() {
    if (!isIntervalActive) {
        intervalID[1] = setInterval(timerIncrement, 1000);
        isIntervalActive = true;
        console.log('Interval started.');
    } else {
        console.log('Interval is already active.');
    }
}

function stopInterval() {
    if (isIntervalActive) {
        clearInterval(intervalID[1]);
        isIntervalActive = false;
        console.log('Interval stopped.');
    } else {
        console.log('Interval is not active.');
    }
}

    $(document).ready(function () {
    
        intervalID[0] = setInterval(clockIncrement, 1000); // 1 second
    
        $('#ts-status-light').click(function() {
            // Pause the interval when the button is clicked
            
            if (isIntervalActive) {
                stopInterval();
                $("#ts-status-light").attr('src', 'resources/images/timesheet-light-R.gif');
                $("#idleTime").html('<i style="color: red;">[Stopped] at: ' + toTime(occupiedTime)['time'] + '</i>');
            } else {
                startInterval();
                $("#ts-status-light").attr('src', 'resources/images/timesheet-light-GG.gif');
            }
            //console.log('Interval paused.');
        });

        // Increment the idle time counter every minute.
        startInterval(); // intervalID = setInterval(timerIncrement, 1000); // 1 second
        time = date.toLocaleTimeString('en-US', { hour: '2-digit', hour12: false,  minute: '2-digit', second: '2-digit'});

        // Zero the idle timer on mouse movement.
        $(this).mousemove(function (e) {
            if (idleTime >= 11)
              idlePenalty({idletime: {time:time,idle:toTime(idleTime)['time']}});
            idleTime = -1;
        });
        $(this).keypress(function (e) {
            if (idleTime >= 11)
              idlePenalty({idletime: {time:time,idle:toTime(idleTime)['time']}});
            idleTime = -1;
        });
        //$("#idleTime").text(month + ' ' + date.getDate() + ' ' + date.getFullYear() + '  '  + time);
    });

    /* https://stackoverflow.com/questions/69264746/how-to-convert-seconds-to-time-in-javascript */
    function toTime(duration) {
        //if (duration < 0) return "-" + toTime(-duration);
        //return new Date(duration * 1000).toISOString().substr(11, 8);
      // Hours, minutes and seconds
      var time = new Array();
      var hrs = ~~(duration / 3600);
      var mins = ~~((duration % 3600) / 60);
      var secs = ~~duration % 60;
      let ret = String(hrs).padStart(2, '0') + ":" + String(mins).padStart(2, '0') + ":" + String(secs).padStart(2, '0');
      time['hrs'] = hrs;
      time['mins'] = mins;
      time['secs'] = secs;
      time['time'] = ret;
      return time;
    }

    function idlePenalty(idletimeobj) {
      //console.log(idletimeobj);
      snd.play();
      var jsonFile;
      date = new Date();
      time = date.toLocaleTimeString('en-US', { hour: '2-digit', hour12: false,  minute: '2-digit', second: '2-digit'}); // .replace(/AM|PM/,'')
      console.log("File Recorded - Time: " + idleTime);
      $.ajax({
        url: '<?= (is_dir($path = APP_PATH . APP_BASE['public']) && getcwd() == realpath($path) ? '1.'.APP_BASE['public'] : '' ) . basename(__FILE__) . '' ?>',
        type: 'POST',
        data: idletimeobj, // { idletime: { time: time, idle: toTime(idleTime)['time'], note: "" } }
        dataType: 'json',
        success: function (msg) {
          console.log(msg);
          $.getJSON("database/weekly-timesheet-<?= date('Y-m'); ?>.json", function(json_decode) {
            var count_idle = 0;
            console.log(json_decode); // this will show the info it in console
            //json_decode = //JSON.parse(JSON.stringify(json));
            Object.keys(json_decode).forEach(key=>{ Object.keys(json_decode[key]).forEach(key1=>{ count_idle += 1; }); });
        
            console.log(count_idle);
        
            $("#stats").html('Idle: [' + count_idle + '] ' + '&nbsp;&nbsp;' + '' + ' <span style="color: red;">01:00:00');

            function myFunction(item, index, arr) {
              console.log(item);
            } 
          });
          // fetch("database/weekly-timesheet-<?= date('Y-m'); ?>.json").then(res => res.json()).then(data => jsonFile = JSON.parse(data));
        },
        error: function (jqXHR, textStatus) {
          console.log(jqXHR.responseText);
          let responseText = jQuery.parseJSON(jqXHR.responseText);
          console.log(responseText);
        }
      });

    }

    function timerIncrement() {
      //snd.play();

         // for now
        date = new Date();
        time = date.toLocaleTimeString('en-US', { hour: '2-digit', hour12: true, minute: '2-digit', second: '2-digit'});
        idleTime = idleTime + 1; 
        idleDateTime = new Date(idleTime * 1000).toISOString().substr(11, 8);
        
        console.log(((idleTime >= 1) ? 'Idle: ' + idleTime + '   ' : '') + 'Work: ' + occupiedTime);
        
        if (idleTime <= 0) {
          $("#ts-status-light").attr('src', 'resources/images/timesheet-light-R.gif');
        }
        
        if (idleTime > 1) { // 1 second
            //window.location.reload();
          $("#idleTime").html('<i style="color: blue;">[Idling] for: ' + toTime(occupiedTime)['time'] + '</i>');

          if (idleTime >= 10) { // 60 second
          
            $("#ts-status-light").attr('src', 'resources/images/timesheet-light-G.gif');

             try {
    // Attempt to play the media element
      snd.play();
} catch (error) {
    // Check if the error is a DOMException
    if (error instanceof DOMException && error.name === 'NotAllowedError') {
        // Handle the error (e.g., show a message to the user)
        console.error('The play method is not allowed by the user agent or the platform.');
    } else {
        // If it's a different type of error, rethrow it
        throw error;
    }
}
             
             
             
             //snd.pause();
             
             $("#ts-status-light").attr('src', 'resources/images/timesheet-light-Y.gif');
             $("#idleTime").html('<i style="color: blue;">[Idled] for: '+ toTime(idleTime)['hrs'] + 'h ' + toTime(idleTime)['mins'] + 'm ' + toTime(idleTime)['secs'] + 's ' + '</i>');

             
             time = date.toLocaleTimeString('en-US', { hour: '2-digit', hour12: false, minute: '2-digit', second: '2-digit'});
             if (idleTime == 10)
               idlePenalty({idletime: {time:time,idle:null}}); // toTime(idleTime)['time']
               
          } else {
            snd.pause();
            $("#idleTime").html('<i style="color: blue;">[Idleing] for: '+ toTime(occupiedTime)['time'] + '</i>');
            $("#ts-status-light").attr('src', 'resources/images/timesheet-light-G.gif');
            occupiedTime = occupiedTime + 1;
          }
        } else {
          snd.pause();
          $("#idleTime").html('<i style="color: green;">Working: '+ toTime(occupiedTime)['time'] + '</i>');
            $("#ts-status-light").attr('src', 'resources/images/timesheet-light-GG.gif');
          occupiedTime = occupiedTime + 1;
        }

    }
<?php $appTimesheet['script'] = ob_get_contents();
ob_end_clean();

ob_start(); ?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" />

<?php
// (check_http_200('https://cdn.tailwindcss.com') ? 'https://cdn.tailwindcss.com' : APP_WWW . 'resources/js/tailwindcss-3.3.5.js')?
is_dir($path = APP_PATH . APP_BASE['resources'] . 'js/') or mkdir($path, 0755, true);
if (is_file($path . 'tailwindcss-3.3.5.js')) {
  if (ceil(abs((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',strtotime('+5 days',filemtime($path . 'tailwindcss-3.3.5.js'))))) / 86400)) <= 0 ) {
    $url = 'https://cdn.tailwindcss.com';
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    if (!empty($js = curl_exec($handle))) 
      file_put_contents($path . 'tailwindcss-3.3.5.js', $js) or $errors['JS-TAILWIND'] = $url . ' returned empty.';
  }
} else {
  $url = 'https://cdn.tailwindcss.com';
  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

  if (!empty($js = curl_exec($handle))) 
    file_put_contents($path . 'tailwindcss-3.3.5.js', $js) or $errors['JS-TAILWIND'] = $url . ' returned empty.';
}
?>

  <script src="<?= 'resources/js/tailwindcss-3.3.5.js' ?? $url ?>"></script>

<style type="text/tailwindcss">
<?= $appTimesheet['style']; ?>
</style>
</head>
<body>
<?= $appTimesheet['body']; ?>

  <!-- https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js -->
  <script src="//code.jquery.com/jquery-1.12.4.js"></script>
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <!-- <script src="resources/js/jquery/jquery.min.js"></script> -->
  
  <script src="resources/js/play_sound.js"></script> 
<script>
<?= $appTimesheet['script']; ?>
</script>
</body>
</html>
<?php $appTimesheet['html'] = ob_get_contents(); 
ob_end_clean();

//check if file is included or accessed directly
if (__FILE__ == get_required_files()[0] || in_array(__FILE__, get_required_files()) && isset($_GET['app']) && $_GET['app'] == 'git' && APP_DEBUG)
  die($appTimesheet['html']);