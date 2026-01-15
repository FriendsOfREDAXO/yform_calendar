<?php
namespace FriendsOfRedaxo\YFormCalendar;

use FriendsOfRedaxo\YFormCalendar\YFormCalendarEvents;

// Alle Termine ab heute abrufen (max. 100)
$today = (new DateTime())->format('Y-m-d');
$events = YFormCalendarEvents::getEventsByDate($today, null, 100);

if (empty($events)) {
    echo '<div class="alert alert-info"><i class="fa fa-info-circle"></i> Keine Termine gefunden.</div>';
} else {
    echo '<div class="events-container">';
    echo '<h3>Termine ab ' . date('d.m.Y') . '</h3>';
    echo '<table class="table table-striped table-hover">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Titel</th>';
    echo '<th>Startdatum</th>';
    echo '<th>Enddatum</th>';
    echo '<th>Ganztägig</th>';
    echo '<th>Serie</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    foreach ($events as $event) {
        $title = $event->getValue('title');
        $start = $event->getValue('dtstart');
        $end = $event->getValue('dtend');
        $allday = $event->getValue('all_day');
        $rrule = $event->getValue('rrule');
        
        echo '<tr>';
        echo '<td><strong>' . rex_escape($title) . '</strong></td>';
        echo '<td>' . ($allday ? date('d.m.Y', strtotime($start)) : date('d.m.Y H:i', strtotime($start))) . '</td>';
        echo '<td>' . ($allday ? date('d.m.Y', strtotime($end)) : date('d.m.Y H:i', strtotime($end))) . '</td>';
        echo '<td>' . ($allday ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>') . '</td>';
        echo '<td>' . ($rrule ? '<span class="badge badge-info">Wiederkehrend</span>' : '<span class="badge badge-secondary">Einmalig</span>') . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '<p class="text-muted"><strong>Gesamt:</strong> ' . count($events) . ' Termine angezeigt</p>';
    echo '</div>';
}
?>

<style>
.events-container {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 4px;
    margin: 20px 0;
}

.events-container h3 {
    margin-top: 0;
    color: #333;
    border-bottom: 2px solid #007bff;
    padding-bottom: 10px;
}

.events-container .table {
    margin-bottom: 0;
}

.events-container .badge {
    padding: 4px 8px;
    font-size: 11px;
}
</style>
