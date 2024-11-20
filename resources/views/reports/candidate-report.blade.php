<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>{{ $title }}</title>
    <style>
        @font-face {
            font-family: 'Figtree';
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: 'Figtree', 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 40px;
            color: #1a1a1a;
            line-height: 1.5;
        }

        /* Header Section */
        .header-table {
            width: 100%;
            margin-bottom: 40px;
            border-collapse: collapse;
        }

        .company-name-cell {
            width: 50%;
            font-size: 24px;
            font-weight: 500;
            border-right: 5px solid #E5E5E5;
            padding: 10px 140px 10px 10px;
            vertical-align: top;
        }

        .department-info-cell {
            width: 50%;
            padding: 10px;
            text-align: right;
            vertical-align: top;
        }

        .department-title {
            color: #0066ff;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .department-name {
            font-size: 12px;
            color: #1a1a1a;
        }

        /* Candidate Info */
        .candidate-info {
            margin-bottom: 30px;
        }

        .candidate-name {
            font-size: 20px;
            font-weight: 500;
            margin-bottom: 5px;
            padding-left: 10px;
        }

        .candidate-email {
            font-size: 12px;
            color: #666666;
            padding-left: 10px;
        }

        /* Stats Section */
        .stats-table {
            width: 100%;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .stats-table td {
            padding: 20px;
            width: 25%;
            vertical-align: top;
        }

        .stat-label {
            font-size: 17px;
            color: #1a1a1a;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .stat-label.center {
            text-align: center;
        }

        .stat-value {
            font-size: 20px;
            color: #0066ff;
            font-weight: 500;
            text-align: center;
        }
        
        .stat-value.black {
            font-size: 13px;
            font-weight: 50;
            color: #000000;
            text-align: left;
        }

        /* Monitor Section */
        .monitor-section {
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 20px;
            border-collapse: collapse;
        }

        .monitor-title {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .monitor-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .monitor-table td {
            padding: 5px 0;
            font-size: 13px;
        }
        
        .monitor-table td:last-child {
            text-align: right;
            font-weight: 300;
            font-size: 15px;
        }

        .yes-badge {
            background: #e2f4b3;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 15px;
        }

        .badge-value {
            padding: 4px 20px;
            border-radius: 12px;
            font-size: 15px;
        }

        /* Test Sections */
        .score-detail {
            font-size: 13px;
            font-weight: 50;
            color: #000000;
            padding-top:10px;
            margin-left: 2px;
        }

        .score-detail-value {
            font-size: 20px;
            color: #0066ff;
            font-weight: 500;
            padding-top:10px;
        }

        .test-sec-header{
            width: 100%;
            display: inline-block;
        }

        .test-sec-data{
            display: inline-block;
            justify-content:center;
        }
        
        .data{
            display: inline-block;
            text-align: right;
            width: 55%;
            height: 7.7%;
            justify-content:center;
        }

        .test-sec-data h1 {
            font-size: 18px;
            font-weight: 500;
            color: #1a1a1a;
        }
        
        .score-detail {
            font-size: 13px;
            color: #666666;
            margin: 0;
            width: auto;
            display: inline-block;
            justify-content:center;
        }
        
        .score-detail-value {
            font-size: 20px;
            font-weight: 500;
            color: #0066ff;
            margin: 0;
            width: auto;
            text-align: center;
            display: inline-block;
        }
        
        .scoring-method {
            font-size: 13px;
            color: #1a1a1a;
            width: auto;
            display: inline-block;
            text-align:left;
        }

        .test-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .test{
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;

        }

        .test-header-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .test-title {
            font-size: 16px;
            font-weight: 500;
        }

        .test-score {
            text-align: right;
            font-size: 16px;
            font-weight: 500;
        }

        .test-description {
            color: #666666;
            font-size: 12px;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        /* Skill Bars */
        .skill-item {
            margin-bottom: 15px;
        }

        .skill-name {
            font-size: 14px;
            font-weight: 100;
            margin-bottom: 8px;
            color: #1a1a1a;
        }

        .skill-bar-table {
            width: 100%;
            height: 24px;
            background: #ffffff;
            border-radius: 12px;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        .bar-correct, .bar-incorrect, .bar-unanswered {
            border-radius: 0; 
        }

        /* If it's the only cell (both first and last), apply full border radius */
        .bar-correct:only-child,
        .bar-incorrect:only-child,
        .bar-unanswered:only-child {
            border-radius: 8px;
        }

        .bar-correct:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        .bar-correct:last-child,
        .bar-incorrect:last-child,
        .bar-unanswered:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .skill-bar-table td {
            height: 24px;
            padding: 0;
            text-align: center;
            font-size: 12px;
            font-weight: 100;
            color: #000000;
            position: relative;
        }

        .bar-correct {
            background-color: #a3e635;
        }

        .bar-incorrect {
            background-color: #fecaca;
        }

        .bar-unanswered {
            background-color: #e5e5e5;
        }

        .skill-guide {
            display: inline-block;
            width: 100%;
            gap: 16px;
            margin-top: 8px;
            font-size: 12px;
            color: #666666;
            text-align: right;
        }

        .guide-item {
            display: inline-block;
            align-items: center;
            gap: 4px;
        }
        
        .guide-color {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .guide-color.correct {
            background-color: #a3e635;
        }

        .guide-color.incorrect {
            background-color: #fecaca;
        }

        .guide-color.unanswered {
            background-color: #e5e5e5;
        }

        /* Time Info */
        .time-info {
            font-size: 12px;
            margin-top: 15px;
        }

        /* Footer */
        .footer {
            position: absolute;
            left:-20px;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            padding: 20px;
            color: #e5e5e5;
            background: #122f53;
        }
        
        .brand{
            color: #ffffff;
            font-size: 22px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td class="company-name-cell">{{ $companyName }}</td>
            <td class="department-info-cell">
                <div class="department-title">Assessment</div>
                <div class="department-name">Department: {{ $department }}</div>
            </td>
        </tr>
    </table>

    <!-- Candidate Info -->
    <div class="candidate-info">
        <div class="candidate-name">{{ $candidateName }}</div>
        <div class="candidate-email">{{ $email }}</div>
    </div>

    <!-- Stats Section -->
    <table class="stats-table">
        <tr>
            <td>
                <div class="stat-label">Status</div>
                <div class="stat-value black">{{ $status }}</div>
            </td>
            <td>
                <div class="stat-label center">Average score</div>
                <div class="stat-value">{{ $averageScore }}%</div>
            </td>
            <td>
                <div class="stat-label center">Weighted</div>
                <div class="stat-value">{{ $weightedScore }}%</div>
            </td>
            <td>
                <div class="stat-label">Scoring method</div>
                <div class="stat-value black">Percentage of correct answers</div>
            </td>
        </tr>
    </table>

    <!-- Anti-cheating Monitor -->
    <div class="monitor-section">
        <div class="monitor-title">Anti-cheating monitor</div>
        <table class="monitor-table">
            @foreach($antiCheat as $check)
            <tr>
                <td>{{ $check['label'] }}</td>
                <td>
                    @if($check['value'] === 'Yes')
                    <span class="yes-badge">Yes</span>
                    @else
                    <span class="badge-value">{{ $check['value'] }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
    </div>

    <!-- Test Sections -->
    @foreach($tests as $test)
    <div class="test-section">
        <div class="test-sec-header">
            <div class="test-sec-data">
                <h1>Test Scores</h1>
                <div class="scoring-method">Scoring method: % of correct answers</div>
            </div>
            <div class="data">
                <div class="test-sec-data">
                    <p class="score-detail">Average score</p>
                    <p class="score-detail-value">{{ $averageScore }}%</p>
                </div>
                <div class="test-sec-data">
                    <p class="score-detail">Weighted</p>
                    <p class="score-detail-value">{{ $weightedScore }}%</p>
                </div>
            </div>
        </div>

        <div class="test">
            <table class="test-header-table">
                <tr>
                    <td class="test-title">{{ $test['name'] }}</td>
                    <td class="test-score">{{ $test['score'] }}%</td>
                </tr>
            </table>
    
            <div class="test-description">{{ $test['description'] }}</div>
            
            @foreach($test['skills'] as $skill)
            <div class="skill-item">
                <div class="skill-name">• {{ $skill['name'] }}</div>
                <table class="skill-bar-table">
                    <tr>
                        @if($skill['correct'] > 0)
                        <td class="bar-correct" width="{{ $skill['correct']*25 }}%">
                            {{ $skill['correct'] }}
                        </td>
                        @endif
                        @if($skill['incorrect'] > 0)
                        <td class="bar-incorrect" width="{{ $skill['incorrect']*25 }}%">
                            {{ $skill['incorrect'] }}
                        </td>
                        @endif
                        @if($skill['unanswered'] > 0)
                        <td class="bar-unanswered" width="{{ $skill['unanswered']*25 }}%">
                            {{ $skill['unanswered'] }}
                        </td>
                        @endif
                    </tr>
                </table>
            </div>
            @endforeach

            <div class="skill-guide">
                <div class="guide-item">
                    <div class="guide-color correct"></div>
                    <span>Correct</span>
                </div>
                <div class="guide-item">
                    <div class="guide-color incorrect"></div>
                    <span>Incorrect</span>
                </div>
                <div class="guide-item">
                    <div class="guide-color unanswered"></div>
                    <span>Not answered</span>
                </div>
            </div>
    
            <div class="time-info">
                Finished in {{ $test['time_spent'] }} out of {{ $test['time_limit'] }}
            </div>
        </div>
    </div>
    @endforeach

    <div class="footer">
        <p>Page {{ '[page]' }} of {{ '[pages]' }}</p>
        <p class="brand">Powered by Milele SkillSage</p>
    </div>

</body>
</html>