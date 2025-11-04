// script.js - gerencia carrinho, pagamento e checkout
(function () {
  document.addEventListener("DOMContentLoaded", init);

  function init() {
    const productsContainer = document.querySelector(".products") || document.getElementById("cart-items");
    const resumeEl = document.querySelector(".resume");
    let cartTotalEl = document.querySelector(".total-cart") || document.getElementById("cart-total");
    let cartAlert = document.getElementById("cart-alert");
    const checkoutBtn = document.getElementById("checkout-btn") || document.getElementById("go-to-checkout") || document.querySelector(".button");

    // cria alert se não existir
    if (!cartAlert && resumeEl) {
      cartAlert = document.createElement("div");
      cartAlert.id = "cart-alert";
      cartAlert.className = "cart-alert";
      cartAlert.style.display = "none";
      resumeEl.insertBefore(cartAlert, resumeEl.firstChild);
    }

    // garante total element
    if (!cartTotalEl && resumeEl) {
      const p = document.createElement("p");
      p.innerHTML = `Total: <span class="total-cart">R$ 0,00</span>`;
      resumeEl.insertBefore(p, resumeEl.querySelector(".custom-select") || resumeEl.querySelector("button") || resumeEl.firstChild);
      cartTotalEl = document.querySelector(".total-cart");
    }

    // carrega carrinho do localStorage (se existir)
    let cart = JSON.parse(localStorage.getItem("cart") || "[]");

    // pagamento selecionado (puxa do localStorage se existir)
    let selectedPayment = localStorage.getItem("paymentMethod") || null;

    // referências do custom-select (se existir)
    const selectBtn = document.querySelector(".select-btn");
    const customSelect = document.querySelector(".custom-select");
    const optionsList = document.querySelector(".options");
    const optionsItems = document.querySelectorAll(".options li");

    // rádios (caso use radio buttons em outra versão)
    const radioPayments = Array.from(document.querySelectorAll("input[name='payment'], input[name='payment-method']"));

    // util: formata BRL
    function formatBRL(value) {
      const n = Number(value) || 0;
      return n.toLocaleString("pt-BR", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // salva cart e payment no localStorage
    function saveState() {
      localStorage.setItem("cart", JSON.stringify(cart));
      if (selectedPayment) localStorage.setItem("paymentMethod", selectedPayment);
    }

    // mostra mensagem de alerta inline
    let alertTimeout = null;
    function showCartAlert(msg, type = "error") {
      if (!cartAlert) {
        alert(msg);
        return;
      }
      cartAlert.textContent = msg;
      cartAlert.style.display = "block";
      if (type === "error") cartAlert.style.background = "#ff6b6b";
      else cartAlert.style.background = "#4caf50";
      clearTimeout(alertTimeout);
      alertTimeout = setTimeout(() => {
        if (cartAlert) cartAlert.style.display = "none";
      }, 4000);
    }

    function clearCartAlert() {
      if (!cartAlert) return;
      cartAlert.style.display = "none";
      cartAlert.textContent = "";
    }

    // renderiza o carrinho (produtos)
    function renderCart() {
      if (!productsContainer) return;
      productsContainer.innerHTML = "";

      if (!cart || cart.length === 0) {
        const emptyMessage = document.createElement("p");
        emptyMessage.className = "empty";
        emptyMessage.textContent = "Seu carrinho está vazio";

        const emptyImg = document.createElement("img");
        emptyImg.src = "./imgs/cart.png";
        emptyImg.alt = "Carrinho vazio";
        emptyImg.className = "empty-img";

        productsContainer.appendChild(emptyMessage);
        productsContainer.appendChild(emptyImg);
        updateTotal();
        return;
      }

      cart.forEach((item, index) => {
        const itemDiv = document.createElement("div");
        itemDiv.className = "item";

        // subtotal
        const subtotal = (Number(item.price) || 0) * (Number(item.qty) || 1);

        itemDiv.innerHTML = `
          <div class="item-left" style="display:flex; gap:12px; align-items:center;">
            <img src="${item.img || 'https://via.placeholder.com/60'}" alt="${escapeHtml(item.name)}" width="60" height="60" style="object-fit:cover;border-radius:8px;">
            <div>
              <div class="item-name">${escapeHtml(item.name)}</div>
              <div class="small">R$ ${formatBRL(item.price)} cada</div>
            </div>
          </div>

          <div class="item-right" style="text-align:right; display:flex; flex-direction:column; gap:6px; align-items:flex-end;">
            <div style="display:flex; gap:8px; align-items:center;">
              <input class="qty-input" type="number" min="1" value="${Number(item.qty) || 1}" style="width:72px; padding:6px; border-radius:6px; border:1px solid #ddd;">
              <button class="btn-remove" data-index="${index}" title="Remover" style="background:none;border:none;color:#a180f3;cursor:pointer;">Remover</button>
            </div>
            <div class="price">Subtotal: R$ ${formatBRL(subtotal)}</div>
          </div>
        `;

        productsContainer.appendChild(itemDiv);

        // listener quantidade
        const qtyInput = itemDiv.querySelector(".qty-input");
        qtyInput.addEventListener("change", (e) => {
          const v = parseInt(e.target.value, 10);
          if (isNaN(v) || v < 1) {
            e.target.value = 1;
            cart[index].qty = 1;
          } else {
            cart[index].qty = v;
          }
          saveState();
          renderCart();
        });

        // remove
        const removeBtn = itemDiv.querySelector(".btn-remove");
        removeBtn.addEventListener("click", () => {
          cart.splice(index, 1);
          saveState();
          renderCart();
          updatePaymentUI(selectedPayment);
        });
      });

      updateTotal();
    }

    // escape simples para evitar HTML injection em nomes
    function escapeHtml(str) {
      return String(str || "").replace(/[&<>"'`=\/]/g, function (s) {
        return ({
          '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;',
          "'": '&#39;', '/': '&#x2F;', '`': '&#x60;', '=': '&#x3D;'
        })[s];
      });
    }

    // calcula e atualiza total na resume
    function updateTotal() {
      const total = cart.reduce((acc, it) => acc + (Number(it.price) || 0) * (Number(it.qty) || 1), 0);
      if (cartTotalEl) {
        cartTotalEl.textContent = `R$ ${formatBRL(total)}`;
      }
      saveState();
    }

    // adiciona item ao carrinho (exposto globalmente)
    window.addToCart = function (name, price, img, qty = 1) {
      const found = cart.find(it => it.name === name && Number(it.price) === Number(price));
      if (found) {
        found.qty = Number(found.qty || 0) + Number(qty || 1);
      } else {
        cart.push({ name, price: Number(price || 0), img: img || "", qty: Number(qty || 1) });
      }
      saveState();
      renderCart();
      updatePaymentUI(selectedPayment);
      showCartAlert("Produto adicionado ao carrinho.", "ok");
    };

    // seleção de pagamento
    function updatePaymentUI(value) {
      selectedPayment = value || null;
      if (selectBtn && optionsList) {
        optionsItems.forEach(li => li.classList.remove("selected"));
        const match = Array.from(optionsItems).find(li => (li.dataset.value || li.getAttribute("data-value")) === value);
        if (match) {
          match.classList.add("selected");
          const img = match.querySelector("img") ? match.querySelector("img").cloneNode(true) : null;
          selectBtn.innerHTML = "";
          if (img) {
            img.style.width = "18px";
            img.style.height = "18px";
            img.style.objectFit = "contain";
            selectBtn.appendChild(img);
          }
          const txt = match.textContent.trim();
          selectBtn.appendChild(document.createTextNode(" " + txt));
        } else {
          selectBtn.textContent = "Selecione a forma de pagamento";
        }
      }
      saveState();
    }

    // listeners no custom-select
    if (selectBtn && optionsList) {
      selectBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        customSelect.classList.toggle("open");
      });

      optionsItems.forEach(li => {
        li.addEventListener("click", () => {
          const value = li.dataset.value || li.getAttribute("data-value");
          updatePaymentUI(value);
          customSelect.classList.remove("open");
        });
      });

      document.addEventListener("click", (e) => {
        if (!e.target.closest(".custom-select")) {
          customSelect.classList.remove("open");
        }
      });
    }

    // botão checkout
    if (checkoutBtn) {
      checkoutBtn.addEventListener("click", (e) => {
        if (e) e.preventDefault && e.preventDefault();
        clearCartAlert();

        if (!cart || cart.length === 0) {
          showCartAlert("Adicione itens no carrinho antes de continuar.");
          return;
        }

        if (!selectedPayment) {
          showCartAlert("Selecione a forma de pagamento antes de continuar.");
          return;
        }

        saveState();
        const target = "finalizar.html?payment=" + encodeURIComponent(selectedPayment);
        window.location.href = target;
      });
    }

    // inicia UI
    if (selectedPayment) updatePaymentUI(selectedPayment);
    renderCart();

    // debug
    window._cartDebug = { cart, updateTotal, updatePaymentUI, renderCart };
  }
})();
