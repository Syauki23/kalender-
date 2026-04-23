@extends('layouts.app')

@section('title', 'Kontak WhatsApp (Per Departemen)')

@section('content')
<div class="calendar-redesign-wrapper" style="padding: 2rem;">
    <div style="max-width: 1000px; margin: 0 auto;">
        
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
            <div>
                <h1 style="font-size: 1.8rem; font-weight: 800; color: var(--text-primary); margin: 0;">
                    <i class="fa-brands fa-whatsapp" style="color: #25D366;"></i> Kontak WhatsApp
                </h1>
                <p style="color: var(--text-muted); margin: 0.5rem 0 0 0; font-size: 0.95rem;">
                    Kelola kontak penerima notifikasi pengingat event per departemen.
                </p>
            </div>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem; align-items: start;">
            
            <!-- Pilih Departemen -->
            <div class="section-card" style="margin: 0; position: sticky; top: 100px;">
                <h3 class="section-card-title">1. Pilih Departemen</h3>
                <div class="user-list" style="max-height: 400px; overflow-y: auto;">
                    @foreach($departments as $dept)
                    <div class="user-list-item dept-selector" data-id="{{ $dept->id }}" data-name="{{ $dept->name }}" style="cursor: pointer; transition: 0.2s;">
                        <div class="user-list-info">
                            <strong style="display: block;">{{ $dept->name }}</strong>
                        </div>
                        <div><i class="fa-solid fa-chevron-right" style="color: var(--text-muted);"></i></div>
                    </div>
                    @endforeach
                    @if($departments->isEmpty())
                        <div class="empty-state">Belum ada departemen.</div>
                    @endif
                </div>
            </div>

            <!-- Daftar Kontak -->
            <div class="section-card" style="margin: 0; display: none;" id="contactSection">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3 class="section-card-title" style="margin: 0;">
                        Kontak <span id="selectedDeptName" style="color: var(--cal-accent);"></span>
                    </h3>
                    <button class="btn btn-primary btn-sm" id="btnAddContact">
                        <i class="fa-solid fa-plus"></i> Tambah
                    </button>
                </div>
                
                <div id="contactList" class="user-list">
                    <!-- Dinamis via JS -->
                </div>
            </div>

            <div class="section-card" id="contactPlaceholder" style="margin: 0; display: flex; align-items: center; justify-content: center; min-height: 200px; text-align: center; color: var(--text-muted);">
                <div>
                    <i class="fa-solid fa-hand-pointer" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p>Silakan pilih departemen terlebih dahulu<br>untuk melihat atau menambah kontak WhatsApp.</p>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Kontak -->
<div class="modal-overlay" id="contactModalOverlay" role="dialog" aria-modal="true">
    <div class="modal" id="contactModal">
        <div class="modal-header">
            <h2 class="modal-title" id="contactModalTitle">Tambah Kontak WA</h2>
            <button class="modal-close" id="contactModalClose"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <form id="contactForm">
                <input type="hidden" id="cId">
                <input type="hidden" id="cDeptId">
                
                <div class="form-group">
                    <label class="form-label" for="cName">Nama Kontak <span class="required">*</span></label>
                    <input type="text" id="cName" class="form-input" placeholder="Nama lengkap..." required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="cPhone">Nomor WhatsApp <span class="required">*</span></label>
                    <input type="text" id="cPhone" class="form-input" placeholder="081234567890" required>
                    <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.25rem;">Gunakan format nomor lokal atau internasional (contoh: 0812... atau 62812...)</p>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" id="contactCancelBtn">Batal</button>
            <button class="btn btn-primary" id="contactSaveBtn">
                <span id="saveContactText"><i class="fa-solid fa-floppy-disk"></i> Simpan</span>
                <span id="saveContactLoading" style="display:none;"><i class="fa-solid fa-spinner fa-spin"></i></span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
let currentDeptId = null;

