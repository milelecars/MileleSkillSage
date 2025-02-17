<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo e($title); ?></title>
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
            margin-bottom: 30px;
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
            margin-bottom: 20px;
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
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .monitor-table tr {
            height: 36px;
        }
        
        .monitor-table td {
            padding: 5px 0;
            font-size: 13px;
        }
        
        .monitor-table td:last-child {
            text-align: right;
            font-weight: 300;
            font-size: 15px;
            width: 120px;
            padding-right: 20px;
        }

        .monitor-table td:last-child span {
            display: inline-block;
            min-width: 60px;
            text-align: center;
        }

        .yes-badge {
            background: #e2f4b3;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 15px;
        }
        
        .no-badge {
            background: #f4b3b3;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 15px;
        }

        .count-badge {
            background: #e2f4b3;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 15px;
        }

        .count-badge.flagged {
            background-color: #f4b3b3;
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
            height: 10%;
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
            gap: 20px;
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
        
        /* Red-Flagged Section */
        .red-flag-section {
            background: #fdf2f2;
            border: 1px solid #e63946;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .red-flag-title {
            font-size: 20px;
            font-weight: bold;
            color: #e63946;
            text-align: center;
            margin-bottom: 15px;
        }

        .red-flag-category {
            margin-bottom: 15px;
            color: #122f53;
        }

        .category-title {
            font-size: 18px;
            font-weight: bold;
            
            margin-bottom: 10px;
            text-decoration: underline;
        }

        .red-flag-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
        }

        .red-flag-th {
            background: #f8d7da;
            font-size: 14px;
            font-weight: bold;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #e63946;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;

        }

        .red-flag-td {
            padding: 10px;
            border-bottom: 1px solid #e5e5e5;
            font-size: 14px;
            color: #333;
        }

        .red-flag-row:nth-child(even) {
            background: #f8f9fa;
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
            bottom: 10;
            width: 100%;
            text-align: center;
            font-size: 10px;
            padding: 0px 20px;
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
            <td class="company-name-cell"><?php echo e($companyName); ?></td>
            <td class="department-info-cell">
                <div class="department-title">Assessment</div>
                <div class="department-name">Department: <?php echo e($department); ?></div>
            </td>
        </tr>
    </table>

    <!-- Candidate Info -->
    <div class="candidate-info">
        <div class="candidate-name"><?php echo e($candidateName); ?></div>
        <div class="candidate-email"><?php echo e($email); ?></div>
    </div>

    <!-- Stats Section -->
    <table class="stats-table">
        <tr>
            <td>
                <div class="stat-label">Status</div>
                <div class="stat-value black"><?php echo e($status); ?></div>
            </td>
            <td>
                <div class="stat-label center">Average score</div>
                <div class="stat-value"><?php echo e($averageScore); ?>%</div>
            </td>
            <td>
                <div class="stat-label center">Weighted</div>
                <div class="stat-value"><?php echo e($weightedScore); ?>%</div>
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
            <?php $__currentLoopData = $antiCheat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($check['label']); ?></td>
                <td>
                    <?php if($check['value'] === 'Yes'): ?>
                        <span class="yes-badge">Yes</span>
                    <?php elseif($check['value'] === 'No'): ?>
                        <span class="no-badge">No</span>
                    <?php elseif(is_numeric($check['value'])): ?>
                        <span class="count-badge <?php echo e(isset($check['flagged']) && $check['flagged'] === 'Yes' ? 'flagged' : ''); ?>">
                            <?php echo e($check['value']); ?>

                        </span>
                    <?php else: ?>
                        <span class="badge-value"><?php echo e($check['value']); ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </table>
    </div>

    <!-- Red-Flagged LSQ Questions Grouped by Category -->
    <div class="red-flag-section">
        <h2 class="red-flag-title">⚠️ Red-Flagged LSQ Questions</h2>

        <?php $__currentLoopData = $groupedQuestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $questions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="red-flag-category">
                <h3 class="category-title"><?php echo e($category); ?></h3> 
                <table class="red-flag-table">
                    <tr>
                        <th class="red-flag-th">Question</th>
                        <th class="red-flag-th">Answer</th>
                    </tr>

                    <?php $__currentLoopData = $questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr class="red-flag-row">
        <td class="red-flag-td"><?php echo e($question->question_text); ?></td>
        <td class="red-flag-td">
            <?php
                $answer = $redFlaggedAnswers->where('question_id', $question->id)->first();
            ?>
            <?php if($answer): ?>
                <?php echo e($answer['meaning']); ?>

            <?php else: ?>
                No answer provided
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                </table>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>


    <!-- Test Sections -->

    <div class="footer">
        <script type="text/php">
            if (isset($pdf)) {
                $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
                $font = 'figtree';
                $size =9;
                $color = array(0,0,0);
                $pdf->page_text(($pdf->get_width()/2) - 35, $pdf->get_height()-35, $text, $font, $size, $color);
            }
        </script>
        <p class="brand">Powered by Milele SkillSage</p>
    </div>

</body>
</html><?php /**PATH C:\Users\HeliaHaghighi\Desktop\MileleSkillSage\resources\views/reports/candidate-report.blade.php ENDPATH**/ ?>