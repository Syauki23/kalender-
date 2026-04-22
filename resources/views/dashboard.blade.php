@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div class="page-header-text">
            <h1 class="page-title">Dashboard Kalender</h1>
            <p class="page-subtitle">Selamat datang, <strong>{{ $user->name }}</strong>
                <span class="badge-role role-{{ $user->role }}">{{ ucfirst($user->role) }}</span>
            </p>
        </div>
        <div class="page-header-actions">
            <button class="btn btn-primary" id="addEventBtn">
                <i class="fa-solid fa-plus"></i> Tambah Event
            </button>
            @if($user->isAdmin())
            <button class="btn btn-outline-primary" id="manageUsersBtn">
                <i class="fa-solid fa-users-gear"></i> Kelola Editor
            </button>
            @endif
        </div>
    </div>
</div>

<!-- Calendar Wrapper -->
<div class="calendar-wrapper">
    <div id="dashboardCalendar"></div>
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
            </form>
        </div>
        <div class="modal-footer">
            @if($user->isAdmin())
            <button class="btn btn-danger" id="deleteEventBtn" style="display:none; margin-right:auto;">
                <i class="fa-solid fa-trash"></i> Hapus
            </button>
            @endif
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
            <button class="btn btn-primary" id="detailEditBtn">
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
                <h3 class="section-card-title">Tambah Editor Baru</h3>
                <form id="addEditorForm">
                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label" for="edName">Nama</label>
                            <input type="text" id="edName" class="form-input" placeholder="Nama lengkap" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="edEmail">Email</label>
                            <input type="email" id="edEmail" class="form-input" placeholder="email@perusahaan.com" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="edPass">Password</label>
                            <input type="password" id="edPass" class="form-input" placeholder="Min. 8 karakter" required minlength="8">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm" id="addEditorBtn">
                        <i class="fa-solid fa-user-plus"></i> Tambah Editor
                    </button>
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
@endif
@endsection

@push('scripts')
<script>
// ─── Config ─────────────────────────────────────────────────────────────────
const IS_ADMIN = {{ $user->isAdmin() ? 'true' : 'false' }};
const IS_EDITOR = {{ $user->isEditor() ? 'true' : 'false' }};
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

    const calendar = new FullCalendar.Calendar(calEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        buttonText: { today: 'Hari Ini', month: 'Bulan', week: 'Minggu', list: 'Daftar' },
        height: 'auto',
        fixedWeekCount: false,
        dayMaxEvents: 3,
        events: '/api/events',
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        dateClick: function(info) {
            resetForm();
            document.getElementById('evDate').value = info.dateStr;
            document.getElementById('eventModalTitle').textContent = 'Tambah Event';
            const deleteBtn = document.getElementById('deleteEventBtn');
            if (deleteBtn) deleteBtn.style.display = 'none';
            openModal('eventModalOverlay');
        },
        eventClick: function(info) {
            currentEventId = info.event.id;
            showDetailModal(info.event);
        }
    });

    calendar.render();

    // ─── Detail Modal ─────────────────────────────────────────────────────────
    function showDetailModal(event) {
        const props = event.extendedProps;
        document.getElementById('detailColorBar').style.background = colorMap[props.color_label] || '#3b82f6';
        document.getElementById('detailTitle').textContent = event.title;

        const dateStr = event.start ? event.start.toLocaleDateString('id-ID', {weekday:'long', year:'numeric', month:'long', day:'numeric'}) : '-';
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

                document.getElementById('eventModalTitle').textContent = 'Edit Event';
                const deleteBtn = document.getElementById('deleteEventBtn');
                if (deleteBtn) deleteBtn.style.display = IS_ADMIN ? 'inline-flex' : 'none';
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

        const payload = {
            title,
            date,
            start_time: document.getElementById('evStart').value || null,
            end_time: document.getElementById('evEnd').value || null,
            location: document.getElementById('evLocation').value || null,
            description: document.getElementById('evDesc').value || null,
            color: document.querySelector('input[name="evColor"]:checked')?.value || 'blue',
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
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(payload),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                closeModal('eventModalOverlay');
                calendar.refetchEvents();
            } else {
                alert('Terjadi kesalahan. Coba lagi.');
            }
        })
        .catch(() => alert('Gagal terhubung ke server.'))
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
        resetForm();
        document.getElementById('evDate').value = new Date().toISOString().split('T')[0];
        document.getElementById('eventModalTitle').textContent = 'Tambah Event';
        const delBtn = document.getElementById('deleteEventBtn');
        if (delBtn) delBtn.style.display = 'none';
        openModal('eventModalOverlay');
    });

    // ─── Modal close buttons ──────────────────────────────────────────────────
    document.getElementById('eventModalClose').addEventListener('click', () => closeModal('eventModalOverlay'));
    document.getElementById('eventCancelBtn').addEventListener('click', () => closeModal('eventModalOverlay'));
    document.getElementById('detailModalClose').addEventListener('click', () => closeModal('detailModalOverlay'));
    document.getElementById('detailCloseBtn').addEventListener('click', () => closeModal('detailModalOverlay'));

    // ─── Manage Users (Admin) ───────────────────────────────────────────────
    @if($user->isAdmin())
    const manageBtn = document.getElementById('manageUsersBtn');
    if (manageBtn) {
        manageBtn.addEventListener('click', function() {
            loadEditors();
            openModal('usersModalOverlay');
        });
    }
    document.getElementById('usersModalClose').addEventListener('click', () => closeModal('usersModalOverlay'));

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
                            <span>${u.email}</span>
                        </div>
                        <span class="badge-role role-editor">Editor</span>
                        <button class="btn btn-danger btn-xs" onclick="removeEditor(${u.id})">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                `).join('');
            });
    }

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
        const btn = document.getElementById('addEditorBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

        fetch('/api/admin/users', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({
                name:     document.getElementById('edName').value,
                email:    document.getElementById('edEmail').value,
                password: document.getElementById('edPass').value,
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('edName').value = '';
                document.getElementById('edEmail').value = '';
                document.getElementById('edPass').value = '';
                loadEditors();
            } else {
                alert('Gagal menambah editor: ' + (data.message || 'Cek data kembali.'));
            }
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-user-plus"></i> Tambah Editor';
        });
    });
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
    }
});
</script>
@endpush
