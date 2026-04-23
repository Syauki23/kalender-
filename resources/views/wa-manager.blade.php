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
</style>
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

        // 1. Pilih Orang
        html += `
            <div class="detail-card">
                <div class="section-label"><i class="fa-solid fa-users"></i> 1. Pilih Penerima WhatsApp</div>
                <div class="contact-grid">
        `;
        
        if (allContacts.length === 0) {
            html += `<p style="font-size: 0.85rem; color: #ef4444;">Belum ada kontak terdaftar di sistem.</p>`;
        } else {
            allContacts.forEach(c => {
                const isChecked = selectedContactIds.includes(c.id) ? 'checked' : '';
                html += `
                    <label class="contact-card">
                        <input type="checkbox" class="contact-checkbox" value="${c.id}" ${isChecked}>
                        <div style="overflow: hidden;">
                            <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${c.name}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">${c.phone}</div>
                        </div>
                    </label>
                `;
            });
        }
        html += `</div></div>`;

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

        const selectedContacts = Array.from(document.querySelectorAll('.contact-checkbox:checked')).map(cb => cb.value);
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
