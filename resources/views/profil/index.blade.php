@extends('layouts.app')

@section('title', 'Profil Pengguna')
@section('page_title', 'Profil Pengguna')

@section('content')
<div class="card" style="max-width: 800px; margin: 0 auto; padding: 2rem;">
    <div style="display: flex; align-items: center; gap: 2rem; margin-bottom: 2.5rem; padding-bottom: 2rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
        <div style="width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple)); display: flex; align-items: center; justify-content: center; font-size: 3.5rem; font-weight: bold; color: white; box-shadow: 0 10px 20px rgba(0,0,0,0.2);">
            V
        </div>
        <div>
            <h2 style="margin-bottom: 0.5rem; font-size: 1.8rem; font-weight: 700;">Veri Septiriyanto</h2>
            <div style="display: flex; gap: 1rem; align-items: center; color: var(--text-secondary); margin-bottom: 1.25rem;">
                <span style="display: flex; align-items: center; gap: 0.5rem;"><i class="ph ph-identification-badge"></i> Owner / Administrator</span>
                <span style="display: flex; align-items: center; gap: 0.5rem;"><i class="ph ph-map-pin"></i> Pusat</span>
            </div>
            <button style="padding: 0.6rem 1.2rem; border-radius: 8px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 500; transition: all 0.2s;">
                <i class="ph ph-camera"></i> Ubah Foto
            </button>
        </div>
    </div>

    <form action="#" method="POST">
        @csrf
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <div class="form-group" style="display: flex; flex-direction: column; gap: 0.5rem;">
                <label style="color: var(--text-secondary); font-size: 0.9rem;">Nama Lengkap</label>
                <div style="position: relative;">
                    <i class="ph ph-user" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                    <input type="text" class="form-control" name="name" value="Veri Septiriyanto" style="width: 100%; padding-left: 2.5rem; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white;">
                </div>
            </div>
            
            <div class="form-group" style="display: flex; flex-direction: column; gap: 0.5rem;">
                <label style="color: var(--text-secondary); font-size: 0.9rem;">Email</label>
                <div style="position: relative;">
                    <i class="ph ph-envelope-simple" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                    <input type="email" class="form-control" name="email" value="veri@example.com" style="width: 100%; padding-left: 2.5rem; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white;">
                </div>
            </div>
            
            <div class="form-group" style="display: flex; flex-direction: column; gap: 0.5rem;">
                <label style="color: var(--text-secondary); font-size: 0.9rem;">No. Telepon / WhatsApp</label>
                <div style="position: relative;">
                    <i class="ph ph-phone" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                    <input type="text" class="form-control" name="phone" value="081234567890" style="width: 100%; padding-left: 2.5rem; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white;">
                </div>
            </div>
            
            <div class="form-group" style="display: flex; flex-direction: column; gap: 0.5rem;">
                <label style="color: var(--text-secondary); font-size: 0.9rem;">Role Sistem</label>
                <div style="position: relative;">
                    <i class="ph ph-shield-check" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                    <input type="text" class="form-control" value="Owner" disabled style="width: 100%; padding-left: 2.5rem; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05); border-radius: 8px; color: var(--text-secondary); cursor: not-allowed;">
                </div>
            </div>
        </div>
        
        <div style="border-top: 1px solid rgba(255,255,255,0.05); padding-top: 2rem; margin-top: 2rem;">
            <h3 style="margin-bottom: 1.5rem; font-size: 1.2rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                <i class="ph ph-lock-key" style="color: var(--accent-blue);"></i> Keamanan & Password
            </h3>
            
            <div class="form-group" style="margin-bottom: 1.5rem; display: flex; flex-direction: column; gap: 0.5rem;">
                <label style="color: var(--text-secondary); font-size: 0.9rem;">Password Lama</label>
                <input type="password" class="form-control" name="current_password" placeholder="Masukkan password lama untuk mengubah password" style="width: 100%; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; padding: 0.75rem 1rem;">
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group" style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <label style="color: var(--text-secondary); font-size: 0.9rem;">Password Baru</label>
                    <input type="password" class="form-control" name="new_password" placeholder="Minimal 8 karakter" style="width: 100%; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; padding: 0.75rem 1rem;">
                </div>
                
                <div class="form-group" style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <label style="color: var(--text-secondary); font-size: 0.9rem;">Konfirmasi Password Baru</label>
                    <input type="password" class="form-control" name="new_password_confirmation" placeholder="Ulangi password baru" style="width: 100%; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; padding: 0.75rem 1rem;">
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 2.5rem; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 1.5rem;">
            <button type="submit" style="background: var(--accent-blue); color: white; border: none; padding: 0.75rem 2rem; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(92, 106, 255, 0.3); transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(92, 106, 255, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(92, 106, 255, 0.3)';">
                <i class="ph ph-floppy-disk"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
