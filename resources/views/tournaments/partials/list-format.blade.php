<?php
    switch($tournament->tournament_format_id) {
        case 2: echo "<span class=\"".@$class." tournament-format type-cache\" title=\"cache refresh\">CR</span>"; break;
        case 3: echo "<span class=\"".@$class." tournament-format type-onesies\" title=\"1.1.1.1\">1</span>"; break;
        case 4: echo "<span class=\"".@$class." tournament-format type-draft\" title=\"draft\">D</span>"; break;
        case 5: echo "<span class=\"".@$class." tournament-format type-cube-draft\" title=\"cube draft\">CD</span>"; break;
        // new formats
        case 11: echo "<span class=\"".@$class." tournament-format type-startup\" title=\"startup\">SU</span>"; break;
        case 8: echo "<span class=\"".@$class." tournament-format type-snapshot\" title=\"snapshot\">SN</span>"; break;
        case 7: echo "<span class=\"".@$class." tournament-format type-eternal\" title=\"eternal\">E</span>"; break;
        case 6: echo "<span class=\"".@$class." tournament-format type-other\" title=\"other\">?</span>"; break;
    }
?>