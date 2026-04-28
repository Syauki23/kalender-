@extends('layouts.app')

@section('title', 'Manajer Pengingat WA')

@push('head')
<style>
    .wa-manager-layout {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 1.5rem;
        height: calc(100vh - 100px);
    }
    
    @media (max-width: 992px) {
        .wa-manager-layout {
            grid-template-columns: 1fr;
            height: auto;
        }
    }

    .wa-panel {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .wa-panel-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border-color);
        background: var(--bg-surface-2);
    }

    .wa-panel-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .wa-panel-body {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
    }

    /* Event List Styles */
    .event-list-item {
        padding: 1rem;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        margin-bottom: 0.75rem;
        cursor: pointer;
        transition: all 0.2s;
        background: var(--bg-surface-2);
    }

    .event-list-item:hover {
        border-color: #128c7e;
        transform: translateY(-2px);
    }

    .event-list-item.active {
        border-color: #128c7e;
        background: rgba(37, 211, 102, 0.05);
        box-shadow: 0 4px 12px rgba(18, 140, 126, 0.1);
    }

    .evt-title {
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .evt-meta {
        font-size: 0.8rem;
        color: var(--text-muted);
        display: flex;
        gap: 1rem;
    }

    /* Detail Section Styles */
    .detail-card {
        background: var(--bg-surface-2);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .section-label {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .contact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 0.75rem;
    }

    .contact-card {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        padding: 0.75rem;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .contact-card:hover {
        border-color: #128c7e;
    }

    .contact-card input[type="checkbox"] {
        accent-color: #128c7e;
        width: 16px;
        height: 16px;
    }

    .reminder-row {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 0.75rem;
        background: var(--bg-surface);
        padding: 0.75rem;
        border-radius: 8px;
        border: 1px solid var(--border-color);
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--text-muted);
    }

    .empty-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: var(--border-color);
    }

    /* Select2 Premium Styling */
    .select2-container--default .select2-selection--multiple {
        background-color: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        min-height: 50px;
        padding: 4px 8px;
        transition: all 0.3s;
    }

    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #128c7e;
        box-shadow: 0 0 0 4px rgba(18, 140, 126, 0.1);
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #128c7e;
        border: none;
        color: white;
        border-radius: 8px;
        padding: 4px 10px 4px 24px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-top: 6px;
        position: relative;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: rgba(255, 255, 255, 0.8);
        border: none;
        left: 8px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 14px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        background: transparent;
        color: white;
    }

    .select2-dropdown {
        background-color: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        overflow: hidden;
        z-index: 1060;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #128c7e;
    }

    .select2-results__option {
        padding: 10px 15px;
        font-size: 0.9rem;
        color: var(--text-primary);
    }

    .select2-results__group {
        background: var(--bg-surface-2);
        color: var(--text-muted);
        font-weight: 800;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 8px 15px;
    }

    .select2-container--default .select2-search--inline .select2-search__field {
        color: var(--text-primary);
        font-family: inherit;
    }

    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: var(--bg-surface-2);
        color: var(--text-muted);
    }

    [data-theme="dark"] .select2-results__option--selectable {
        color: #e2e8f0;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="page-header" style="margin-bottom: 1.5rem;">
    <div>
        <h1 class="page-title"><i class="fa-brands fa-whatsapp" style="color: #25D366;"></i> Manajer Pengingat WA</h1>
        <p class="page-subtitle">Atur jadwal pengingat WhatsApp ganda untuk setiap event.</p>
    </div>
</div>

<div class="wa-manager-layout">
    <!-- Kiri: Daftar Event -->
    <div class="wa-panel">
        <div class="wa-panel-header">
            <h2 class="wa-panel-title"><i class="fa-solid fa-calendar-check"></i> Event Mendatang</h2>
        </div>
        <div class="wa-panel-body" id="eventListContainer" style="padding: 1rem;">
            <div class="empty-state">
                <i class="fa-solid fa-spinner fa-spin empty-icon"></i>
                <p>Memuat event...</p>
            </div>
        </div>
    </div>

    <!-- Kanan: Detail & Konfigurasi -->
    <div class="wa-panel">
        <div class="wa-panel-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="wa-panel-title"><i class="fa-solid fa-sliders"></i> Konfigurasi Pengingat</h2>
            <button class="btn btn-primary btn-sm" id="btnSaveConfig" style="display: none;">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Pengaturan
            </button>
        </div>
        <div class="wa-panel-body" id="configContainer">
            <div class="empty-state" style="margin-top: 5rem;">
                <i class="fa-solid fa-hand-pointer empty-icon"></i>
                <p>Pilih event di sebelah kiri untuk mengatur pengingat WhatsApp.</p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const eventListContainer = document.getElementById('eventListContainer');
    const configContainer = document.getElementById('configContainer');
    const btnSaveConfig = document.getElementById('btnSaveConfig');
    
    let activeEventId = null;
    let allContacts = [];

    // 1. Load Data Awal
    loadEvents();
    loadAllContacts();

    function loadAllContacts() {
        fetch('/api/whatsapp-contacts/all')
            .then(r => r.json())
            .then(data => {
                allContacts = data;
            });
    }

    function loadEvents() {
        fetch('/api/wa-manager/events')
            .then(r => r.json())
            .then(events => {
                if (events.length === 0) {
                    eventListContainer.innerHTML = `
                        <div class="empty-state">
                            <i class="fa-solid fa-calendar-xmark empty-icon"></i>
                            <p>Tidak ada event mendatang.</p>
                        </div>
                    `;
                    return;
                }

                eventListContainer.innerHTML = events.map(ev => {
                    const date = new Date(ev.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                    const time = ev.start_time ? ev.start_time.substring(0,5) : 'All Day';
                    return `
                        <div class="event-list-item" data-id="${ev.id}" onclick="selectEvent(${ev.id})">
                            <div class="evt-title">${ev.title}</div>
                            <div class="evt-meta">
                                <span><i class="fa-regular fa-calendar"></i> ${date}</span>
                                <span><i class="fa-regular fa-clock"></i> ${time}</span>
                            </div>
                        </div>
                    `;
                }).join('');
            });
    }

    window.selectEvent = function(id) {
        activeEventId = id;
        
        // Update UI active state
        document.querySelectorAll('.event-list-item').forEach(el => el.classList.remove('active'));
        document.querySelector(`.event-list-item[data-id="${id}"]`).classList.add('active');

        // Show loading in config
        configContainer.innerHTML = `
            <div class="empty-state">
                <i class="fa-solid fa-spinner fa-spin empty-icon"></i>
                <p>Memuat detail...</p>
            </div>
        `;
        btnSaveConfig.style.display = 'none';

        // Fetch detail
        fetch(`/api/wa-manager/events/${id}`)
            .then(r => r.json())
            .then(event => {
                renderConfigUI(event);
                btnSaveConfig.style.display = 'inline-flex';
            });
    };

    function renderConfigUI(event) {
        const selectedContactIds = event.whatsapp_contacts ? event.whatsapp_contacts.map(c => c.id) : [];
        const reminders = event.reminders || [];

        // Header Info
        const dateStr = new Date(event.date).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        
        let html = `
            <div class="detail-card" style="border-left: 4px solid ${event.color || '#3b82f6'};">
                <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--text-primary);">${event.title}</h3>
                <div style="font-size: 0.85rem; color: var(--text-muted); display: flex; gap: 1.5rem;">
                    <span><i class="fa-solid fa-calendar-day"></i> ${dateStr}</span>
                    <span><i class="fa-solid fa-clock"></i> ${event.start_time ? event.start_time.substring(0,5) + ' WIB' : 'TBA'}</span>
                </div>
            </div>
        `;

        // 1. Pilih Orang (Grouped by Dept)
        html += `
            <div class="detail-card">
                <div class="section-label"><i class="fa-solid fa-users"></i> 1. Pilih Penerima WhatsApp</div>
                <div style="margin-bottom: 0.5rem;">
                    <select id="contactSelect" multiple="multiple" style="width: 100%;">
        `;
        
        if (allContacts.length === 0) {
            html += `<option disabled>Belum ada kontak terdaftar</option>`;
        } else {
            // Group contacts by department
            const grouped = {};
            allContacts.forEach(c => {
                const deptName = c.department ? c.department.name : 'Tanpa Departemen';
                if (!grouped[deptName]) grouped[deptName] = [];
                grouped[deptName].push(c);
            });

            Object.keys(grouped).forEach(deptName => {
                html += `<optgroup label="${deptName}">`;
                grouped[deptName].forEach(c => {
                    const isSelected = selectedContactIds.includes(c.id) ? 'selected' : '';
                    html += `<option value="${c.id}" ${isSelected}>${c.name} (${c.phone})</option>`;
                });
                html += `</optgroup>`;
            });
        }
        html += `
                    </select>
                </div>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">
                    <i class="fa-solid fa-circle-info"></i> Ketik nama atau departemen untuk mencari.
                </p>
            </div>
        `;

        // 2. Waktu Pengingat
        html += `
            <div class="detail-card">
                <div class="section-label" style="justify-content: space-between;">
                    <span><i class="fa-solid fa-clock-rotate-left"></i> 2. Atur Waktu Pengingat</span>
                    <button type="button" class="btn btn-outline btn-xs" onclick="addReminderRow()">
                        <i class="fa-solid fa-plus"></i> Tambah Waktu
                    </button>
                </div>
                <div id="reminderRowsContainer">
        `;

        if (reminders.length === 0) {
            html += `
                <div class="empty-state" id="noReminderText" style="padding: 1.5rem; margin-bottom: 0;">
                    <p style="font-size: 0.85rem;">Belum ada jadwal pengingat.</p>
                </div>
            `;
        } else {
            reminders.forEach(r => {
                // Konversi format API (Y-m-d H:i:s) ke input datetime-local (Y-m-dTH:i)
                const t = r.schedule_time.replace(' ', 'T').substring(0, 16);
                html += createReminderRowHtml(t);
            });
        }

        html += `</div></div>`;

        configContainer.innerHTML = html;

        // Initialize Select2 after rendering
        $('#contactSelect').select2({
            placeholder: "Cari penerima...",
            allowClear: true,
            language: {
                noResults: function() { return "Kontak tidak ditemukan"; }
            }
        });
    }

    window.addReminderRow = function() {
        const noText = document.getElementById('noReminderText');
        if (noText) noText.remove();

        const container = document.getElementById('reminderRowsContainer');
        const div = document.createElement('div');
        div.innerHTML = createReminderRowHtml('');
        container.appendChild(div.firstElementChild);
    };

    window.removeReminderRow = function(btn) {
        btn.closest('.reminder-row').remove();
        const container = document.getElementById('reminderRowsContainer');
        if (container.children.length === 0) {
            container.innerHTML = `
                <div class="empty-state" id="noReminderText" style="padding: 1.5rem; margin-bottom: 0;">
                    <p style="font-size: 0.85rem;">Belum ada jadwal pengingat.</p>
                </div>
            `;
        }
    };

    function createReminderRowHtml(value) {
        return `
            <div class="reminder-row">
                <input type="datetime-local" class="form-input reminder-input" value="${value}" required style="flex: 1;">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeReminderRow(this)" title="Hapus jadwal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        `;
    }

    // Save Action
    btnSaveConfig.addEventListener('click', function() {
        if (!activeEventId) return;

        const selectedContacts = $('#contactSelect').val() || [];
        const reminderInputs = Array.from(document.querySelectorAll('.reminder-input'));
        
        // Validasi input
        let validReminders = [];
        let hasError = false;
        reminderInputs.forEach(inp => {
            if (!inp.value) hasError = true;
            else validReminders.push(inp.value);
        });

        if (hasError) {
            alert('Harap isi semua input waktu pengingat atau hapus yang tidak perlu.');
            return;
        }

        if (validReminders.length > 0 && selectedContacts.length === 0) {
            alert('Pilih minimal satu penerima pesan WA!');
            return;
        }

        btnSaveConfig.disabled = true;
        btnSaveConfig.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

        fetch(`/api/wa-manager/events/${activeEventId}/reminders`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                whatsapp_contact_ids: selectedContacts,
                reminders: validReminders
            })
        })
        .then(r => r.json().then(data => ({ status: r.status, ok: r.ok, data })))
        .then(res => {
            if (res.ok) {
                alert('Berhasil menyimpan jadwal pengingat WA!');
                selectEvent(activeEventId); // Refresh detail
            } else {
                alert('Gagal: ' + (res.data.error || res.data.message || 'Terjadi kesalahan'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Kesalahan jaringan.');
        })
        .finally(() => {
            btnSaveConfig.disabled = false;
            btnSaveConfig.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Simpan Pengaturan';
        });
    });

});
</script>
@endpush
