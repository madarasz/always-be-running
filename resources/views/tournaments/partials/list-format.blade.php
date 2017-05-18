<?php
    switch($tournament->tournament_format_id) {
        case 2: echo "<span class=\"".@$class." tournament-type type-cache\" title=\"cache refresh\">CR</span>"; break;
        case 3: echo "<span class=\"".@$class." tournament-type type-onesies\" title=\"1.1.1.1\">1</span>"; break;
        case 4: echo "<span class=\"".@$class." tournament-type type-draft\" title=\"draft\">D</span>"; break;
        case 5: echo "<span class=\"".@$class." tournament-type type-cube-draft\" title=\"cube draft\">CD</span>"; break;
    }
?>