<!DOCTYPE>
<html>
<head>
</head>

<body>
<!--
pain_score TINYINT NOT NULL,
input_method VARCHAR(32) NOT NULL DEFAULT 'button',
spoken_text VARCHAR(50) NULL,
server_recorded_at DATETIME NOT NULL

POST /api/algometry/pain-score
-->


<button onclick="startVoice()">Speak pain score</button>
<div id="result"></div>

<script>
function startVoice() {
    const SpeechRecognition =
        window.SpeechRecognition || window.webkitSpeechRecognition;

    if (!SpeechRecognition) {
        document.getElementById('result').innerText =
            'Voice input is not supported on this browser.';
        return;
    }

    const recognition = new SpeechRecognition();
    recognition.lang = 'en-US';
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;

    recognition.onresult = function (event) {
        const spoken = event.results[0][0].transcript.toLowerCase();

        const map = {
            'one': 1,
            'two': 2,
            'three': 3,
            'four': 4,
            'five': 5,
            'six': 6,
            'seven': 7,
            'eight': 8,
            'nine': 9,
            'ten': 10
        };

        let score = null;

        if (map[spoken]) {
            score = map[spoken];
        } else {
            const number = spoken.match(/\b([1-9]|10)\b/);
            if (number) score = parseInt(number[1], 10);
        }

        if (!score) {
            document.getElementById('result').innerText =
                'Could not understand. Please try again.';
            return;
        }

        document.getElementById('result').innerText =
            'Pain score recorded: ' + score;

        savePainScore(score, spoken);
    };

    recognition.start();
}

function savePainScore(score, spokenText) {
    fetch('/api/algometry/pain-score', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            session_id: 123,
            probe: 'A',
            pain_score: score,
            input_method: 'voice',
            spoken_text: spokenText
        })
    });
}
</script>
</body>
</html>