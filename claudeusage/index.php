<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claude Usage & Limits Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
            padding: 15px;
            color: #e2e8f0;
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
        }

        h1 {
            color: #f1f5f9;
            text-align: center;
            margin-bottom: 10px;
            font-size: 2em;
            text-shadow: 0 0 20px rgba(56, 189, 248, 0.3);
        }

        @media (min-width: 768px) {
            h1 {
                font-size: 2.5em;
            }
            body {
                padding: 20px;
            }
        }

        .subscription-badge {
            text-align: center;
            margin-bottom: 20px;
        }

        .subscription-badge span {
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85em;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3);
        }

        @media (min-width: 768px) {
            .subscription-badge span {
                font-size: 0.9em;
            }
        }

        .api-status {
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background: rgba(15, 23, 42, 0.6);
            border-radius: 8px;
            font-size: 0.85em;
            color: #94a3b8;
            border: 1px solid rgba(56, 189, 248, 0.2);
        }

        .api-status.live {
            color: #4ade80;
            border-color: rgba(74, 222, 128, 0.3);
        }

        .time-filter {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .time-filter button {
            background: rgba(30, 41, 59, 0.8);
            color: #cbd5e1;
            border: 2px solid rgba(56, 189, 248, 0.3);
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.85em;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        @media (min-width: 768px) {
            .time-filter {
                gap: 10px;
            }
            .time-filter button {
                padding: 10px 25px;
                font-size: 0.95em;
            }
        }

        .time-filter button:hover {
            background: rgba(30, 41, 59, 1);
            border-color: rgba(56, 189, 248, 0.6);
            transform: translateY(-2px);
        }

        .time-filter button.active {
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
            color: white;
            border-color: #0ea5e9;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.4);
        }

        .limit-cards {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }

        @media (min-width: 640px) {
            .limit-cards {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }
        }

        .limit-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.9) 100%);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(56, 189, 248, 0.2);
        }

        @media (min-width: 768px) {
            .limit-card {
                padding: 25px;
            }
        }

        .limit-card h3 {
            color: #94a3b8;
            font-size: 0.9em;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        @media (min-width: 768px) {
            .limit-card h3 {
                font-size: 1em;
            }
        }

        .progress-bar {
            width: 100%;
            height: 30px;
            background: rgba(15, 23, 42, 0.6);
            border-radius: 15px;
            overflow: visible;
            margin-bottom: 10px;
            position: relative;
            border: 1px solid rgba(56, 189, 248, 0.2);
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #0ea5e9 0%, #2563eb 100%);
            transition: width 0.5s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.85em;
            box-shadow: 0 0 20px rgba(14, 165, 233, 0.5);
            border-radius: 15px;
            position: relative;
            min-width: 0;
        }

        .progress-fill::after {
            content: attr(data-percent);
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-weight: bold;
            font-size: 0.85em;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
            white-space: nowrap;
            pointer-events: none;
        }

        .progress-fill[data-percent="0%"]::after {
            color: #94a3b8;
            left: 20px;
            transform: translateY(-50%);
        }

        .progress-fill.warning {
            background: linear-gradient(90deg, #f59e0b 0%, #ef4444 100%);
            box-shadow: 0 0 20px rgba(245, 158, 11, 0.5);
        }

        .progress-fill.danger {
            background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.5);
        }

        .limit-details {
            display: flex;
            justify-content: space-between;
            font-size: 0.85em;
            color: #94a3b8;
            margin-top: 10px;
            flex-wrap: wrap;
            gap: 5px;
        }

        @media (min-width: 768px) {
            .limit-details {
                font-size: 0.9em;
            }
        }

        .limit-details .used {
            font-weight: 600;
            color: #38bdf8;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 25px;
        }

        @media (min-width: 640px) {
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 15px;
            }
        }

        @media (min-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.9) 100%);
            border-radius: 10px;
            padding: 18px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            transition: transform 0.3s ease;
            border: 1px solid rgba(56, 189, 248, 0.2);
        }

        @media (min-width: 768px) {
            .stat-card {
                padding: 25px;
            }
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(56, 189, 248, 0.5);
        }

        .stat-card h2 {
            font-size: 0.75em;
            color: #94a3b8;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        @media (min-width: 768px) {
            .stat-card h2 {
                font-size: 0.85em;
                margin-bottom: 10px;
            }
        }

        .stat-card .value {
            font-size: 2em;
            color: #38bdf8;
            font-weight: bold;
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.3);
        }

        @media (min-width: 768px) {
            .stat-card .value {
                font-size: 2.5em;
            }
        }

        .section {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.9) 100%);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(56, 189, 248, 0.2);
        }

        @media (min-width: 768px) {
            .section {
                padding: 30px;
            }
        }

        .section h2 {
            color: #f1f5f9;
            margin-bottom: 20px;
            font-size: 1.3em;
            border-bottom: 2px solid #0ea5e9;
            padding-bottom: 10px;
        }

        @media (min-width: 768px) {
            .section h2 {
                font-size: 1.5em;
            }
        }

        .chart-container {
            position: relative;
            height: 250px;
            margin-bottom: 20px;
        }

        @media (min-width: 768px) {
            .chart-container {
                height: 300px;
            }
        }

        .model-usage {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }

        @media (min-width: 640px) {
            .model-usage {
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 15px;
            }
        }

        .model-card {
            background: rgba(15, 23, 42, 0.6);
            padding: 18px;
            border-radius: 10px;
            border-left: 4px solid #0ea5e9;
            border: 1px solid rgba(56, 189, 248, 0.2);
        }

        @media (min-width: 768px) {
            .model-card {
                padding: 20px;
            }
        }

        .model-card h4 {
            color: #cbd5e1;
            margin-bottom: 10px;
            font-size: 1em;
        }

        @media (min-width: 768px) {
            .model-card h4 {
                font-size: 1.1em;
            }
        }

        .model-card .count {
            font-size: 1.8em;
            color: #38bdf8;
            font-weight: bold;
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.3);
        }

        @media (min-width: 768px) {
            .model-card .count {
                font-size: 2em;
            }
        }

        .refresh-btn {
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            display: block;
            margin: 20px auto;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.4);
        }

        .refresh-btn:hover {
            background: linear-gradient(135deg, #0284c7 0%, #1d4ed8 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.5);
        }

        .error {
            background: rgba(239, 68, 68, 0.2);
            border-left: 4px solid #ef4444;
            padding: 15px;
            border-radius: 5px;
            color: #fca5a5;
            margin-bottom: 20px;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .info-banner {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.15) 0%, rgba(37, 99, 235, 0.15) 100%);
            border-left: 4px solid #0ea5e9;
            border: 1px solid rgba(14, 165, 233, 0.3);
            padding: 12px 18px;
            border-radius: 8px;
            color: #cbd5e1;
            margin-bottom: 20px;
            font-size: 0.9em;
            text-align: center;
        }

        .info-banner strong {
            color: #38bdf8;
        }

        .info-banner em {
            color: #38bdf8;
            font-style: normal;
            font-weight: 600;
        }

        .info-banner a {
            color: #60a5fa;
            text-decoration: underline;
            transition: color 0.2s;
        }

        .info-banner a:hover {
            color: #93c5fd;
        }

        @media (max-width: 640px) {
            .info-banner {
                font-size: 0.85em;
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Claude CLI Usage Dashboard</h1>
        <div class="info-banner">
            <strong>Note:</strong> This dashboard tracks <em>local Claude CLI usage</em> from this server only.
            For total account usage across all devices (web, mobile, other machines), check
            <a href="https://claude.ai/settings/usage" target="_blank">claude.ai/settings/usage</a>
        </div>

        <?php
        $historyFile = '/home/appsforte/.claude/history.jsonl';
        $credFile = '/home/appsforte/.claude/.credentials.json';

        if (!file_exists($historyFile)) {
            echo '<div class="error">Error: Claude history file not found</div>';
            exit;
        }

        // Get subscription info
        $subscriptionType = 'Unknown';
        if (file_exists($credFile)) {
            $credData = json_decode(file_get_contents($credFile), true);
            $subscriptionType = ucfirst($credData['claudeAiOauth']['subscriptionType'] ?? 'unknown');
        }

        // Read history
        $lines = file($historyFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $prompts = [];

        foreach ($lines as $line) {
            $data = json_decode($line, true);
            if ($data && isset($data['display'])) {
                $prompts[] = $data;
            }
        }

        // Try to detect model usage from history entries
        $modelCounts = ['sonnet' => 0, 'opus' => 0, 'haiku' => 0, 'unknown' => 0];
        foreach ($prompts as $prompt) {
            $model = 'unknown';

            // Check if there's model information in the prompt data
            if (isset($prompt['model'])) {
                $modelName = strtolower($prompt['model']);
                if (strpos($modelName, 'sonnet') !== false) {
                    $model = 'sonnet';
                } elseif (strpos($modelName, 'opus') !== false) {
                    $model = 'opus';
                } elseif (strpos($modelName, 'haiku') !== false) {
                    $model = 'haiku';
                }
            } elseif (isset($prompt['display'])) {
                // Try to detect from display text
                $display = strtolower($prompt['display']);
                if (strpos($display, 'sonnet') !== false) {
                    $model = 'sonnet';
                } elseif (strpos($display, 'opus') !== false) {
                    $model = 'opus';
                } elseif (strpos($display, 'haiku') !== false) {
                    $model = 'haiku';
                } else {
                    // Default to sonnet for Claude Code
                    $model = 'sonnet';
                }
            }

            $modelCounts[$model]++;
        }

        $promptsJson = json_encode($prompts);
        $modelCountsJson = json_encode($modelCounts);
        ?>

        <div class="subscription-badge">
            <span>Claude <?php echo htmlspecialchars($subscriptionType); ?></span>
        </div>

        <div class="api-status" id="api-status">
            Live API data loading...
        </div>

        <div class="limit-cards">
            <div class="limit-card">
                <h3>5-Hour Rolling Window</h3>
                <div class="progress-bar">
                    <div class="progress-fill" id="rolling-progress" style="width: 0%;" data-percent="0%"></div>
                </div>
                <div class="limit-details">
                    <span class="used" id="rolling-used">Calculating...</span>
                    <span id="rolling-max">Loading...</span>
                </div>
            </div>

            <div class="limit-card">
                <h3>Weekly Sonnet Usage</h3>
                <div class="progress-bar">
                    <div class="progress-fill" id="weekly-sonnet-progress" style="width: 0%;" data-percent="0%"></div>
                </div>
                <div class="limit-details">
                    <span class="used" id="weekly-sonnet-used">0 prompts</span>
                    <span id="sonnet-max">Loading...</span>
                </div>
            </div>

            <div class="limit-card">
                <h3>Weekly Opus Usage</h3>
                <div class="progress-bar">
                    <div class="progress-fill" id="weekly-opus-progress" style="width: 0%;" data-percent="0%"></div>
                </div>
                <div class="limit-details">
                    <span class="used" id="weekly-opus-used">0 prompts</span>
                    <span id="opus-max">Loading...</span>
                </div>
            </div>
        </div>

        <div class="time-filter">
            <button onclick="filterByTime('24h')" id="btn-24h">Last 24 Hours</button>
            <button onclick="filterByTime('7d')" id="btn-7d" class="active">Last 7 Days</button>
            <button onclick="filterByTime('30d')" id="btn-30d">Last 30 Days</button>
            <button onclick="filterByTime('all')" id="btn-all">All Time</button>
        </div>

        <div class="section">
            <h2>Model Usage Breakdown</h2>
            <div class="model-usage" id="model-usage"></div>
        </div>

        <div class="section">
            <h2>Usage Over Time</h2>
            <div class="chart-container">
                <canvas id="usageChart"></canvas>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h2>Total Prompts</h2>
                <div class="value" id="stat-total">0</div>
            </div>
            <div class="stat-card">
                <h2>Daily Average</h2>
                <div class="value" id="stat-avg">0</div>
            </div>
            <div class="stat-card">
                <h2>Most Active Day</h2>
                <div class="value" id="stat-peak">0</div>
            </div>
            <div class="stat-card">
                <h2>Projects Used</h2>
                <div class="value" id="stat-projects">0</div>
            </div>
        </div>

        <button class="refresh-btn" onclick="location.reload()">Refresh Stats</button>
    </div>

    <script>
        const allPrompts = <?php echo $promptsJson; ?>;
        const modelCounts = <?php echo $modelCountsJson; ?>;
        let currentFilter = '7d';
        let usageChart = null;
        let apiData = null;

        // Fetch live API data
        async function fetchApiData() {
            try {
                const response = await fetch('get_usage.php');
                apiData = await response.json();

                if (apiData.error) {
                    document.getElementById('api-status').textContent = 'Error: ' + apiData.error;
                } else if (apiData.connected) {
                    const desc = apiData.limits?.description || 'Subscription data loaded';
                    document.getElementById('api-status').textContent = desc + ' ✓';
                    document.getElementById('api-status').classList.add('live');
                } else {
                    document.getElementById('api-status').textContent = 'Subscription data loaded';
                    document.getElementById('api-status').classList.add('live');
                }

                // Re-update with live API data
                filterByTime(currentFilter);
            } catch (error) {
                document.getElementById('api-status').textContent = 'Unable to load subscription data';
                console.error('Failed to fetch API data:', error);
            }
        }

        function filterByTime(range) {
            currentFilter = range;

            document.querySelectorAll('.time-filter button').forEach(btn => {
                btn.classList.remove('active');
            });
            document.getElementById('btn-' + range).classList.add('active');

            const now = Date.now();
            let cutoff = 0;

            switch(range) {
                case '24h': cutoff = now - (24 * 60 * 60 * 1000); break;
                case '7d': cutoff = now - (7 * 24 * 60 * 60 * 1000); break;
                case '30d': cutoff = now - (30 * 24 * 60 * 60 * 1000); break;
                case 'all': cutoff = 0; break;
            }

            const filtered = allPrompts.filter(p => p.timestamp && p.timestamp >= cutoff);

            updateLimits(filtered, range);
            updateStats(filtered, range);
            updateChart(filtered);
            updateModelUsage(filtered);
        }

        function updateLimits(prompts, range) {
            // Get limits from API data or use defaults
            const limits = apiData?.limits || {
                '5hr_limit': 750,
                'weekly_sonnet_limit': 25000,
                'weekly_opus_limit': 3000
            };

            // Update limit labels
            document.getElementById('rolling-max').textContent = `Max: ${limits['5hr_limit']} prompts`;
            document.getElementById('sonnet-max').textContent = `Max: ${limits.weekly_sonnet_limit.toLocaleString()} prompts`;
            document.getElementById('opus-max').textContent = `Max: ${limits.weekly_opus_limit.toLocaleString()} prompts`;

            // Calculate 5-hour rolling window (last 5 hours)
            const fiveHoursAgo = Date.now() - (5 * 60 * 60 * 1000);
            const last5Hours = allPrompts.filter(p => p.timestamp >= fiveHoursAgo);
            const rolling5HrCount = last5Hours.length;

            const rolling5HrPercent = Math.min(100, (rolling5HrCount / limits['5hr_limit']) * 100);

            document.getElementById('rolling-used').textContent = `${rolling5HrCount} prompts`;
            const rollingProgress = document.getElementById('rolling-progress');
            const rollingPercentRounded = Math.round(rolling5HrPercent);
            rollingProgress.style.width = rolling5HrPercent + '%';
            rollingProgress.setAttribute('data-percent', rollingPercentRounded + '%');
            rollingProgress.textContent = '';

            rollingProgress.classList.remove('warning', 'danger');
            if (rolling5HrPercent > 80) {
                rollingProgress.classList.add('danger');
            } else if (rolling5HrPercent > 60) {
                rollingProgress.classList.add('warning');
            }

            // Weekly usage (last 7 days)
            const weekAgo = Date.now() - (7 * 24 * 60 * 60 * 1000);
            const lastWeek = allPrompts.filter(p => p.timestamp >= weekAgo);

            // Count actual Sonnet usage from the week
            let weeklySonnetCount = 0;
            let weeklyOpusCount = 0;

            lastWeek.forEach(p => {
                let model = 'sonnet'; // default
                if (p.model) {
                    const modelName = p.model.toLowerCase();
                    if (modelName.includes('opus')) {
                        model = 'opus';
                    } else if (modelName.includes('haiku')) {
                        model = 'haiku';
                    }
                }

                if (model === 'sonnet') weeklySonnetCount++;
                if (model === 'opus') weeklyOpusCount++;
            });

            const weeklySonnetPercent = Math.min(100, (weeklySonnetCount / limits.weekly_sonnet_limit) * 100);

            document.getElementById('weekly-sonnet-used').textContent = `${weeklySonnetCount} prompts`;
            const sonnetProgress = document.getElementById('weekly-sonnet-progress');
            const sonnetPercentRounded = Math.round(weeklySonnetPercent);
            sonnetProgress.style.width = weeklySonnetPercent + '%';
            sonnetProgress.setAttribute('data-percent', sonnetPercentRounded + '%');
            sonnetProgress.textContent = '';

            sonnetProgress.classList.remove('warning', 'danger');
            if (weeklySonnetPercent > 80) {
                sonnetProgress.classList.add('danger');
            } else if (weeklySonnetPercent > 60) {
                sonnetProgress.classList.add('warning');
            }

            // Opus usage
            const weeklyOpusPercent = Math.min(100, (weeklyOpusCount / limits.weekly_opus_limit) * 100);

            document.getElementById('weekly-opus-used').textContent = `${weeklyOpusCount} prompts`;
            const opusProgress = document.getElementById('weekly-opus-progress');
            const opusPercentRounded = Math.round(weeklyOpusPercent);
            opusProgress.style.width = weeklyOpusPercent + '%';
            opusProgress.setAttribute('data-percent', opusPercentRounded + '%');
            opusProgress.textContent = '';

            opusProgress.classList.remove('warning', 'danger');
            if (weeklyOpusPercent > 80) {
                opusProgress.classList.add('danger');
            } else if (weeklyOpusPercent > 60) {
                opusProgress.classList.add('warning');
            }
        }

        function updateModelUsage(prompts) {
            // Count models from filtered prompts
            const counts = { sonnet: 0, opus: 0, haiku: 0 };

            prompts.forEach(p => {
                let model = 'sonnet'; // default
                if (p.model) {
                    const modelName = p.model.toLowerCase();
                    if (modelName.includes('opus')) {
                        model = 'opus';
                    } else if (modelName.includes('haiku')) {
                        model = 'haiku';
                    } else if (modelName.includes('sonnet')) {
                        model = 'sonnet';
                    }
                }
                counts[model]++;
            });

            const html = `
                <div class="model-card">
                    <h4>Claude Sonnet 4</h4>
                    <div class="count">${counts.sonnet.toLocaleString()}</div>
                    <div style="font-size: 0.85em; color: #94a3b8; margin-top: 5px;">Most used model</div>
                </div>
                <div class="model-card" style="border-left-color: #ef4444;">
                    <h4>Claude Opus 4</h4>
                    <div class="count">${counts.opus.toLocaleString()}</div>
                    <div style="font-size: 0.85em; color: #94a3b8; margin-top: 5px;">Premium model</div>
                </div>
                <div class="model-card" style="border-left-color: #10b981;">
                    <h4>Claude Haiku</h4>
                    <div class="count">${counts.haiku.toLocaleString()}</div>
                    <div style="font-size: 0.85em; color: #94a3b8; margin-top: 5px;">Fast model</div>
                </div>
            `;
            document.getElementById('model-usage').innerHTML = html;
        }

        function updateStats(prompts, range) {
            const total = prompts.length;

            let days = 1;
            if (range === '24h') days = 1;
            else if (range === '7d') days = 7;
            else if (range === '30d') days = 30;
            else {
                const timestamps = prompts.map(p => p.timestamp).filter(t => t);
                if (timestamps.length > 0) {
                    days = Math.ceil((Math.max(...timestamps) - Math.min(...timestamps)) / (1000 * 60 * 60 * 24)) || 1;
                }
            }

            const avgPerDay = (total / days).toFixed(1);

            const dailyCounts = {};
            prompts.forEach(p => {
                if (p.timestamp) {
                    const date = new Date(p.timestamp).toISOString().split('T')[0];
                    dailyCounts[date] = (dailyCounts[date] || 0) + 1;
                }
            });

            let peakCount = 0;
            Object.values(dailyCounts).forEach(count => {
                if (count > peakCount) peakCount = count;
            });

            const projects = new Set();
            prompts.forEach(p => {
                if (p.project) projects.add(p.project);
            });

            document.getElementById('stat-total').textContent = total.toLocaleString();
            document.getElementById('stat-avg').textContent = avgPerDay;
            document.getElementById('stat-peak').textContent = peakCount;
            document.getElementById('stat-projects').textContent = projects.size;
        }

        function updateChart(prompts) {
            const dailyCounts = {};
            prompts.forEach(p => {
                if (p.timestamp) {
                    const date = new Date(p.timestamp).toISOString().split('T')[0];
                    dailyCounts[date] = (dailyCounts[date] || 0) + 1;
                }
            });

            const sortedDates = Object.keys(dailyCounts).sort();
            const labels = sortedDates.map(date => {
                const d = new Date(date);
                return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });
            const data = sortedDates.map(date => dailyCounts[date]);

            if (usageChart) usageChart.destroy();

            const ctx = document.getElementById('usageChart').getContext('2d');
            usageChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Prompts per Day',
                        data: data,
                        borderColor: '#38bdf8',
                        backgroundColor: 'rgba(56, 189, 248, 0.2)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#38bdf8',
                        pointBorderColor: '#0ea5e9',
                        pointHoverRadius: 6,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleColor: '#f1f5f9',
                            bodyColor: '#cbd5e1',
                            borderColor: '#38bdf8',
                            borderWidth: 1
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                color: '#94a3b8'
                            },
                            grid: {
                                color: 'rgba(56, 189, 248, 0.1)'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#94a3b8'
                            },
                            grid: {
                                color: 'rgba(56, 189, 248, 0.1)'
                            }
                        }
                    }
                }
            });
        }

        // Initialize
        filterByTime('7d');
        fetchApiData();
    </script>
</body>
</html>
