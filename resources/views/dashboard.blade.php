@extends('layouts.app')

@section('title', 'Dashboard')

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

                <div class="sidebar-actions">
                    <button class="btn btn-sidebar" id="addEventBtn">
                        <i class="fa-solid fa-plus"></i> Tambah Event
                    </button>
                    @if($user->canManageGlobal())
                    @endif
                </div>

                <!-- Agenda List Section -->
                <div class="legend-section">
                    <div class="legend-title" id="agendaTitle">Agenda Bulan</div>
                    <div id="agendaList" class="agenda-list">
                        <div class="agenda-empty">Tidak ada agenda.</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Calendar -->
        <main class="calendar-main">
            <div id="dashboardCalendar"></div>
        </main>
    </div>
</div>

<!-- ─── ADD / EDIT EVENT MODAL ──────────────────────────────────────────────── -->
<div class="modal-overlay" id="eventModalOverlay" role="dialog" aria-modal="true" aria-labelledby="eventModalTitle">
    <div class="modal" id="eventModal">
        <div class="modal-header">
            <h2 class="modal-title" id="eventModalTitle">Tambah Event</h2>
            <button class="modal-close" id="eventModalClose" aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <form id="eventForm">
                @csrf
                <input type="hidden" id="eventId" value="">

                <div class="form-row-2">
                    <div class="form-group">
                        <label class="form-label" for="evTitle">Judul Event <span class="required">*</span></label>
                        <input type="text" id="evTitle" class="form-input" placeholder="Nama event..." required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="evDate">Tanggal <span class="required">*</span></label>
                        <input type="date" id="evDate" class="form-input" required>
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label class="form-label" for="evStart">Mulai</label>
                        <input type="time" id="evStart" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="evEnd">Selesai</label>
                        <input type="time" id="evEnd" class="form-input">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="evLocation">Lokasi</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-location-dot input-icon"></i>
                        <input type="text" id="evLocation" class="form-input" placeholder="Ruang rapat, Zoom, dll...">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="evDesc">Deskripsi</label>
                    <textarea id="evDesc" class="form-input form-textarea" rows="3" placeholder="Keterangan tambahan..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Warna Label</label>
                    <div class="color-picker" id="colorPicker">
                        <label class="color-option">
                            <input type="radio" name="evColor" value="blue" checked>
                            <span class="color-dot color-blue" title="Biru"></span>
                        </label>
                        <label class="color-option">
                            <input type="radio" name="evColor" value="green">
                            <span class="color-dot color-green" title="Hijau"></span>
                        </label>
                        <label class="color-option">
                            <input type="radio" name="evColor" value="orange">
                            <span class="color-dot color-orange" title="Oranye"></span>
                        </label>
                        <label class="color-option">
                            <input type="radio" name="evColor" value="red">
                            <span class="color-dot color-red" title="Merah"></span>
                        </label>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 1.5rem;">
                    <label class="toggle-switch">
                        <input type="checkbox" id="evIsPrivate" name="is_private" value="1">
                        <span class="toggle-slider"></span>
                        <span class="toggle-label" style="font-weight: 700; color: var(--text-primary);">Event Privat</span>
                    </label>
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0.25rem 0 0 52px;" id="privateLabelHint">
                        Aktifkan agar event ini hanya bisa dilihat oleh pihak terkait.
                    </p>
                </div>

                <div class="form-group" style="margin-top: 1.5rem; background: rgba(37, 211, 102, 0.05); border: 1px solid rgba(37, 211, 102, 0.2); padding: 1rem; border-radius: 12px;">
                    <label class="form-label" style="color: #128c7e;" for="evWaSchedule"><i class="fa-brands fa-whatsapp"></i> Waktu Kirim Pengingat WA (Opsional)</label>
                    <input type="datetime-local" id="evWaSchedule" class="form-input" style="margin-top: 0.5rem;">
                    <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.5rem;">
                        <i class="fa-solid fa-circle-info"></i> Kosongkan jika tidak ingin mengirim pengingat. Jika diisi, silakan pilih kontak di bawah ini.
                    </p>

                    <!-- Contact Selection -->
                    <div id="evWaContactsGroup" style="margin-top: 1rem; display: none;">
                        <label class="form-label" style="font-size: 0.75rem; font-weight: 700; color: var(--text-primary); text-transform: uppercase;">Pilih Penerima WhatsApp</label>
                        <div id="waContactsList" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.5rem; margin-top: 0.5rem; max-height: 150px; overflow-y: auto; padding: 0.8rem; background: #fff; border-radius: 8px; border: 1px solid var(--border-color);">
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Memuat kontak...</div>
                        </div>
                    </div>
                </div>

                @if($user->canManageGlobal())
                <div class="form-group" id="evDeptGroup" style="display: none; margin-left: 52px; margin-top: 0.5rem; background: var(--bg-surface-2); padding: 1rem; border-radius: 12px; border: 1px solid var(--border-color);">
                    <label class="form-label" for="evDeptId" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Pilih Departemen Tujuan</label>
                    <select id="evDeptId" class="form-input">
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    <p style="font-size: 0.65rem; color: var(--text-muted); margin-top: 0.5rem;">
                        <i class="fa-solid fa-circle-info"></i> Sebagai Global Manager, Anda bisa menentukan departemen mana yang berhak melihat event ini.
                    </p>
                </div>
                @endif
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" id="deleteEventBtn" style="display:none; margin-right:auto;">
                <i class="fa-solid fa-trash"></i> Hapus
            </button>
            <button class="btn btn-outline" id="eventCancelBtn">Batal</button>
            <button class="btn btn-primary" id="eventSaveBtn">
                <span id="saveBtnText"><i class="fa-solid fa-floppy-disk"></i> Simpan</span>
                <span id="saveBtnLoading" style="display:none;"><i class="fa-solid fa-spinner fa-spin"></i></span>
            </button>
        </div>
    </div>
