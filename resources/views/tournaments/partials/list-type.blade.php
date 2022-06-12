<?php
    switch($tournament->tournament_type_id) {
        case 2: echo "<span class=\"".@$class." tournament-type type-store\" title=\"store championship\">S</span>"; break;
        case 3: echo "<span class=\"".@$class." tournament-type type-regional\" title=\"regional championship\">R</span>"; break;
        case 4: echo "<span class=\"".@$class." tournament-type type-national\" title=\"national championship\">N</span>"; break;
        case 5: echo "<span class=\"".@$class." tournament-type type-world\" title=\"world championship\">W</span>"; break;
        case 8: if (!$tournament->date) {
                    echo "<span class=\"".@$class." tournament-type type-recurring\"><i class=\"fa fa-repeat\" aria-hidden=\"true\"></i></span>";
                } break;
        case 9: echo "<span class=\"".@$class." tournament-type type-continental\" title=\"continental championship\">C</span>"; break;
        case 10: echo "<span class=\"".@$class." tournament-type type-team\" title=\"team tournament\">TT</span>"; break;
        case 11: echo "<span class=\"".@$class." tournament-type type-circuit\" title=\"circuit opener\">CO</span>"; break;
        case 12: echo "<span class=\"".@$class." tournament-type type-async\" title=\"asynchronous tournament\">A</span>"; break;
    }
?>