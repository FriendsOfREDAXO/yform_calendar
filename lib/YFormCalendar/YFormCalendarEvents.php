<?php

namespace FriendsOfRedaxo\YFormCalendar;

use rex_yform_manager_table;

/**
 * Model class for rex_yform_calendar table
 */
class YFormCalendarEvents
{
    public static function getCalendarEvents(array $params = [], $customQuery = null)
    {
        $table = rex_yform_manager_table::get('rex_yform_calendar');
        $query = $customQuery ?? $table->query();
        
        return CalRender::getCalendarEvents($params, $query);
    }

    public static function getEventsByDate(string $startDate, ?string $endDate = null, int $limit = PHP_INT_MAX)
    {
        $table = rex_yform_manager_table::get('rex_yform_calendar');
        $params = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'limit' => $limit
        ];
        
        return iterator_to_array(self::getCalendarEvents($params, $table->query()));
    }
}
