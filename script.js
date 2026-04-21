let cart = [];

function calculateChange(){
    let paymentInput = document.getElementById("payment");
    if(!paymentInput) return;
    
    let payment = paymentInput.value || 0;
    let total = cart.reduce((sum, p) => sum + (p.price * p.qty), 0);
    let change = payment - total;
    document.getElementById("change").innerText = "Change: ₱" + parseFloat(change).toFixed(2);
}

function loadProducts(){
    fetch("get_products.php")
    .then(res => {
        if(!res.ok) throw new Error("Failed to load products");
        return res.json();
    })
    .then(data => {
        if(!data || data.length === 0){
            document.getElementById("productTable").innerHTML = `
            <div class="empty-state">
                <span class="empty-state-icon"></span>
                <h3>No Products Available</h3>
                <p>Add your first product from the Products section to get started!</p>
            </div>`;
            return;
        }
        
        let html = '<div class="product-grid">';

        data.forEach(p => {
            // Check for low stock
            if(p.stock <= 5){
                let notifDiv = document.getElementById("notif");
                if(notifDiv){
                    const escapedName = new DOMParser().parseFromString('<!doctype html><body>' + (p.name || ''), 'text/html').body.textContent;
                    notifDiv.innerHTML += `<div style="background:#ffccd5;padding:12px;border-radius:10px;margin-bottom:10px;border-left:4px solid #f44336;">⚠ Low stock: <strong>${escapedName}</strong></div>`;
                }
            }

            const statusClass = p.status === 'LOW' ? 'low' : 'ok';
            const statusText = p.status === 'LOW' ? 'Low Stock' : 'In Stock';
            
            const imageSrc = p.image && p.image.includes('http') ? p.image : 'uploads/' + (p.image || 'default.png');
            // Escape HTML in product names
            const escapedName = new DOMParser().parseFromString('<!doctype html><body>' + (p.name || ''), 'text/html').body.textContent;
            const escapedCategory = new DOMParser().parseFromString('<!doctype html><body>' + (p.category || ''), 'text/html').body.textContent;
            
            html += `
            <div class="product-card">
                <div class="product-card-image">
                    <img src="${encodeURI(imageSrc)}" alt="${escapedName}" onerror="this.src='uploads/default.png'">
                </div>
                <div class="product-card-content">
                    <h3>${escapedName}</h3>
                    <p class="category">${escapedCategory}</p>
                    <div class="price">₱${parseFloat(p.price).toFixed(2)}</div>
                    <span class="stock ${statusClass}">${statusText}</span>
                    <button onclick="addToCart(${p.id}, '${escapedName.replace(/'/g, "\\'")}', ${p.price}, '${(p.image || '').replace(/'/g, "\\'")}')">
                        Add to Cart
                    </button>
                </div>
            </div>`;
        });

        html += '</div>';
        document.getElementById("productTable").innerHTML = html;
    })
    .catch(err => {
        console.error("Error loading products:", err);
        document.getElementById("productTable").innerHTML = `<div class="empty-state"><h3>Error loading products</h3></div>`;
    });
}

function addToCart(id, name, price, image){
    let item = cart.find(p => p.id === id);

    if(item){
        item.qty++;
    } else {
        cart.push({id, name, price, image, qty:1});
    }
    // Validate quantity
    if(cart.find(p => p.id === id).qty <= 0){
        showToast("Invalid quantity", true);
        return;
    }
    renderCart();
    showToast(`\u2705 ${name} added to cart!`);
}

function renderCart(){
    let html = `
    <tr>
        <th>Product</th>
        <th>Qty</th>
        <th>Price</th>
        <th>Action</th>
    </tr>`;

    let total = 0;

    cart.forEach((p, index) => {
        const subtotal = p.price * p.qty;
        total += subtotal;
        const escapedName = new DOMParser().parseFromString('<!doctype html><body>' + (p.name || ''), 'text/html').body.textContent;

        html += `
        <tr>
            <td>
                <img src="uploads/${p.image}" width="30" style="margin-right: 8px;" onerror="this.src='uploads/default.png'">
                ${escapedName}
            </td>
            <td>
                <button class="qty-btn" onclick="updateQty(${index}, -1)">−</button>
                <span style="margin: 0 8px;">${p.qty}</span>
                <button class="qty-btn" onclick="updateQty(${index}, 1)">+</button>
            </td>
            <td>₱${subtotal.toFixed(2)}</td>
            <td><button class="remove-btn" onclick="removeFromCart(${index})">Remove</button></td>
        </tr>`;
    });

    document.getElementById("cartTable").innerHTML = html;
    document.getElementById("total").innerText = "₱" + total.toFixed(2);
    calculateChange();
}

function updateQty(index, change){
    cart[index].qty += change;
    if(cart[index].qty <= 0){
        cart.splice(index, 1);
    }
    renderCart();
}

function removeFromCart(index){
    cart.splice(index, 1);
    renderCart();
}

function checkout(){
    if(cart.length === 0){
        showToast("Cart is empty!", true);
        return;
    }

    let payment = document.getElementById("payment").value;
    if(!payment || payment <= 0){
        showToast("Please enter a valid payment amount", true);
        return;
    }

    let total = cart.reduce((sum, p) => sum + (p.price * p.qty), 0);
    let customer_id = document.getElementById("customer").value || 0;
    let checkoutData = {
        items: cart,
        payment: parseFloat(payment),
        total: total,
        customer_id: parseInt(customer_id)
    };

    fetch("checkout.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify(checkoutData)
    })
    .then(res => res.json())
    .then(response => {
        if(response.success){
            showToast("✅ " + response.message + " - Redirecting...");
            setTimeout(() => {
                window.location.href = response.receipt_url;
            }, 1500);
        } else {
            showToast("❌ " + response.message, true);
        }
    })
    .catch(err => {
        showToast("Error during checkout: " + err.message, true);
    });
}

function showToast(message, isError = false){
    const toast = document.createElement('div');
    toast.className = isError ? 'toast error' : 'toast';
    toast.textContent = message; // Use textContent for security
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('removing');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

loadProducts();

// Load Customers
function loadCustomers(){
    fetch("get_customers.php")
    .then(res => {
        if(!res.ok) throw new Error("Failed to load customers");
        return res.json();
    })
    .then(data => {
        let customerSelect = document.getElementById("customer");
        if(!customerSelect) return;
        
        let html = '<option value="">Select Customer</option>';
        if(Array.isArray(data)){
            data.forEach(c => {
                const escapedName = new DOMParser().parseFromString('<!doctype html><body>' + (c.name || ''), 'text/html').body.textContent;
                html += `<option value="${c.id}">${escapedName}</option>`;
            });
        }
        customerSelect.innerHTML = html;
    })
    .catch(err => {
        console.error("Error loading customers:", err);
        showToast("Error loading customers", true);
    });
}

// Real-time payment change calculation
document.addEventListener('DOMContentLoaded', function(){
    let paymentInput = document.getElementById("payment");
    if(paymentInput){
        paymentInput.addEventListener('input', calculateChange);
    }
    loadCustomers();
});