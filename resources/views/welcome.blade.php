@extends('layouts.app')

@section('title', 'Kalender Event Publik')

@section('content')
<div class="calendar-redesign-wrapper">
    <div class="calendar-container">
    <!-- Sidebar -->
    <aside class="calendar-sidebar">
        <div>
            <div class="calendar-header-large">
                <div class="cal-month" id="customMonth">Januari</div>
                <div class="cal-year" id="customYear">2025</div>
                
                <div class="cal-nav">
                    <button class="btn-cal-nav" id="calPrev" title="Bulan Sebelumnya">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button class="btn-cal-nav" id="calNext" title="Bulan Berikutnya">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>

            <!-- Agenda List Section -->
            <div class="legend-section">
                <div class="legend-title" id="agendaTitle">Agenda Bulan April</div>
                <div id="agendaList" class="agenda-list">
                    <!-- Agenda items will be injected here -->
                    <div class="agenda-empty">Tidak ada agenda di bulan ini.</div>
                </div>
            </div>
        </div>

    </aside>

    <!-- Main Calendar -->
    <main class="calendar-main">
        <div id="publicCalendar"></div>
    </main>
</div>
</div>

<!-- ─── EVENT DETAIL MODAL ─────────────────────────────────────────────────── -->
<div class="modal-overlay" id="detailModalOverlay" role="dialog" aria-modal="true" aria-labelledby="detailModalTitle">
    <div class="modal modal-detail" id="detailModal">
        <div class="modal-header">
            <div class="modal-event-color-bar" id="detailColorBar"></div>
            <div class="modal-header-content">
                <h2 class="modal-title" id="detailModalTitle">Detail Event</h2>
                <button class="modal-close" id="detailModalClose" aria-label="Tutup modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
        <div class="modal-body" id="detailModalBody">
            <div class="event-detail-title" id="detailTitle"></div>
            <div class="event-detail-grid">
                <div class="event-detail-item" id="detailDateRow">
                    <i class="fa-solid fa-calendar-day event-detail-icon"></i>
                    <span id="detailDate"></span>
                </div>
                <div class="event-detail-item" id="detailTimeRow">
                    <i class="fa-solid fa-clock event-detail-icon"></i>
                    <span id="detailTime"></span>
                </div>
                <div class="event-detail-item" id="detailLocationRow">
                    <i class="fa-solid fa-location-dot event-detail-icon"></i>
                    <span id="detailLocation"></span>
                </div>
                <div class="event-detail-item" id="detailDescRow">
                    <i class="fa-solid fa-align-left event-detail-icon"></i>
                    <span id="detailDesc"></span>
                </div>
                <div class="event-detail-item" id="detailCreatorRow">
                    <i class="fa-solid fa-user event-detail-icon"></i>
                    <span id="detailCreator"></span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" id="detailCloseBtn">Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('publicCalendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        headerToolbar: false, // Hide default header
        height: 'auto',
        fixedWeekCount: false,
        dayMaxEvents: 3,
        events: '/api/events',
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        eventDidMount: function(info) {
            info.el.setAttribute('title', info.event.title);
        },
        eventClick: function(info) {
            showDetailModal(info.event);
        },
        datesSet: function(info) {
            // Update sidebar Month/Year
            const date = info.view.currentStart;
            const monthName = date.toLocaleDateString('id-ID', { month: 'long' });
            const year = date.getFullYear();
            
            document.getElementById('customMonth').textContent = monthName;
            document.getElementById('customYear').textContent = year;
            document.getElementById('agendaTitle').textContent = `Agenda Bulan ${monthName}`;

            // Fetch and show Agendas
            fetchAgendas(info.view.activeStart, info.view.activeEnd);
        }
    });

    calendar.render();

    // ─── Fetch Agendas for Sidebar ──────────────────────────────────────────
    function fetchAgendas(start, end) {
        const agendaList = document.getElementById('agendaList');
        const apiUrl = `/api/events?start=${start.toISOString()}&end=${end.toISOString()}`;

        fetch(apiUrl)
            .then(res => res.json())
            .then(events => {
                agendaList.innerHTML = '';
                
                // Filter events based on current view range (since API might return all)
                const monthEvents = events.filter(ev => {
                    const evDate = new Date(ev.start);
                    return evDate >= start && evDate <= end;
                });

                if (monthEvents.length === 0) {
                    agendaList.innerHTML = '<div class="agenda-empty">Tidak ada agenda di bulan ini.</div>';
                    return;
                }

                // Sort events by date
                monthEvents.sort((a, b) => new Date(a.start) - new Date(b.start));

                monthEvents.forEach(ev => {
                    const evDate = new Date(ev.start);
                    const day = evDate.getDate();
                    const dow = evDate.toLocaleDateString('id-ID', { weekday: 'short' });
                    const time = ev.extendedProps.start_time || '00:00';
                    
                    const item = document.createElement('a');
                    item.href = '#';
                    item.className = 'agenda-item';
                    item.onclick = (e) => {
                        e.preventDefault();
                        showDetailModal(calendar.getEventById(ev.id) || ev);
                    };

                    item.innerHTML = `
                        <div class="agenda-date">
                            <span class="day">${day}</span>
                            <span class="dow">${dow}</span>
                        </div>
                        <div class="agenda-info">
                            <div class="agenda-time">${time} WIB</div>
                            <div class="agenda-name">${ev.title}</div>
                        </div>
                    `;
                    agendaList.appendChild(item);
                });
            })
            .catch(err => {
                console.error('Error fetching agendas:', err);
                agendaList.innerHTML = '<div class="agenda-empty">Gagal memuat agenda.</div>';
            });
    }

    // ─── Custom Navigation ──────────────────────────────────────────────────
    document.getElementById('calPrev').addEventListener('click', () => calendar.prev());
    document.getElementById('calNext').addEventListener('click', () => calendar.next());

    // ─── Detail Modal ────────────────────────────────────────────────────────
    function showDetailModal(event) {
        const props = event.extendedProps;
        const colorMap = { blue: '#3b82f6', green: '#10b981', orange: '#f59e0b', red: '#ef4444' };

        document.getElementById('detailColorBar').style.background = colorMap[props.color_label] || '#3b82f6';
        document.getElementById('detailTitle').textContent = event.title;

        // Handle both FC Event object and raw JSON object
        const startObj = typeof event.start === 'string' ? new Date(event.start) : event.start;
        const dateStr = startObj ? startObj.toLocaleDateString('id-ID', {weekday:'long', year:'numeric', month:'long', day:'numeric'}) : '-';
        document.getElementById('detailDate').textContent = dateStr;

        const timeEl = document.getElementById('detailTimeRow');
        if (props.start_time) {
            document.getElementById('detailTime').textContent = props.start_time + (props.end_time ? ' — ' + props.end_time : '');
            timeEl.style.display = '';
        } else {
            timeEl.style.display = 'none';
        }

        const locEl = document.getElementById('detailLocationRow');
        if (props.location) {
            document.getElementById('detailLocation').textContent = props.location;
            locEl.style.display = '';
        } else {
            locEl.style.display = 'none';
        }

        const descEl = document.getElementById('detailDescRow');
        if (props.description) {
            document.getElementById('detailDesc').textContent = props.description;
            descEl.style.display = '';
        } else {
            descEl.style.display = 'none';
        }

        if (props.creator) {
            document.getElementById('detailCreator').textContent = 'Dibuat oleh: ' + props.creator;
            document.getElementById('detailCreatorRow').style.display = '';
        } else {
            document.getElementById('detailCreatorRow').style.display = 'none';
        }

        openModal('detailModalOverlay');
    }

    // ─── Modal Helpers ────────────────────────────────────────────────────────
    function openModal(id) {
        const overlay = document.getElementById(id);
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(id) {
        const overlay = document.getElementById(id);
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    document.getElementById('detailModalClose').addEventListener('click', () => closeModal('detailModalOverlay'));
    document.getElementById('detailCloseBtn').addEventListener('click', () => closeModal('detailModalOverlay'));
    document.getElementById('detailModalOverlay').addEventListener('click', function(e) {
        if (e.target === this) closeModal('detailModalOverlay');
    });
});
</script>
@endpush
