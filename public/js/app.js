// Simple frontend JS for menu, cart and checkout
(function(){
    const productsDiv = document.getElementById('products');
    const cartDiv = document.getElementById('cart');
    const checkoutBtn = document.getElementById('checkoutBtn');
    let cart = [];

    function renderProducts(products){
        productsDiv.innerHTML = '';
        products.forEach(p=>{
            const el = document.createElement('div');
            el.innerHTML = `<strong>${p.name}</strong> - $${p.price.toFixed(2)}<br>${p.description}<br>
            <button data-id="${p.id}">Añadir</button>`;
            const btn = el.querySelector('button');
            btn.addEventListener('click', ()=> {
                addToCart(p.id, p.name, p.price);
            });
            productsDiv.appendChild(el);
        });
    }

    function renderCart(){
        cartDiv.innerHTML = '';
        if(cart.length===0){ cartDiv.innerText = 'Carrito vacío'; return; }
        cart.forEach((it,idx)=>{
            const row = document.createElement('div');
            row.innerHTML = `${it.name} x ${it.quantity} - $${(it.price*it.quantity).toFixed(2)} <button data-idx="${idx}">Quitar</button>`;
            row.querySelector('button').addEventListener('click', ()=> {
                cart.splice(idx,1); renderCart();
            });
            cartDiv.appendChild(row);
        });
    }

    function addToCart(id,name,price){
        const existing = cart.find(c=>c.product_id===id);
        if(existing) existing.quantity++;
        else cart.push({product_id:id,name,price,quantity:1});
        renderCart();
    }

    async function loadMenu(){
        const res = await fetch('/api/menu');
        const products = await res.json();
        renderProducts(products);
    }

    if(checkoutBtn){
        checkoutBtn.addEventListener('click', async ()=>{
            if(cart.length===0){ alert('Carrito vacío'); return; }
            const name = prompt('Nombre del cliente');
            if(!name) return;
            const payload = { customer_name: name, items: cart.map(c=>({product_id:c.product_id,quantity:c.quantity})) };
            const r = await fetch('/api/checkout',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
            const json = await r.json();
            if(r.status===201) { alert('Pedido creado. ID: '+json.order_id); cart=[]; renderCart(); }
            else alert('Error: '+(json.error||'unknown'));
        });
    }

    // If on checkout.html, handle form
    const form = document.getElementById('checkoutForm');
    if(form){
        form.addEventListener('submit', async (e)=>{
            e.preventDefault();
            if(cart.length===0){ alert('Carrito vacío'); return; }
            const fd = new FormData(form);
            const payload = {
                customer_name: fd.get('customer_name'),
                customer_phone: fd.get('customer_phone'),
                items: cart.map(c=>({product_id:c.product_id,quantity:c.quantity}))
            };
            const r = await fetch('/api/checkout',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
            const json = await r.json();
            document.getElementById('result').innerText = JSON.stringify(json);
        });
    }

    // initial
    loadMenu();
    renderCart();
})();
