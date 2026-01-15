<?php 
namespace FriendsOfRedaxo\YFormCalendar;

use rex;
use rex_addon;
use rex_api_function;
use rex_extension;
use rex_plugin;
use rex_view;
use rex_yform;
use rex_yform_manager_dataset;

$package = rex_addon::get('yform_calendar');
if (rex_addon::get('yform')->isAvailable()) {
    rex_yform::addTemplatePath($package->getPath('ytemplates'));
}

if (rex::isBackend()) {
    rex_view::addCssFile(rex_addon::get('yform_calendar')->getAssetsUrl('rrule.css'));
    rex_view::addJsFile(rex_addon::get('yform_calendar')->getAssetsUrl('rrule.js'));
}

// API-Klasse registrieren
rex_api_function::register($package->getPath('lib/rex_api_rrule.php'), 'rrule');

// Inline Script für RRule-Termine in Listview
if (rex::isBackend()) {
    rex_extension::register('PAGE_HEADER', function($ep) {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Delegiert Event-Listener für dynamisch hinzugefügte Buttons
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-show-occurrences-list')) {
                    const button = e.target.closest('.btn-show-occurrences-list');
                    const rruleString = button.dataset.rrule;
                    const exdateString = button.dataset.exdate || '';
                    const buttonId = button.id;
                    
                    const listContainer = document.querySelector('.rrule-occurrences-list-' + buttonId);
                    if (!listContainer) return;
                    
                    // Lade die Termine
                    fetch('/redaxo/index.php?rex-api-call=rrule&action=get_next_occurrences', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'rrule=' + encodeURIComponent(rruleString) + '&exdate=' + encodeURIComponent(exdateString) + '&start_date=' + new Date().toISOString().split('T')[0] + '&limit=10'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            listContainer.innerHTML = '<div class="alert alert-danger"><strong>Fehler:</strong> ' + data.error + '</div>';
                            return;
                        }

                        if (!data.occurrences || data.occurrences.length === 0) {
                            listContainer.innerHTML = '<p class="text-muted">Keine Termine gefunden.</p>';
                            return;
                        }

                        let html = '<ul class="list-group">';
                        data.occurrences.forEach(occ => {
                            html += '<li class="list-group-item">';
                            html += '<strong>' + occ.formatted + '</strong>';
                            html += '<br><small class="text-muted">' + occ.date + '</small>';
                            html += '</li>';
                        });
                        html += '</ul>';
                        
                        listContainer.innerHTML = html;
                    })
                    .catch(err => {
                        listContainer.innerHTML = '<div class="alert alert-danger"><strong>Fehler:</strong> ' + err.message + '</div>';
                    });
                }
            }, false);
        });
        </script>
        <?php
        return $ep->getSubject();
    });
}