</div>

<!-- ─── DETAIL MODAL (Dashboard) ──────────────────────────────────────────── -->
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
            <div id="detailPrivateBadge" style="display: none; margin-bottom: 1.5rem;">
                <span class="badge-role role-admin" style="background: rgba(79, 70, 229, 0.1); color: var(--cal-accent); padding: 0.4rem 0.8rem; font-size: 0.75rem;">
                    <i class="fa-solid fa-eye-slash"></i> Privat Departemen
                </span>
            </div>
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
            <button class="btn btn-primary" id="detailEditBtn" style="display:none;">
                <i class="fa-solid fa-pen"></i> Edit
            </button>
        </div>
    </div>
</div>

<!-- ─── MANAGE USERS MODAL (Admin only) ───────────────────────────────────── -->
@if($user->isAdmin())
<div class="modal-overlay" id="usersModalOverlay" role="dialog" aria-modal="true" aria-labelledby="usersModalTitle">
    <div class="modal modal-wide" id="usersModal">
        <div class="modal-header">
            <h2 class="modal-title" id="usersModalTitle"><i class="fa-solid fa-users-gear"></i> Kelola Editor</h2>
            <button class="modal-close" id="usersModalClose" aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <!-- Add Editor Form -->
            <div class="section-card">
                <h3 class="section-card-title" id="editorFormTitle">Tambah Editor Baru</h3>
                <form id="addEditorForm">
                    <input type="hidden" id="edId">
                    <div class="form-row-2">
                        <div class="form-group">
                            <label class="form-label" for="edName">Nama Akun (Orang)</label>
                            <input type="text" id="edName" class="form-input" placeholder="Nama lengkap" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="edUsername">Username (Untuk Login)</label>
                            <input type="text" id="edUsername" class="form-input" placeholder="Contoh: ppic_admin" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="edEmail">Email</label>
                            <input type="email" id="edEmail" class="form-input" placeholder="email@perusahaan.com" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="edPass">Password</label>
                            <input type="password" id="edPass" class="form-input" placeholder="Bebas" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="edDept">Departemen</label>
                            <select id="edDept" class="form-input" required>
                                <option value="">-- Pilih Departemen --</option>
                            </select>
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="toggle-switch">
                                <input type="checkbox" id="edIsGlobal" checked>
                                <span class="toggle-slider"></span>
                                <span class="toggle-label" style="font-weight: 700; color: var(--text-primary);">Berikan Akses Global (Editor)</span>
                            </label>
                            <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px; margin-left: 52px;">
                                Jika aktif, user bisa melihat semua event dan membuat event untuk departemen mana saja. Jika mati, user hanya bisa melihat event miliknya & umum (Akun Dept).
                            </p>
                        </div>
                    </div>
                    <div class="form-actions" style="display: flex; gap: 0.5rem; align-items: center;">
                        <button type="submit" class="btn btn-primary btn-sm" id="addEditorBtn">
                            <i class="fa-solid fa-user-plus"></i> Tambah Editor
                        </button>
                        <button type="button" class="btn btn-outline btn-sm" id="cancelEditBtn" style="display: none;" onclick="cancelEditUser()">
                            Batal Edit
                        </button>
                    </div>
                </form>
            </div>


            <!-- Editor List -->
            <div class="section-card">
                <h3 class="section-card-title">Daftar Editor</h3>
                <div id="editorList" class="user-list">
                    <div class="loading-spinner"><i class="fa-solid fa-spinner fa-spin"></i> Memuat...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="deptsModalOverlay" role="dialog" aria-modal="true" aria-labelledby="deptsModalTitle">
    <div class="modal" id="deptsModal">
        <div class="modal-header">
            <h2 class="modal-title" id="deptsModalTitle"><i class="fa-solid fa-building-user"></i> Master Departemen</h2>
            <button class="modal-close" id="deptsModalClose" aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div class="section-card">
                <h3 class="section-card-title">Tambah / Edit Nama Departemen</h3>
                <form id="addDeptForm" style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                    <input type="text" id="deptName" class="form-input" placeholder="Nama departemen baru..." required>
                    <button type="submit" class="btn btn-primary btn-sm" id="addDeptBtn">
                        <i class="fa-solid fa-plus"></i> Simpan
                    </button>
                </form>
                <div id="deptList" class="user-list" style="max-height: 400px; overflow-y: auto;">
                    <!-- Loaded via JS -->
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
// ─── Config ─────────────────────────────────────────────────────────────────
const IS_ADMIN = {{ $user->isAdmin() ? 'true' : 'false' }};
const IS_EDITOR = {{ $user->isEditor() ? 'true' : 'false' }};
const CURRENT_USER_ID = {{ $user->id }};
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const colorMap = { blue: '#3b82f6', green: '#10b981', orange: '#f59e0b', red: '#ef4444' };

