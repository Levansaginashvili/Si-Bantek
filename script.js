document.addEventListener('DOMContentLoaded', () => {
    // 1. Menu Navigasi Aktif Interaktif
    const navItems = document.querySelectorAll('.nav-menu li');

    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Hapus kelas 'active' dari semua menu
            navItems.forEach(nav => nav.classList.remove('active'));
            
            // Tambahkan kelas 'active' pada menu yang diklik
            this.classList.add('active');
        });
    });

    // 2. Event Tombol Logout
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const confirmLogout = confirm('Apakah Anda yakin ingin keluar?');
            if (confirmLogout) {
                alert('Anda telah logout dari aplikasi.');
                // Logika redirect logout (contoh):
                // window.location.href = 'login.html';
            }
        });
    }

    // 3. Efek Hover atau Interaksi Tambahan Card
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-2px)';
            card.style.transition = 'transform 0.2s ease, box-shadow 0.2s ease';
            card.style.boxShadow = '0 6px 16px rgba(0,0,0,0.08)';
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
            card.style.boxShadow = '0 4px 12px rgba(0,0,0,0.04)';
        });
    });
});