const selectors = document.querySelectorAll('.dept-selector');
selectors.forEach(sel => {
    sel.addEventListener('click', function() {
        // Reset styles
        selectors.forEach(s => s.style.background = 'transparent');
        this.style.background = 'var(--bg-surface-2)';
        
        currentDeptId = this.dataset.id;
        document.getElementById('selectedDeptName').textContent = '- ' + this.dataset.name;
        
        document.getElementById('contactPlaceholder').style.display = 'none';
        document.getElementById('contactSection').style.display = 'block';
        
        loadContacts();
    });
});

function loadContacts() {
    const list = document.getElementById('contactList');
    list.innerHTML = '<div class="loading-spinner"><i class="fa-solid fa-spinner fa-spin"></i> Memuat...</div>';
    
    fetch('/api/admin/departments/' + currentDeptId + '/whatsapp-contacts')
        .then(r => r.json())
        .then(data => {
            if(data.length === 0) {
                list.innerHTML = '<div class="empty-state">Belum ada kontak WA untuk departemen ini.</div>';
                return;
            }
            
            list.innerHTML = data.map(c => `
                <div class="user-list-item" style="padding: 1rem;">
                    <div class="user-list-avatar" style="background: #25D366; color: white;">
                        <i class="fa-brands fa-whatsapp"></i>
                    </div>
                    <div class="user-list-info">
                        <strong>${c.name}</strong>
                        <span style="font-family: monospace; color: var(--text-muted); font-size: 0.9rem;">${c.phone}</span>
                    </div>
                    <div style="display: flex; gap: 0.4rem;">
                        <button class="btn btn-outline btn-xs" onclick='editContact(${JSON.stringify(c)})'>
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn btn-danger btn-xs" onclick="deleteContact(${c.id})">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            `).join('');
        });
}

// ─── Modal ───
const overlay = document.getElementById('contactModalOverlay');

document.getElementById('btnAddContact').addEventListener('click', () => {
    document.getElementById('contactForm').reset();
    document.getElementById('cId').value = '';
    document.getElementById('cDeptId').value = currentDeptId;
    document.getElementById('contactModalTitle').textContent = 'Tambah Kontak WA';
    overlay.classList.add('open');
});

document.getElementById('contactModalClose').addEventListener('click', () => overlay.classList.remove('open'));
document.getElementById('contactCancelBtn').addEventListener('click', () => overlay.classList.remove('open'));

window.editContact = function(c) {
    document.getElementById('cId').value = c.id;
    document.getElementById('cDeptId').value = c.department_id;
    document.getElementById('cName').value = c.name;
    document.getElementById('cPhone').value = c.phone;
    document.getElementById('contactModalTitle').textContent = 'Edit Kontak WA';
    overlay.classList.add('open');
};

document.getElementById('contactSaveBtn').addEventListener('click', () => {
    const id = document.getElementById('cId').value;
    const deptId = document.getElementById('cDeptId').value;
    const name = document.getElementById('cName').value;
    const phone = document.getElementById('cPhone').value;
    
    if(!name || !phone) {
        alert('Nama dan Nomor wajib diisi!');
        return;
    }

    const payload = {
        department_id: deptId,
        name: name,
        phone: phone,
        _token: CSRF
    };

    const url = id ? '/api/admin/whatsapp-contacts/' + id : '/api/admin/whatsapp-contacts';
    const method = id ? 'PUT' : 'POST';
    
    document.getElementById('saveContactText').style.display = 'none';
    document.getElementById('saveContactLoading').style.display = 'inline';
    document.getElementById('contactSaveBtn').disabled = true;

    fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            overlay.classList.remove('open');
            loadContacts();
        } else {
            alert('Gagal menyimpan kontak.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan sistem.');
    })
    .finally(() => {
        document.getElementById('saveContactText').style.display = 'inline';
        document.getElementById('saveContactLoading').style.display = 'none';
        document.getElementById('contactSaveBtn').disabled = false;
    });
});

window.deleteContact = function(id) {
    if(!confirm('Hapus kontak WA ini?')) return;
    
    fetch('/api/admin/whatsapp-contacts/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF }
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            loadContacts();
        }
    });
};
</script>
@endpush