// ─── Modal Helpers ────────────────────────────────────────────────────────────
function openModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}
function closeAllModals() {
    document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
    document.body.style.overflow = '';
}

// Close on overlay click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) closeAllModals();
    });
});

// ─── FullCalendar ─────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const calEl = document.getElementById('dashboardCalendar');
    let currentEventId = null;

    function loadWaContacts(selectedIds = []) {
        const list = document.getElementById('waContactsList');
        const deptId = document.getElementById('evDeptId')?.value || '';
        
        list.innerHTML = '<div style="font-size: 0.75rem; color: var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Memuat...</div>';
        
        let url = '/api/whatsapp-contacts/all';
        if (deptId && document.getElementById('evIsPrivate').checked) {
            url += '?department_id=' + deptId;
        }

        fetch(url)
            .then(res => res.json())
            .then(contacts => {
                if (contacts.length === 0) {
                    list.innerHTML = '<div style="font-size: 0.75rem; color: var(--text-muted);">Tidak ada kontak tersedia.</div>';
                    return;
                }
                
                list.innerHTML = contacts.map(c => `
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; cursor: pointer; padding: 0.25rem; border-radius: 4px;">
                        <input type="checkbox" class="wa-contact-check" value="${c.id}" ${selectedIds.includes(c.id) ? 'checked' : ''}>
                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${c.name} (${c.phone})">${c.name}</span>
                    </label>
                `).join('');
            })
            .catch(err => {
                console.error('Gagal memuat kontak:', err);
                list.innerHTML = '<div style="font-size: 0.75rem; color: #ef4444;">Gagal memuat kontak.</div>';
            });
    }

    const calendar = new FullCalendar.Calendar(calEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        headerToolbar: false, // Hide default header
        height: 'auto',
        fixedWeekCount: false,
        dayMaxEvents: 3,
        events: '/api/events',
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        dateClick: function(info) {
            document.getElementById('eventForm').reset();
            document.getElementById('eventId').value = '';
            document.getElementById('evWaSchedule').value = '';
            document.getElementById('evWaContactsGroup').style.display = 'none';
            document.getElementById('waContactsList').innerHTML = '';
            if(document.getElementById('evDeptGroup')) document.getElementById('evDeptGroup').style.display = 'none';

            document.getElementById('evDate').value = info.dateStr;
            document.getElementById('eventModalTitle').textContent = 'Tambah Event';
            const deleteBtn = document.getElementById('deleteEventBtn');
            if (deleteBtn) deleteBtn.style.display = 'none';
            openModal('eventModalOverlay');
        },
        eventClick: function(info) {
            currentEventId = info.event.id;
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

    // ─── Custom Navigation ──────────────────────────────────────────────────
    document.getElementById('calPrev').addEventListener('click', () => calendar.prev());
    document.getElementById('calNext').addEventListener('click', () => calendar.next());

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

    // ─── Detail Modal ─────────────────────────────────────────────────────────
    function showDetailModal(event) {
        const props = event.extendedProps;
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
        } else { timeEl.style.display = 'none'; }

        const locEl = document.getElementById('detailLocationRow');
        if (props.location) {
            document.getElementById('detailLocation').textContent = props.location;
            locEl.style.display = '';
        } else { locEl.style.display = 'none'; }

        const descEl = document.getElementById('detailDescRow');
        if (props.description) {
            document.getElementById('detailDesc').textContent = props.description;
            descEl.style.display = '';
        } else { descEl.style.display = 'none'; }

        if (props.creator) {
            document.getElementById('detailCreator').textContent = 'Dibuat oleh: ' + props.creator;
            document.getElementById('detailCreatorRow').style.display = '';
        } else { document.getElementById('detailCreatorRow').style.display = 'none'; }

        // Private Badge
        document.getElementById('detailPrivateBadge').style.display = props.department_id ? 'block' : 'none';

        const canEdit = IS_ADMIN || IS_EDITOR || props.created_by == CURRENT_USER_ID;
        document.getElementById('detailEditBtn').style.display = canEdit ? 'inline-flex' : 'none';

        openModal('detailModalOverlay');
    }

    // Edit from detail modal
    document.getElementById('detailEditBtn').addEventListener('click', function() {
        if (!currentEventId) return;
        closeModal('detailModalOverlay');
        fetch('/api/events/' + currentEventId)
            .then(r => r.json())
            .then(event => {
                document.getElementById('eventId').value = event.id;
                document.getElementById('evTitle').value = event.title;
                document.getElementById('evDate').value = event.date;
                document.getElementById('evStart').value = event.start_time || '';
                document.getElementById('evEnd').value = event.end_time || '';
                document.getElementById('evLocation').value = event.location || '';
                document.getElementById('evDesc').value = event.description || '';
                const radio = document.querySelector(`input[name="evColor"][value="${event.color}"]`);
                if (radio) radio.checked = true;
                
                const isPrivate = !!event.department_id;
                document.getElementById('evIsPrivate').checked = isPrivate;
                
                const deptGroup = document.getElementById('evDeptGroup');
                if (deptGroup) {
                    deptGroup.style.display = isPrivate ? 'block' : 'none';
                    if (event.department_id) {
                        document.getElementById('evDeptId').value = event.department_id;
                    }
                }

                document.getElementById('evWaSchedule').value = event.wa_schedule_time || '';
                
                if (event.wa_schedule_time) {
                    document.getElementById('evWaContactsGroup').style.display = 'block';
                    const selectedIds = event.whatsapp_contacts ? event.whatsapp_contacts.map(c => c.id) : [];
                    loadWaContacts(selectedIds);
                } else {
                    document.getElementById('evWaContactsGroup').style.display = 'none';
                }

                document.getElementById('eventModalTitle').textContent = 'Edit Event';
                const deleteBtn = document.getElementById('deleteEventBtn');
                if (deleteBtn) {
                    const canDelete = IS_ADMIN || IS_EDITOR || event.created_by == CURRENT_USER_ID;
                    deleteBtn.style.display = canDelete ? 'inline-flex' : 'none';
                }
                openModal('eventModalOverlay');
            });
    });

    // ─── Save Event ────────────────────────────────────────────────────────────
    document.getElementById('eventSaveBtn').addEventListener('click', function() {
        const id = document.getElementById('eventId').value;
        const title = document.getElementById('evTitle').value.trim();
        const date = document.getElementById('evDate').value;

        if (!title || !date) {
            alert('Judul dan tanggal wajib diisi!');
            return;
        }

        const startTime = document.getElementById('evStart').value;
        const endTime = document.getElementById('evEnd').value;

        const payload = {
            title,
            date,
            start_time: startTime ? startTime.substring(0, 5) : null,
            end_time: endTime ? endTime.substring(0, 5) : null,
            location: document.getElementById('evLocation').value || null,
            description: document.getElementById('evDesc').value || null,
            color: document.querySelector('input[name="evColor"]:checked')?.value || 'blue',
            is_private: document.getElementById('evIsPrivate').checked ? 1 : 0,
            department_id: document.getElementById('evDeptId') ? document.getElementById('evDeptId').value : null,
            wa_schedule_time: document.getElementById('evWaSchedule').value || null,
            whatsapp_contact_ids: Array.from(document.querySelectorAll('.wa-contact-check:checked')).map(el => el.value),
            _token: CSRF,
        };

        document.getElementById('saveBtnText').style.display = 'none';
        document.getElementById('saveBtnLoading').style.display = 'inline';
        document.getElementById('eventSaveBtn').disabled = true;

        const isEdit = id !== '';
        const url    = isEdit ? '/api/events/' + id : '/api/events';
        const method = isEdit ? 'PATCH' : 'POST';

        fetch(url, {
            method: method,
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload),
        })
        .then(r => r.json().then(data => ({ status: r.status, ok: r.ok, data })))
        .then(res => {
            if (res.ok) {
                closeModal('eventModalOverlay');
                calendar.refetchEvents();
            } else {
                const errorMsg = res.data.message || 'Terjadi kesalahan. Coba lagi.';
                alert('Gagal: ' + errorMsg);
                if (res.data.errors) {
                    console.error('Validation Errors:', res.data.errors);
                }
            }
        })
        .catch((err) => {
            console.error('Fetch error:', err);
            alert('Gagal terhubung ke server. Silakan muat ulang halaman.');
        })
        .finally(() => {
            document.getElementById('saveBtnText').style.display = 'inline';
            document.getElementById('saveBtnLoading').style.display = 'none';
            document.getElementById('eventSaveBtn').disabled = false;
        });
    });

    // ─── Delete Event (Admin) ──────────────────────────────────────────────────
    const deleteBtn = document.getElementById('deleteEventBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            const id = document.getElementById('eventId').value;
            if (!id) return;
            if (!confirm('Hapus event ini? Tindakan tidak dapat dibatalkan.')) return;

            fetch('/api/events/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
                body: JSON.stringify({ _method: 'DELETE' }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    closeModal('eventModalOverlay');
                    calendar.refetchEvents();
                }
            });
        });
    }

    // ─── Add Event Button ─────────────────────────────────────────────────────
    document.getElementById('addEventBtn').addEventListener('click', function() {
        document.getElementById('eventForm').reset();
        document.getElementById('eventId').value = '';
        document.getElementById('evWaSchedule').value = '';
        document.getElementById('evWaContactsGroup').style.display = 'none';
        document.getElementById('waContactsList').innerHTML = '';
        if(document.getElementById('evDeptGroup')) document.getElementById('evDeptGroup').style.display = 'none';

        document.getElementById('evDate').value = new Date().toISOString().split('T')[0];
        document.getElementById('eventModalTitle').textContent = 'Tambah Event';
        const delBtn = document.getElementById('deleteEventBtn');
        if (delBtn) delBtn.style.display = 'none';
        openModal('eventModalOverlay');
    });

    // Toggle Dept Selection for Global Users
    const evIsPrivate = document.getElementById('evIsPrivate');
    const evDeptGroup = document.getElementById('evDeptGroup');
    if (evIsPrivate && evDeptGroup) {
        evIsPrivate.addEventListener('change', function() {
            evDeptGroup.style.display = this.checked ? 'block' : 'none';
            if (document.getElementById('evWaSchedule').value) {
                loadWaContacts();
            }
        });
    }

    // WA Schedule event listeners
    document.getElementById('evWaSchedule').addEventListener('input', function() {
        const group = document.getElementById('evWaContactsGroup');
        if (this.value) {
            group.style.display = 'block';
            loadWaContacts();
        } else {
            group.style.display = 'none';
        }
    });

    if (document.getElementById('evDeptId')) {
        document.getElementById('evDeptId').addEventListener('change', function() {
            if (document.getElementById('evWaSchedule').value) {
                loadWaContacts();
            }
        });
    }

    // ─── Modal close buttons ──────────────────────────────────────────────────
    document.getElementById('eventModalClose').addEventListener('click', () => closeModal('eventModalOverlay'));
    document.getElementById('eventCancelBtn').addEventListener('click', () => closeModal('eventModalOverlay'));
    document.getElementById('detailModalClose').addEventListener('click', () => closeModal('detailModalOverlay'));
    document.getElementById('detailCloseBtn').addEventListener('click', () => closeModal('detailModalOverlay'));

    // ─── Manage Users (Admin) ───────────────────────────────────────────────
    @if($user->isAdmin())
    const manageBtn = document.getElementById('manageUsersBtn');
    const manageBtnSidebar = document.getElementById('manageUsersBtnSidebar');
    
    const openUserManagement = function(e) {
        if(e) e.preventDefault();
        loadDepartments();
        loadEditors();
        openModal('usersModalOverlay');
    };

    if (manageBtn) manageBtn.addEventListener('click', openUserManagement);
    if (manageBtnSidebar) manageBtnSidebar.addEventListener('click', openUserManagement);

    const manageDeptsBtnSidebar = document.getElementById('manageDeptsBtnSidebar');
    if (manageDeptsBtnSidebar) {
        manageDeptsBtnSidebar.addEventListener('click', function(e) {
            e.preventDefault();
            loadDepartments();
            openModal('deptsModalOverlay');
        });
    }

    document.getElementById('usersModalClose').addEventListener('click', () => closeModal('usersModalOverlay'));
    if (document.getElementById('deptsModalClose')) {
        document.getElementById('deptsModalClose').addEventListener('click', () => closeModal('deptsModalOverlay'));
    }

    // Auto-fill email from username
    document.getElementById('edUsername').addEventListener('input', function() {
        const username = this.value.trim().toLowerCase().replace(/\s+/g, '');
        if (username) {
            document.getElementById('edEmail').value = username + '@kalender.com';
        }
    });

    function loadEditors() {
        const list = document.getElementById('editorList');
        list.innerHTML = '<div class="loading-spinner"><i class="fa-solid fa-spinner fa-spin"></i> Memuat...</div>';
        fetch('/api/admin/users')
            .then(r => r.json())
            .then(users => {
                if (users.length === 0) {
                    list.innerHTML = '<p class="empty-state">Belum ada editor terdaftar.</p>';
                    return;
                }
                list.innerHTML = users.map(u => `
                    <div class="user-list-item" id="userItem-${u.id}">
                        <div class="user-list-avatar">${u.name.charAt(0).toUpperCase()}</div>
                        <div class="user-list-info">
                            <strong>${u.name}</strong>
                            <span style="color: var(--cal-accent); font-weight: 600;">@${u.username}</span>
                            <span>${u.email}</span>
                        </div>
                        <div style="text-align: right; margin-right: 1rem;">
                            <span class="badge-role role-${u.role}" style="display: block; margin-bottom: 2px;">
                                ${u.role === 'admin' ? 'Admin' : (u.role === 'editor' ? 'Editor' : 'Akun Dept')}
                            </span>
                            <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">
                                ${u.department ? u.department.name : 'No Dept'}
                            </span>
                        </div>
                        <div style="display: flex; gap: 0.4rem;">
                            <button class="btn btn-outline btn-xs" onclick='editUser(${JSON.stringify(u)})'>
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button class="btn btn-danger btn-xs" onclick="removeEditor(${u.id})">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
            });
    }

    // ─── Department Management Logic ──────────────────────────────────────────
    function loadDepartments() {
        const list = document.getElementById('deptList');
        const select = document.getElementById('edDept');
        
        fetch('/api/admin/departments')
            .then(r => r.json())
            .then(depts => {
                // Update Master List
                list.innerHTML = depts.map(d => `
                    <div class="user-list-item" style="padding: 0.5rem 1rem;">
                        <div class="user-list-info"><strong>${d.name}</strong></div>
                        <div style="display: flex; gap: 0.4rem;">
                            <button class="btn btn-outline btn-xs" title="Edit Nama" onclick="editDepartment(${d.id}, '${d.name}')">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button class="btn btn-danger btn-xs" title="Hapus" onclick="removeDepartment(${d.id})">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                `).join('');

                // Update Dropdown in Editor Form
                const currentVal = select.value;
                select.innerHTML = '<option value="">-- Pilih Departemen --</option>' + 
                    depts.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
                select.value = currentVal;
            });
    }

    window.removeDepartment = function(id) {
        if(!confirm('Hapus departemen ini?')) return;
        fetch('/api/admin/departments/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF }
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) loadDepartments();
            else alert(data.error || 'Gagal menghapus departemen.');
        });
    };

    window.editDepartment = function(id, currentName) {
        const newName = prompt('Ubah nama departemen:', currentName);
        if (newName && newName !== currentName) {
            fetch('/api/admin/departments/' + id, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ name: newName })
            })
            .then(r => r.json())
            .then(data => {
                if(data.success) loadDepartments();
                else alert(data.error || 'Gagal mengubah departemen.');
            });
        }
    };

    document.getElementById('addDeptForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const input = document.getElementById('deptName');
        fetch('/api/admin/departments', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ name: input.value })
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                input.value = '';
                loadDepartments();
            } else {
                alert('Gagal menambah departemen.');
            }
        });
    });

    window.removeEditor = function(id) {
        if (!confirm('Yakin hapus editor ini?')) return;
        fetch('/api/admin/users/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('userItem-' + id)?.remove();
            }
        });
    };

    document.getElementById('addEditorForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('edId').value;
        const btn = document.getElementById('addEditorBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

        const url = id ? `/api/admin/users/${id}` : '/api/admin/users';
        const method = id ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({
                name:            document.getElementById('edName').value,
                username:        document.getElementById('edUsername').value,
                email:           document.getElementById('edEmail').value,
                password:        document.getElementById('edPass').value,
                role:            document.getElementById('edIsGlobal').checked ? 'editor' : 'user',
                department_id:   document.getElementById('edDept').value,
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                cancelEditUser();
                loadEditors();
            } else {
                alert('Gagal menyimpan editor: ' + (data.message || 'Cek data kembali.'));
            }
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-user-plus"></i> Tambah Editor';
        });
    });

    window.editUser = function(u) {
        document.getElementById('edId').value = u.id;
        document.getElementById('edName').value = u.name;
        document.getElementById('edUsername').value = u.username;
        document.getElementById('edEmail').value = u.email;
        document.getElementById('edIsGlobal').checked = u.role === 'editor';
        document.getElementById('edDept').value = u.department ? u.department.id : '';
        document.getElementById('edPass').value = '';
        document.getElementById('edPass').required = false; // Pass optional on edit
        document.getElementById('edPass').placeholder = 'Kosongkan jika tidak ganti';

        document.getElementById('editorFormTitle').textContent = 'Edit Akun Editor';
        document.getElementById('addEditorBtn').innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan';
        document.getElementById('cancelEditBtn').style.display = 'block';
    };

    window.cancelEditUser = function() {
        document.getElementById('edId').value = '';
        document.getElementById('edName').value = '';
        document.getElementById('edUsername').value = '';
        document.getElementById('edEmail').value = '';
        document.getElementById('edPass').value = '';
        document.getElementById('edIsGlobal').checked = true;
        document.getElementById('edDept').value = '';
        document.getElementById('edPass').required = true;
        document.getElementById('edPass').placeholder = 'Bebas';

        document.getElementById('editorFormTitle').textContent = 'Tambah Editor Baru';
        document.getElementById('addEditorBtn').innerHTML = '<i class="fa-solid fa-user-plus"></i> Tambah Editor';
        document.getElementById('cancelEditBtn').style.display = 'none';
    };

    // Auto-open modals via URL params
    const urlParams = new URLSearchParams(window.location.search);
    const modalParam = urlParams.get('modal');
    if (modalParam === 'users') {
        openUserManagement();
    } else if (modalParam === 'depts') {
        loadDepartments();
        openModal('deptsModalOverlay');
    }
    @endif

    // ─── Reset Form ────────────────────────────────────────────────────────────
    function resetForm() {
        document.getElementById('eventId').value = '';
        document.getElementById('evTitle').value = '';
        document.getElementById('evDate').value = '';
        document.getElementById('evStart').value = '';
        document.getElementById('evEnd').value = '';
        document.getElementById('evLocation').value = '';
        document.getElementById('evDesc').value = '';
        document.querySelector('input[name="evColor"][value="blue"]').checked = true;
        document.getElementById('evIsPrivate').checked = false;
        const deptGroup = document.getElementById('evDeptGroup');
        if (deptGroup) deptGroup.style.display = 'none';
    }
});
</script>
@endpush
