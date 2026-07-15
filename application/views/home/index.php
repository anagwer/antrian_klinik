<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teras GenZ - Pesan Sekarang</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #56654f;
            --primary-hover: #69805eff;
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --text-main: #2d3436;
            --text-muted: #636e72;
            --border-radius: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            padding-bottom: 80px; /* Space for fab */
        }

        /* Header */
        header {
            background: var(--card-bg);
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            height: 40px;
            object-fit: contain;
        }

        /* Banner */
        .banner-container {
            padding: 15px 20px;
        }

        .banner {
            width: 100%;
            border-radius: var(--border-radius);
            object-fit: cover;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        /* Categories */
        .categories-container {
            padding: 10px 20px;
            display: flex;
            gap: 10px;
            overflow-x: auto;
            scrollbar-width: none; /* Firefox */
        }

        .categories-container::-webkit-scrollbar {
            display: none; /* Safari and Chrome */
        }

        .category-bubble {
            background: var(--card-bg);
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .category-bubble.active {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 4px 10px rgba(255, 107, 107, 0.3);
        }

        /* Menu Grid */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 15px;
            padding: 15px 20px;
        }

        .menu-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            transition: transform 0.2s ease;
        }

        .menu-card:hover {
            transform: translateY(-5px);
        }

        .menu-img {
            width: 100%;
            height: 140px;
            object-fit: cover;
        }

        .menu-details {
            padding: 12px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .menu-name {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 5px;
            line-height: 1.3;
        }

        .menu-price {
            font-size: 14px;
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 12px;
            margin-top: auto;
        }

        /* Add to Cart Actions */
        .add-action {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f1f3f5;
            border-radius: 8px;
            overflow: hidden;
        }

        .btn-qty {
            background: none;
            border: none;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 16px;
            color: var(--text-main);
        }
        
        .btn-qty:hover {
            background: #e9ecef;
        }

        .qty-val {
            font-size: 14px;
            font-weight: 600;
            width: 30px;
            text-align: center;
        }

        .btn-add {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 0;
            width: 100%;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-add:hover {
            background: var(--primary-hover);
        }

        /* Floating Cart Button */
        .fab-cart {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary-color);
            color: white;
            padding: 15px 25px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
            cursor: pointer;
            z-index: 1000;
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            width: 90%;
            max-width: 400px;
            justify-content: space-between;
        }

        .fab-cart.hidden {
            transform: translate(-50%, 150%);
        }

        .fab-cart .cart-info {
            display: flex;
            flex-direction: column;
        }

        .fab-cart .cart-items {
            font-size: 12px;
            opacity: 0.9;
        }

        .fab-cart .cart-total {
            font-weight: 700;
            font-size: 16px;
        }

        /* Modal Overlay */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1001;
            display: none;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .modal-overlay.active {
            display: block;
            opacity: 1;
        }

        /* Detail Modal */
        .detail-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.9);
            background: var(--card-bg);
            border-radius: 16px;
            z-index: 1003;
            width: 90%;
            max-width: 400px;
            padding: 20px;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .detail-modal.active {
            opacity: 1;
            visibility: visible;
            transform: translate(-50%, -50%) scale(1);
        }

        .detail-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .detail-title { font-size: 18px; font-weight: 700; margin-bottom: 10px; }
        .detail-desc { font-size: 14px; color: var(--text-muted); line-height: 1.5; margin-bottom: 20px; }

        /* Cart Modal */
        .cart-modal {
            position: fixed;
            bottom: -100%;
            left: 0;
            width: 100%;
            background: var(--card-bg);
            border-radius: 24px 24px 0 0;
            z-index: 1002;
            padding: 20px;
            transition: bottom 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            max-height: 85vh;
            display: flex;
            flex-direction: column;
        }

        .cart-modal.active {
            bottom: 0;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 700;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: var(--text-muted);
        }

        .cart-items-container {
            overflow-y: auto;
            flex-grow: 1;
            margin-bottom: 15px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .cart-item-info {
            flex-grow: 1;
        }

        .cart-item-name {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .cart-item-price {
            font-size: 13px;
            color: var(--primary-color);
        }

        /* Form Checkout */
        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
        }

        .payment-methods {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .payment-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .payment-card.active {
            border-color: var(--primary-color);
            background: #fff0f0;
            color: var(--primary-color);
            font-weight: 600;
        }

        #qris-container {
            display: none;
            text-align: center;
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        #qris-container img {
            max-width: 200px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .btn-checkout {
            background: var(--primary-color);
            color: white;
            border: none;
            width: 100%;
            padding: 15px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        /* Notifications */
        .toast {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-100px);
            background: #333;
            color: white;
            padding: 12px 20px;
            border-radius: 30px;
            font-size: 14px;
            z-index: 9999;
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            opacity: 0;
        }

        .toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        .toast.success { background: #56654f; }
        .toast.error { background: #e74c3c; }

    </style>
</head>
<body>

    <!-- Header -->
    <header style="background-color: #56654f; color: white;">
        <img src="<?= base_url('files/toko/logo.png') ?>" alt="Teras GenZ Logo" class="logo">
        <h1 style="margin-left: 10px; font-size: 20px; color: white;">Teras GenZ</h1>
    </header>

    <!-- Banner Promo -->
    <div class="banner-container">
        <img src="<?= base_url('files/toko/banner.jpg') ?>" alt="Promo Banner" class="banner">
    </div>

    <!-- Category Filter -->
    <div class="categories-container">
        <div class="category-bubble active" onclick="filterMenu('all', this)">Semua</div>
        <?php foreach($kategori as $kat): ?>
            <div class="category-bubble" onclick="filterMenu('<?= $kat->nama_kategori ?>', this)"><?= $kat->nama_kategori ?></div>
        <?php endforeach; ?>
    </div>

    <!-- Menu Grid -->
    <div class="menu-grid">
        <?php foreach($menus as $menu): ?>
        <div class="menu-card" data-category="<?= $menu->kategori ?>" data-id="<?= $menu->id ?>" data-name="<?= htmlspecialchars($menu->nama_menu) ?>" data-price="<?= $menu->harga ?>">
            <?php 
                $foto = !empty($menu->foto) ? base_url('files/menu/'.$menu->foto) : 'https://via.placeholder.com/300x200?text=No+Image'; 
            ?>
            <img src="<?= $foto ?>" alt="<?= htmlspecialchars($menu->nama_menu) ?>" class="menu-img" onclick="openDetail(this, '<?= htmlspecialchars(addslashes($menu->deskripsi)) ?>')" style="cursor: pointer;">
            <div class="menu-details">
                <div class="menu-name" onclick="openDetail(this.parentElement.previousElementSibling, '<?= htmlspecialchars(addslashes($menu->deskripsi)) ?>')" style="cursor: pointer;"><?= htmlspecialchars($menu->nama_menu) ?></div>
                <div class="menu-price">Rp <?= number_format($menu->harga, 0, ',', '.') ?></div>
                
                <div class="add-action" id="action-<?= $menu->id ?>" style="display: none;">
                    <button class="btn-qty" onclick="updateQty(<?= $menu->id ?>, -1)"><i class="fas fa-minus"></i></button>
                    <div class="qty-val" id="qty-<?= $menu->id ?>">0</div>
                    <button class="btn-qty" onclick="updateQty(<?= $menu->id ?>, 1)"><i class="fas fa-plus"></i></button>
                </div>
                <div class="subtotal-item" id="subtotal-item-<?= $menu->id ?>" style="display: none; font-size: 12px; font-weight: 700; color: var(--primary-color); text-align: center; margin-top: 5px;"></div>
                <button class="btn-add" id="btn-add-<?= $menu->id ?>" onclick="addToCart(<?= $menu->id ?>)">Tambah</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Floating Action Button Cart -->
    <div class="fab-cart hidden" id="fabCart" onclick="openCart()">
        <div class="cart-info">
            <span class="cart-items" id="fabItemCount">0 Item</span>
            <span class="cart-total" id="fabTotalPrice">Rp 0</span>
        </div>
        <div>
            <i class="fas fa-shopping-cart"></i>
        </div>
    </div>

    <!-- Modal Overlay -->
    <div class="modal-overlay" id="modalOverlay" onclick="closeAllModals()"></div>

    <!-- Detail Modal -->
    <div class="detail-modal" id="detailModal">
        <div class="modal-header">
            <div class="modal-title">Detail Menu</div>
            <button class="close-modal" onclick="closeDetail()"><i class="fas fa-times"></i></button>
        </div>
        <img src="" id="detailImg" class="detail-img">
        <div class="detail-title" id="detailTitle"></div>
        <div class="detail-desc" id="detailDesc"></div>
    </div>

    <!-- Cart Modal -->
    <div class="cart-modal" id="cartModal">
        <div class="modal-header">
            <div class="modal-title">Keranjang Pesanan</div>
            <button class="close-modal" onclick="closeCart()"><i class="fas fa-times"></i></button>
        </div>
        
        <div class="cart-items-container" id="cartItemsContainer">
            <!-- Items injected by JS -->
        </div>

        <div class="form-group">
            <label class="form-label">Nama Pemesan</label>
            <input type="text" id="customerName" class="form-control" placeholder="Masukkan nama Anda..." required>
        </div>

        <div class="form-group">
            <label class="form-label">Nomor Meja</label>
            <input type="text" id="noMeja" class="form-control" placeholder="Kosongkan jika dibawa pulang">
        </div>

        <div class="form-group">
            <label class="form-label">Metode Pembayaran</label>
            <div class="payment-methods">
                <div class="payment-card active" onclick="setPayment('Cash', this)">Cash</div>
                <div class="payment-card" onclick="setPayment('QRIS', this)">QRIS</div>
            </div>
            <input type="hidden" id="paymentMethod" value="Cash">
        </div>

        <div class="form-group">
            <label class="form-label">Jenis Pesanan</label>
            <div class="payment-methods">
                <div class="payment-card active" onclick="setJenisOrder('Dine-in', this)">Dine-in</div>
                <div class="payment-card" onclick="setJenisOrder('Takeaway', this)">Takeaway</div>
            </div>
            <input type="hidden" id="jenisOrder" value="Dine-in">
        </div>

        <div id="qris-container">
            <p style="font-size: 13px; font-weight: 600; margin-bottom: 10px;">Scan QRIS di bawah ini untuk membayar</p>
            <img src="<?= base_url('files/toko/qris.jpeg') ?>" alt="QRIS Payment">
        </div>

        <button class="btn-checkout" id="btnCheckout" onclick="checkout()">
            Pesan - <span id="checkoutTotal">Rp 0</span>
        </button>
    </div>

    <!-- Toast -->
    <div id="toast" class="toast"></div>

    <script>
        let cart = {}; // Store {id: {name, price, qty}}

        function formatRupiah(number) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast show ' + type;
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        function filterMenu(category, element) {
            // Update bubbles
            document.querySelectorAll('.category-bubble').forEach(el => el.classList.remove('active'));
            element.classList.add('active');

            // Filter cards
            document.querySelectorAll('.menu-card').forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function addToCart(id) {
            const card = document.querySelector(`.menu-card[data-id="${id}"]`);
            const name = card.dataset.name;
            const price = parseInt(card.dataset.price);

            if (!cart[id]) {
                cart[id] = { id: id, name: name, price: price, qty: 1 };
            }

            document.getElementById(`btn-add-${id}`).style.display = 'none';
            document.getElementById(`action-${id}`).style.display = 'flex';
            document.getElementById(`qty-${id}`).innerText = cart[id].qty;

            updateCartUI();
        }

        function updateQty(id, change) {
            if (cart[id]) {
                cart[id].qty += change;
                
                if (cart[id].qty <= 0) {
                    delete cart[id];
                    document.getElementById(`action-${id}`).style.display = 'none';
                    document.getElementById(`btn-add-${id}`).style.display = 'block';
                } else {
                    document.getElementById(`qty-${id}`).innerText = cart[id].qty;
                }
                
                updateCartUI();
            }
        }

        function updateCartUI() {
            let totalItems = 0;
            let totalPrice = 0;

            // Reset all subtotals in menu grid first
            document.querySelectorAll('.subtotal-item').forEach(el => {
                el.style.display = 'none';
                el.innerText = '';
            });

            for (let id in cart) {
                totalItems += cart[id].qty;
                totalPrice += cart[id].qty * cart[id].price;
                
                // Update subtotal in menu grid
                const subtotalEl = document.getElementById(`subtotal-item-${id}`);
                if (subtotalEl) {
                    subtotalEl.innerText = 'Total ' + formatRupiah(cart[id].qty * cart[id].price);
                    subtotalEl.style.display = 'block';
                }
            }

            const fab = document.getElementById('fabCart');
            if (totalItems > 0) {
                fab.classList.remove('hidden');
                document.getElementById('fabItemCount').innerText = `${totalItems} Item`;
                document.getElementById('fabTotalPrice').innerText = formatRupiah(totalPrice);
                document.getElementById('checkoutTotal').innerText = formatRupiah(totalPrice);
            } else {
                fab.classList.add('hidden');
                closeCart();
            }

            renderCartModal();
        }

        function renderCartModal() {
            const container = document.getElementById('cartItemsContainer');
            container.innerHTML = '';

            for (let id in cart) {
                const item = cart[id];
                const itemHtml = `
                    <div class="cart-item">
                        <div class="cart-item-info">
                            <div class="cart-item-name">${item.name}</div>
                            <div class="cart-item-price">${formatRupiah(item.price)} x ${item.qty}</div>
                        </div>
                        <div style="text-align: right;">
                            <div class="add-action" style="width: 100px; margin-bottom: 5px;">
                                <button class="btn-qty" onclick="updateQty(${id}, -1)"><i class="fas fa-minus"></i></button>
                                <div class="qty-val">${item.qty}</div>
                                <button class="btn-qty" onclick="updateQty(${id}, 1)"><i class="fas fa-plus"></i></button>
                            </div>
                            <div style="font-size: 13px; font-weight: 700; color: var(--primary-color);">${formatRupiah(item.price * item.qty)}</div>
                        </div>
                    </div>
                `;
                container.innerHTML += itemHtml;
            }
        }

        function openCart() {
            document.getElementById('modalOverlay').classList.add('active');
            document.getElementById('cartModal').classList.add('active');
        }

        function closeCart() {
            document.getElementById('modalOverlay').classList.remove('active');
            document.getElementById('cartModal').classList.remove('active');
        }

        function openDetail(imgEl, desc) {
            const card = imgEl.closest('.menu-card');
            const title = card.dataset.name;
            const imgSrc = imgEl.src;
            
            document.getElementById('detailImg').src = imgSrc;
            document.getElementById('detailTitle').innerText = title;
            document.getElementById('detailDesc').innerText = desc;
            
            document.getElementById('modalOverlay').classList.add('active');
            document.getElementById('detailModal').classList.add('active');
        }
        
        function closeDetail() {
            document.getElementById('modalOverlay').classList.remove('active');
            document.getElementById('detailModal').classList.remove('active');
        }

        function closeAllModals() {
            closeCart();
            closeDetail();
        }

        function setPayment(method, element) {
            document.querySelectorAll('.payment-card').forEach(el => {
                if (el.getAttribute('onclick').includes('setPayment')) el.classList.remove('active');
            });
            element.classList.add('active');
            document.getElementById('paymentMethod').value = method;

            if (method === 'QRIS') {
                document.getElementById('qris-container').style.display = 'block';
            } else {
                document.getElementById('qris-container').style.display = 'none';
            }
        }

        function setJenisOrder(jenis, element) {
            document.querySelectorAll('.payment-card').forEach(el => {
                if (el.getAttribute('onclick').includes('setJenisOrder')) el.classList.remove('active');
            });
            element.classList.add('active');
            document.getElementById('jenisOrder').value = jenis;
        }

        function checkout() {
            const customerName = document.getElementById('customerName').value.trim();
            const noMeja = document.getElementById('noMeja').value.trim();
            const paymentMethod = document.getElementById('paymentMethod').value;
            const jenisOrder = document.getElementById('jenisOrder').value;
            
            if (customerName === '') {
                showToast('Mohon isi Nama Pemesan', 'error');
                document.getElementById('customerName').focus();
                return;
            }

            if (Object.keys(cart).length === 0) {
                showToast('Keranjang kosong', 'error');
                return;
            }

            if (paymentMethod === 'QRIS') {
                if (!confirm('Apakah sudah dibayar menggunakan QRIS? Jika sudah, klik OK.')) {
                    return;
                }
            }

            // Prepare cart data
            const cartData = [];
            for(let id in cart) {
                cartData.push({
                    nama_menu: cart[id].name,
                    harga_menu: cart[id].price,
                    jumlah_menu: cart[id].qty,
                    sub_total: cart[id].price * cart[id].qty
                });
            }

            const btn = document.getElementById('btnCheckout');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('nama_pemesan', customerName);
            formData.append('no_meja', noMeja);
            formData.append('metode_pembayaran', paymentMethod);
            formData.append('jenis_order', jenisOrder);
            formData.append('cart', JSON.stringify(cartData));

            fetch('<?= base_url('home/checkout') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    showToast('Pesanan berhasil dibuat!', 'success');
                    
                    // Reset UI
                    cart = {};
                    updateCartUI();
                    document.getElementById('customerName').value = '';
                    setPayment('Cash', document.querySelector('.payment-card'));
                    
                    // Hide Add buttons and show Add to cart state
                    document.querySelectorAll('.add-action').forEach(el => el.style.display = 'none');
                    document.querySelectorAll('.btn-add').forEach(el => el.style.display = 'block');
                    
                    closeCart();
                    
                    // Show success alert
                    setTimeout(() => {
                        if (paymentMethod === 'Cash') {
                            alert('Pesanan berhasil dibuat, silakan bayar di kasir.');
                        } else {
                            alert(`Pesanan berhasil! No Penjualan: ${data.no_penjualan}. Silakan tunggu pesanan Anda.`);
                        }
                    }, 500);
                } else {
                    showToast(data.message || 'Terjadi kesalahan', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan koneksi', 'error');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>
