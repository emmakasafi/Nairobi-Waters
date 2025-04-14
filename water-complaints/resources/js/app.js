// Laravel Bootstrap (includes axios, echo setup, etc.)
import './bootstrap';

// jQuery (important: define it globally)
import $ from 'jquery';
window.$ = window.jQuery = $;

// Bootstrap (JS functionality)
import 'bootstrap';

// AdminLTE Core
import 'admin-lte';

// OverlayScrollbars (note correct package name!)
import 'overlayscrollbars';

// Chart.js (auto import is the best option for newer versions)
import 'chart.js/auto';

// DataTables
import 'datatables.net';

// Optional: initialize tooltips, overlays, etc.
$(function () {
    // Example: Tooltip init
    $('[data-toggle="tooltip"]').tooltip();

    // Example: DataTable init
    $('#example').DataTable();

    // Example: overlay scrollbar (optional, if using layout that needs it)
    $("body").overlayScrollbars({});
});
