@extends('layouts.app')

@section('title', 'Kalender Event Publik')

@section('content')
<div class="page-header page-header-public">
    <div class="page-header-inner">
        <div class="page-header-text">
            <h1 class="page-title">Kalender Event</h1>
            <p class="page-subtitle">Jadwal kegiatan dan acara perusahaan</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-lock"></i> Login untuk Kelola Event
            </a>
        </div>
    </div>
</div>

<!-- Calendar Wrapper -->
<div class="calendar-wrapper">
    <div id="publicCalendar"></div>
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
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        },
        buttonText: { today: 'Hari Ini' },
        height: 'auto',
        fixedWeekCount: false,
        dayMaxEvents: 3,
        events: '/api/events',
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        eventDidMount: function(info) {
            // Tooltip saat hover
            info.el.setAttribute('title', info.event.title);
        },
        eventClick: function(info) {
            showDetailModal(info.event);
        },
        dateClick: function() {
            // Public: tidak bisa tambah event
        }
    });

    calendar.render();

    // ─── Detail Modal ────────────────────────────────────────────────────────
    function showDetailModal(event) {
        const props = event.extendedProps;
        const colorMap = { blue: '#3b82f6', green: '#10b981', orange: '#f59e0b', red: '#ef4444' };

        document.getElementById('detailColorBar').style.background = colorMap[props.color_label] || '#3b82f6';
        document.getElementById('detailTitle').textContent = event.title;

        const dateStr = event.start ? event.start.toLocaleDateString('id-ID', {weekday:'long', year:'numeric', month:'long', day:'numeric'}) : '-';
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
